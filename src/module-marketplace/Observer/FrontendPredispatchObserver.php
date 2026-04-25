<?php

namespace TCGCollective\MarketPlace\Observer;

use Magento\Backend\App\AbstractAction;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class FrontendPredispatchObserver implements ObserverInterface
{
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
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \TCGCollective\MarketPlace\Helper\Data
     */
    protected $helper;

    protected $storeManager;

    protected $messageSession;

    /**
     * PredispatchObserver constructor.
     * @param \Lof\MarketPlace\Model\SellerFactory $sellerFactory
     * @param \Magento\Framework\Url $frontendUrl
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\App\ActionFlag $actionFlag
     * @param \Magento\Framework\App\State $state
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \TCGCollective\MarketPlace\Helper\Data $helper
     */
    public function __construct(
        \Lof\MarketPlace\Model\SellerFactory $sellerFactory,
        \Magento\Framework\Url $frontendUrl,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\ActionFlag $actionFlag,
        \Magento\Framework\App\State $state,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \TCGCollective\MarketPlace\Helper\Data $helper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Message\Session $messageSession
    ) {
        $this->sellerFactory = $sellerFactory;
        $this->_frontendUrl = $frontendUrl;
        $this->session = $customerSession;
        $this->actionFlag = $actionFlag;
        $this->state = $state;
        $this->messageManager = $messageManager;
        $this->helper = $helper;
        $this->storeManager = $storeManager;
        $this->messageSession = $messageSession;
    }

    

    /**
     * @param Observer $observer
     * @return PredispatchObserver|void
     */
    public function execute(Observer $observer)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->_urlBuilder = $objectManager->create('Magento\Framework\UrlInterface');

        $currentUrl = $this->_urlBuilder->getCurrentUrl();
        if (str_contains($currentUrl, 'airwallex')) {
            return $this;
        }

        $controllerAction = $observer->getData('controller_action');
    //     if ($controllerAction) {
            $this->action = $controllerAction;

            $area = $this->state->getAreaCode();
            $currentWebsiteUrl = $this->helper->getCurrentUrl();

            if (str_contains($currentWebsiteUrl, "lofmarketplace/catalog/dashboard")) {
                $this->_redirectUrl($this->getFrontendUrl('marketplace/catalog/dashboard'));
            }

            $routeName = $this->action->getRequest()->getRouteName();
            $controllerName = $this->action->getRequest()->getControllerName();
            $actionName = $this->action->getRequest()->getActionName();

            $currentUrl = $routeName.'/'.$controllerName.'/'.$actionName;

            if ($area === 'frontend' && !str_contains($currentWebsiteUrl, 'paypal/') && !str_contains($currentWebsiteUrl, 'checkout/') && !str_contains($currentWebsiteUrl, 'xendit/') && !str_contains($currentWebsiteUrl, "loginascustomer/login/index")) {

                
                if ($currentUrl == "/seller/login") {
                    $websiteCode = $this->storeManager->getStore()->getWebsite()->getCode();
                    if ($websiteCode != "base") {
                        $newCurrentWebsiteUrl = str_replace($websiteCode . '/', "", $currentWebsiteUrl);
                        // return $this->_redirectUrl($newCurrentWebsiteUrl);
                    }
                }

                $customerSession = $this->session;
                $customerId = $customerSession->getId();
                $seller = $this->sellerFactory->create()->load($customerId, 'customer_id');
                $status = $seller->getStatus();

                /* Auto set store based on seller country */
                $sellerCountryID = $seller->getDefaultWebsiteCode() ? strtolower($seller->getDefaultWebsiteCode()) : null;
                // if (
                //     !str_contains($currentUrl, "checkout") &&
                //     !str_contains($currentUrl, "paypal/express") &&
                //     $currentUrl != "customer/section/load"
                // ) {
                // if ($sellerCountryID != "base") {
                    $newUrl = $this->helper->autoSelectWebsite($sellerCountryID);
                    if ($newUrl) {
                        if ($currentUrl != "/seller/login") {
                            if ($currentUrl == "cms/index/index") {
                                $newUrl .= 'marketplace/catalog/dashboard';
                            }
                            
                            return $this->_redirectUrl($newUrl);
                        }
                    }
                // }
                // }
                /* Auto set store based on seller country */

                $allowedUrl = [
                    'paypal/express/getTokenData',
                    'customer/section/load',
                    'checkout/index/index',
                    'checkout/cart/add',
                    'checkout/onepage/success',
                    'paypal/express/onAuthorization',
                    'paypal/express/cancel',
                    '/seller/login',
                    'lofmarketplace/seller/login',
                    'lofmarketplace/seller/loginPost',
                    'loginascustomer/login/index',
                    'xendit/checkout/notification',
                    'xendit/checkout/success',
                    'xendit/checkout/failure',
                    'xendit/checkout/invoice',
                    'xendit/checkout/redirect',
                    'checkout/cart/index'
                ];

                if ($currentUrl == "customer/account/logoutSuccess") {
                    return $this->_redirectUrl($this->helper->getPwaBaseUrl());
                }

                if ($currentUrl == "checkout/onepage/success") {
                    $this->messageSession->getMessages(true);
                    return $this->_redirectUrl($this->getFrontendUrl('marketplace/lofmpsellermembership/membership/index').'?success=true');
                }

                // dd($currentWebsiteUrl);

                // if (!str_contains($currentUrl, 'airwallex')) {
                    if ($currentUrl == "/noroute/index" || !in_array($currentUrl, $allowedUrl) && !str_contains($currentWebsiteUrl, 'documentation') && !str_contains($currentWebsiteUrl, 'loffaq')) {
                        return $this->_redirectUrl($this->getFrontendUrl('lofmarketplace/seller/login'));
                    }
                // }
            }
        // }

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
