<?php
namespace TCGCollective\MarketplaceGraphQl\Model\Resolver;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Helper\Address;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Model\Logger as CustomerLogger;

class ConfirmCustomerEmail implements ResolverInterface
{
    protected $accountManagement;
    protected $customerRepository;
    protected $addressHelper;
    protected $scopeConfig;
    protected $storeManager;
    protected $session;
    protected $customerLogger;

    public function __construct(
        AccountManagementInterface $accountManagement,
        CustomerRepositoryInterface $customerRepository,
        Address $addressHelper,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        Session $session,
        CustomerLogger $customerLogger
    ) {
        $this->accountManagement = $accountManagement;
        $this->customerRepository = $customerRepository;
        $this->addressHelper = $addressHelper;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->session = $session;
        $this->customerLogger = $customerLogger;
    }

    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (empty($args['id']) || empty($args['key'])) {
            throw new GraphQlInputException(__('Both "id" and "key" are required.'));
        }

        $customerId = (int)$args['id'];
        $key = $args['key'];

        try {
            // Get customer email from ID
            $customerEmail = $this->customerRepository->getById($customerId)->getEmail();

            // Activate account (same as controller logic)
            $customer = $this->accountManagement->activate($customerEmail, $key);

            // Log the user in (optional if you want PWA auto-login behavior)
            $this->session->setCustomerDataAsLoggedIn($customer);

            // Build success message (same as controller getSuccessMessage)
            $successMessage = $this->getSuccessMessage($customerId);

            return [
                'success' => true,
                'message' => $successMessage ?? __('Your account has been confirmed successfully.')
            ];

        } catch (StateException $e) {
            return [
                'success' => false,
                'message' => __('This confirmation key is invalid or has expired.')
            ];
        } catch (NoSuchEntityException $e) {
            return [
                'success' => false,
                'message' => __('Customer not found.')
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => __('There was an error confirming the account.')
                // 'message' => __($e->getMessage())
            ];
        }
    }

    /**
     * Replicates getSuccessMessage() logic from frontend controller
     */
    protected function getSuccessMessage($customerId)
    {
        if ($this->addressHelper->isVatValidationEnabled()) {
            return __(
                $this->addressHelper->getTaxCalculationAddressType() == Address::TYPE_SHIPPING
                    ? 'If you are a registered VAT customer, please enter your shipping address for proper VAT calculation.'
                    : 'If you are a registered VAT customer, please enter your billing address for proper VAT calculation.'
            );
        }

        if ($customerId && $this->customerLogger->get($customerId)->getLastLoginAt()) {
            return null;
        }

        return __('Thank you for registering with %1.', $this->storeManager->getStore()->getFrontendName());
    }
}
