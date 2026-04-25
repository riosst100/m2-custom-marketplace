<?php
declare(strict_types=1);

namespace TCGCollective\AgeVerification\Model\Resolver;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;

class SaveDob implements ResolverInterface
{
    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    public function __construct(
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->customerRepository = $customerRepository;
    }

    /**
     * Save logged-in customer's DOB
     *
     * @param Field $field
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        // $context here implements \Magento\GraphQl\Model\Query\ContextInterface
        if (!$context->getUserId()) {
            throw new GraphQlAuthorizationException(__('Customer must be logged in to save DOB.'));
        }

        $customerId = (int)$context->getUserId();
        $dob = $args['dob'] ?? null;

        if (!$dob) {
            throw new GraphQlInputException(__('DOB is required. Use format YYYY-MM-DD.'));
        }

        try {
            $customer = $this->customerRepository->getById($customerId);
            $customer->setDob($dob);
            $this->customerRepository->save($customer);

            return [
                'success' => true,
                'message' => __('Customer date of birth saved successfully.')
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => __('Something went wrong while saving the dob.')
            ];
        }
    }
}
