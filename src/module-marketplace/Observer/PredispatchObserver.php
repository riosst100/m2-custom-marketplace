<?php

namespace TCGCollective\MarketPlace\Observer;

use Magento\Backend\App\AbstractAction;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class PredispatchObserver implements ObserverInterface
{
    protected $urlBuilder;


    const FLAG_IS_URLS_CHECKED = 'check_url_settings';

    /**
     * @var AbstractAction|null
     */
    private $action;

    /**
     * @var \Magento\Framework\Url
     */
    protected $_frontendUrl;

    /**
     * @var \Lof\MarketPlace\Model\SellerFactory
     */
    protected $sellerFactory;

    /**
     * @var \Magento\Framework\App\ActionFlag
     */
    protected $actionFlag;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $session;

    /**
     * @var \Magento\Framework\App\State
     */
    protected $state;

    /**
     * @var \TCGCollective\MarketPlace\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * PredispatchObserver constructor.
     * @param \Lof\MarketPlace\Model\SellerFactory $sellerFactory
     * @param \Magento\Framework\Url $frontendUrl
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\App\ActionFlag $actionFlag
     * @param \Magento\Framework\App\State $state
     * @param \TCGCollective\MarketPlace\Helper\Data $helper
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     */
    public function __construct(
        \Lof\MarketPlace\Model\SellerFactory $sellerFactory,
        \Magento\Framework\Url $frontendUrl,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\ActionFlag $actionFlag,
        \Magento\Framework\App\State $state,
        \TCGCollective\MarketPlace\Helper\Data $helper,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        $this->sellerFactory = $sellerFactory;
        $this->_frontendUrl = $frontendUrl;
        $this->session = $customerSession;
        $this->actionFlag = $actionFlag;
        $this->state = $state;
        $this->helper = $helper;
        $this->messageManager = $messageManager;
    }

    /**
     * @param Observer $observer
     * @return PredispatchObserver|void
     */
    public function execute(Observer $observer)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->urlBuilder = $objectManager->create(\Magento\Framework\UrlInterface::class);

        $controllerAction = $observer->getData('controller_action');
        $this->action = $controllerAction;

        $area = $this->state->getAreaCode();
        if ($area === 'marketplace') {
            $customerSession = $this->session;
            $customerId = $customerSession->getId();
            $seller = $this->sellerFactory->create()->load($customerId, 'customer_id');
            $status = $seller->getStatus();

            $routeName = $this->action->getRequest()->getRouteName();
            $controllerName = $this->action->getRequest()->getControllerName();
            $actionName = $this->action->getRequest()->getActionName();
            
            $currentUrl = $routeName.'/'.$controllerName.'/'.$actionName;

            /* Auto set store based on seller country */
            $sellerCountryID = $seller->getDefaultWebsiteCode() ? strtolower($seller->getDefaultWebsiteCode()) : null;

            // die('sellerCountryID: '.$sellerCountryID);

            // $this->helper->setStoreBySellerCountry($sellerCountryID);
            /* Auto set store based on seller country */
            // if ($sellerCountryID != "base") {
                $newUrl = $this->helper->autoSelectWebsite($sellerCountryID);
                // dd($newUrl);
                if ($newUrl && $controllerName . '/' . $actionName != "seller/login") {
                    // if ($newUrl == "marketplace/catalog/dashboard") {
                    //     dd($customerSession->getiD());
                    // }
                    // if (str_contains($newUrl, "marketplace/catalog/dashboard")) {
                    //     $newUrl .= '?cust_id=' . $customerId;
                    // }
                    // if ($currentUrl ==  "lofmarketplace/seller/loginPost") {
                    //     $this->session->setCustomerDataAsLoggedIn($customerSession->getCustomer());
                    //     $this->session->regenerateId();

                    //     $newUrlArr = explode("/", $newUrl);

                    //     dd($newUrlArr);

                    //     $newUrl = 'marketplace/catalog/dashboard';
                    // }
                    // dd($newUrl);
                    return $this->_redirectUrl($newUrl);
                }
            // }

            if ($customerSession->isLoggedIn()) {
                if (
                    ($seller->getRegistrationStep() == "verification_membership" 
                    || $seller->getRegistrationStep() == "verification_address") 
                    && !str_contains($currentUrl, "catalog/seller/downloadIdentify") 
                    && $routeName != "sellerverification"
                ) {
                    return $this->_redirectUrl($this->getFrontendUrl('marketplace/sellerverification/index/index'));
                }
                // dd($currentUrl);
                // dd($seller->getRegistrationStep());
                // marketplace/sellerverification/index/save/
                if ($seller->getRegistrationStep() == "verification" && $routeName != "sellerverification" && !str_contains($currentUrl, "catalog/seller/downloadIdentify") && !str_contains($currentUrl, "catalog/payment/methods") && !$seller->getNextStep()) {
                    if (str_contains($currentUrl, "catalog/payment/methods")) {
                        return $this->_redirectUrl($this->getFrontendUrl('marketplace/catalog/step/nextStep/id/payment'));
                    }
                    return $this->_redirectUrl($this->getFrontendUrl('marketplace/sellerverification/index/index'));
                } elseif ($seller->getRegistrationStep() == "payment" && !str_contains($currentUrl, "catalog/payment") && $routeName != "sellerverification") {
                    return $this->_redirectUrl($this->urlBuilder->getUrl('catalog/payment/methods'));
                } elseif ($seller->getRegistrationStep() == "membership" && $routeName != "sellerverification" && $currentUrl != "sellermembership/membership/buy" && $currentUrl != "catalog/payment/methods" && $currentUrl != "catalog/payment/savemethods" && $currentUrl != "catalog/payment/deactivate" && $currentUrl != "catalog/payment/activate") {
                    // dd($this->urlBuilder->getUrl('lofmpsellermembership/membership/buy'));
                    return $this->_redirectUrl($this->urlBuilder->getUrl('sellermembership/membership/buy'));
                }
            }
        }

        return $this;
    }

    /**
     * Redirect to URL
     * @param string $url
     * @return \Magento\Framework\App\ResponseInterface
     */
    protected function _redirectUrl($url)
    {
        $this->action->getResponse()->setRedirect($url);
        $this->session->setIsUrlNotice($this->actionFlag->get('', self::FLAG_IS_URLS_CHECKED));
        return $this->action->getResponse();
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
}
