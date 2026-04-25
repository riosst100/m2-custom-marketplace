<?php
namespace TCGCollective\SellerMembership\Controller\Marketplace\Autologin;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session;
use TCGCollective\SellerMembership\Helper\AutoLogin;
use Magento\Customer\Api\CustomerRepositoryInterface;

class Index extends Action
{
    protected $session;
    protected $autoLoginHelper;
    protected $customerRepository;

    public function __construct(
        Context $context,
        Session $session,
        AutoLogin $autoLoginHelper,
        CustomerRepositoryInterface $customerRepository
    ) {
        parent::__construct($context);
        $this->session = $session;
        $this->autoLoginHelper = $autoLoginHelper;
        $this->customerRepository = $customerRepository;
    }

    public function execute()
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/successPage.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        // $logger->info('Controller Autologin');

        $token = $this->getRequest()->getParam('autologin_token');
        $redirectUrl = $this->getRequest()->getParam('redirect', 'marketplace/lofmpsellermembership/membership/index');

        if ($token && !$this->session->isLoggedIn()) {
            $customerId = $this->autoLoginHelper->validateToken($token);
            if ($customerId) {
                try {
                    $customer = $this->customerRepository->getById($customerId);
                    $this->session->setCustomerDataAsLoggedIn($customer);
                } catch (\Exception $e) {
                    // ignore
                }
            }
        }
        // $logger->info('Redirect');
        return $this->_redirect($redirectUrl . '?success=true');
    }
}
