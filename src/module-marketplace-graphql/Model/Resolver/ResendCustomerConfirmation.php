<?php
namespace TCGCollective\MarketplaceGraphQl\Model\Resolver;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;

class ResendCustomerConfirmation implements ResolverInterface
{
    /**
     * @var AccountManagementInterface
     */
    protected $accountManagement;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    public function __construct(
        AccountManagementInterface $accountManagement,
        CustomerRepositoryInterface $customerRepository,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager
    ) {
        $this->accountManagement = $accountManagement;
        $this->customerRepository = $customerRepository;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
    }

    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (empty($args['email'])) {
            throw new GraphQlInputException(__('Email is required.'));
        }

        $email = trim($args['email']);

        try {
            $customer = $this->customerRepository->get($email);
        } catch (NoSuchEntityException $e) {
            // Same behavior as frontend — don’t reveal user existence
            return [
                'success' => true,
                'message' => __('If there is an account associated with %1, you will receive a confirmation email.', $email)
            ];
        }

        try {
            if ($customer->getConfirmation() === null) {
                // Already confirmed
                return [
                    'success' => true,
                    'message' => __('This account is already confirmed. You can sign in now.')
                ];
            }

            // Send confirmation email again
            $this->accountManagement->resendConfirmation($email);

            return [
                'success' => true,
                'message' => __('We have sent another confirmation email to your inbox.')
            ];

        } catch (StateException $e) {
            return [
                'success' => false,
                'message' => __('This account is already confirmed. You can sign in now.')
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => __('We could not send a new confirmation email. Please try again later.')
            ];
        }
    }
}
