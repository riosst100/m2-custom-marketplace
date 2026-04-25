<?php

namespace TCGCollective\MarketPlace\Controller\Marketplace\Payment;

class Deactivate extends \Magento\Framework\App\Action\Action
{
    const FLAG_IS_URLS_CHECKED = 'check_url_settings';

    /**
     * @var \Magento\Framework\Url
     */
    protected $_frontendUrl;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $session;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Lof\MarketPlace\Model\SellerFactory
     */
    protected $sellerFactory;

    /**
     * @var \Magento\Framework\App\ActionFlag
     */
    protected $_actionFlag;

    /**
     * Save constructor.
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Lof\MarketPlace\Model\SellerFactory $sellerFactory
     * @param \Magento\Framework\Url $frontendUrl
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Lof\MarketPlace\Model\SellerFactory $sellerFactory,
        \Magento\Framework\Url $frontendUrl,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        parent::__construct($context);

        $this->_frontendUrl = $frontendUrl;
        $this->_actionFlag = $context->getActionFlag();
        $this->sellerFactory = $sellerFactory;
        $this->session = $customerSession;
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * @param string $route
     * @param array $params
     * @return string|null
     */
    public function getFrontendUrl($route = '', $params = [])
    {
        return $this->_frontendUrl->getUrl($route, $params);
    }

    /**
     * @param $url
     */
    protected function _redirectUrl($url)
    {
        $this->getResponse()->setRedirect($url);
        $this->session->setIsUrlNotice($this->_actionFlag->get('', self::FLAG_IS_URLS_CHECKED));
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $customerSession = $this->session;
        // @phpstan-ignore-next-line
        $helper = $this->_objectManager->get(\Lof\MarketPlace\Helper\Data::class);
        if (!$customerSession->isLoggedIn()) {
            $this->_redirectUrl('catalog/dashboard');
            return;
        }

        $section = 'payment';
        $groups = [];

        // if (strlen($section) > 0 && count($groups) > 0) {
            $seller_id = (int)$helper->getSellerId();
            try {
                $code = $this->getRequest()->getParam('id');
                $groups[$code]['active'] = 0;

                $this->_objectManager->get(\Lof\MarketPlace\Helper\Data::class)
                ->savePaymentData($section, $groups, $seller_id);
                
                $customerId = $customerSession->getId();

                $seller = $this->sellerFactory->create()->load($customerId, 'customer_id');
                $seller->setRegistrationStep('payment');
                $seller->setNextStep('payment');
                $seller->save();

                $this->messageManager->addSuccessMessage(__('The Payment has been deactivated.'));
                $this->_redirect('*/*/methods');
                return;
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $this->_redirect('*/*/methods');
                return;
            }
        // }
        $this->_redirect('*/*/methods');
    }
}
