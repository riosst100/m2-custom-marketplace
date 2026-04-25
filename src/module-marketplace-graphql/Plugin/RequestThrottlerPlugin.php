<?php
namespace TCGCollective\MarketplaceGraphQl\Plugin;

use Magento\Integration\Model\Oauth\Token\RequestThrottler;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Customer\Api\CustomerRepositoryInterface;

class RequestThrottlerPlugin
{
    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    public function __construct(CustomerRepositoryInterface $customerRepository)
    {
        $this->customerRepository = $customerRepository;
    }

    /**
     * Around plugin for throttle()
     *
     * @param RequestThrottler $subject
     * @param callable $proceed
     * @param string $userName
     * @param int $userType
     * @throws AuthenticationException
     */
    public function aroundThrottle(RequestThrottler $subject, callable $proceed, $userName, $userType)
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/signin.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);        

        // Check if the user exists
        try {
            $customer = $this->customerRepository->get($userName);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {            
            // User not found — proceed as normal (generic message)
            return $proceed($userName, $userType);
        }

        // Check if not confirmed
        if ($customer->getConfirmation()) {            
            throw new AuthenticationException(
                __("Your account hasn't been confirmed yet.  Please check your email and click the confirmation link to activate your account.")
            );
        }

        // Continue with default logic
        return $proceed($userName, $userType);
    }
}
