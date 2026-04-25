<?php

namespace TCGCollective\MarketPlace\Controller\Marketplace\Payment;

use Magento\Framework\App\Action\Context;

class Methods extends \Magento\Framework\App\Action\Action
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
    protected $paymentHelper;

    /**
     * Index constructor.
     *
     * @param Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Lof\MarketPlace\Model\SellerFactory $sellerFactory
     * @param \Magento\Framework\Url $frontendUrl
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Lof\MarketPlace\Model\SellerFactory $sellerFactory,
        \Magento\Framework\Url $frontendUrl,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \TCGCollective\MarketPlace\Helper\Payments $paymentHelper
    ) {
        parent::__construct($context);

        $this->_frontendUrl = $frontendUrl;
        $this->_actionFlag = $context->getActionFlag();
        $this->sellerFactory = $sellerFactory;
        $this->session = $customerSession;
        $this->resultPageFactory = $resultPageFactory;
        $this->paymentHelper = $paymentHelper;
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
        $customerId = $customerSession->getId();
        $seller = $this->sellerFactory->create()->load($customerId, 'customer_id');
        
        $status = $seller->getStatus();

        if ($customerSession->isLoggedIn()) {
            $transactionId = $this->getRequest()->getParam('transaction_id');
            $method = $this->getRequest()->getParam('id');

            if ($transactionId) {
                $this->paymentHelper->invoiceSuccessXenditWebhook([
                    'external_id' => $transactionId,
                    'status' => 'PAID'
                ]);

                $account = $this->paymentHelper->getPaymentAccount($method);
                $account->setTestPaymentStatus('success')->save();

                $this->paymentHelper->createPlatformFeeRule($account);

                $this->paymentHelper->updatePaymentMethodStatus($method, 1);

                $this->messageManager->addSuccessMessage(__('Xendit payment method has been activated and is now ready to receive payments from buyers. You can view your balance and transaction details directly from your Xendit dashboard.'));

                if ($seller->getRegistrationStep() != "finish") {
                    $seller->setRegistrationStep('membership')
                    ->setNextStep('')
                    ->save();

                    return $this->_redirectUrl($this->getFrontendUrl().'marketplace/catalog/payment/methods');
                }

                return $this->_redirectUrl($this->getFrontendUrl().'marketplace/catalog/payment/methods');
            }

            $resultPage = $this->resultPageFactory->create();
            $resultPage->getConfig()->getTitle()->set(__('Payment Methods'));
            return $resultPage;
        } else {
            $this->messageManager->addNoticeMessage(__('You must have a seller account to access'));
            $this->_redirectUrl($this->getFrontendUrl('lofmarketplace/seller/login'));
        }
    }
}
