<?php

namespace TCGCollective\MarketPlace\Plugin\Customer\Api\CustomerRepository;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Lof\MarketPermissions\Model\Customer\SellerAttributes;
use Lof\MarketPermissions\Api\SellerRepositoryInterface;

/**
 * A plugin for customer save operation for processing seller routines.
 */
class Save
{
    /**
     * @var \Lof\MarketPermissions\Model\Customer\SellerAttributes
     */
    private $customerSaveAttributes;

    /**
     * @var \Lof\MarketPermissions\Api\SellerRepositoryInterface
     */
    private $sellerRepository;
    private $state;

    /**
     * @param SellerAttributes $customerSaveAttributes
     * @param SellerRepositoryInterface $sellerRepository
     */
    public function __construct(
        SellerAttributes $customerSaveAttributes,
        SellerRepositoryInterface $sellerRepository,
        \Magento\Framework\App\State $state
    ) {
        $this->customerSaveAttributes = $customerSaveAttributes;
        $this->sellerRepository = $sellerRepository;
        $this->state = $state;
    }

    /**
     * Before customer save.
     *
     * @param CustomerRepositoryInterface $customerRepository
     * @param CustomerInterface $customer
     * @param null $passwordHash [optional]
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSave(
        CustomerRepositoryInterface $customerRepository,
        CustomerInterface $customer,
        $passwordHash = null
    ) {
        $areaCode = $this->getAreaCode();
        if ($areaCode != "graphql") {
            $this->customerSaveAttributes->updateSellerAttributes($customer);
            $customer = $this->setCustomerGroup($customer);
        }
        
        return [$customer, $passwordHash];
    }

    public function getAreaCode() 
    {
        try {
            $areaCode = $this->state->getAreaCode();
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $areaCode = 'frontend';
        }

        return $areaCode;
    }

    /**
     * Set customer group.
     *
     * @param CustomerInterface $customer
     * @return CustomerInterface
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function setCustomerGroup(CustomerInterface $customer)
    {
        $sellerId = $this->customerSaveAttributes->getSellerId();
        if ($sellerId) {
            $seller = $this->sellerRepository->get($sellerId);
            $customer->setGroupId($seller->getGroupId());
        }
        return $customer;
    }

    /**
     * After customer save.
     *
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     * @return CustomerInterface
     * @throws CouldNotSaveException
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterSave(
        CustomerRepositoryInterface $customerRepository,
        CustomerInterface $customer
    ) {
        $areaCode = $this->getAreaCode();
        if ($areaCode != "graphql") {
            $this->customerSaveAttributes->saveSellerAttributes($customer);
        }
        return $customer;
    }
}
