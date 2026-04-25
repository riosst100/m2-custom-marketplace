<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace TCGCollective\MarketplaceGraphQl\Model\Order;

use Magento\Sales\Api\Data\OrderAddressInterface;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * Class to get the order address details
 */
class OrderAddress extends \Magento\SalesGraphQl\Model\Order\OrderAddress
{
    public function defaultBlankAddress() 
    {
        return [
            'firstname' => '',
            'lastname' => '',
            'middlename' => '',
            'region' => '',
            'region_id' => '',
            'country_code' => null,
            'street' => [''],
            'company' => '',
            'telephone' => '',
            'fax' => '',
            'postcode' => '',
            'city' => '',
            'prefix' => '',
            'suffix' => '',
            'vat_id' => ''
        ];
    }

    /**
     * Get the order Shipping address
     *
     * @param OrderInterface $order
     * @return array|null
     */
    public function getOrderShippingAddress(
        OrderInterface $order
    ): ?array {
        $shippingAddress = $this->defaultBlankAddress();
        if ($order->getShippingAddress()) {
            $shippingAddress = $this->formatAddressData($order->getShippingAddress());
        }
        return $shippingAddress;
    }

    /**
     * Get the order billing address
     *
     * @param OrderInterface $order
     * @return array|null
     */
    public function getOrderBillingAddress(
        OrderInterface $order
    ): ?array {
        $billingAddress = $this->defaultBlankAddress();
        if ($order->getBillingAddress() && $order->getBillingAddress()->getCity()) {
            $billingAddress = $this->formatAddressData($order->getBillingAddress());
        }
        return $billingAddress;
    }

    /**
     * Customer Order address data formatter
     *
     * @param OrderAddressInterface $orderAddress
     * @return array
     */
    private function formatAddressData(
        OrderAddressInterface $orderAddress
    ): array {
        return
            [
                'firstname' => $orderAddress->getFirstname(),
                'lastname' => $orderAddress->getLastname(),
                'middlename' => $orderAddress->getMiddlename(),
                'postcode' => $orderAddress->getPostcode(),
                'prefix' => $orderAddress->getPrefix(),
                'suffix' => $orderAddress->getSuffix(),
                'street' => $orderAddress->getStreet(),
                'country_code' => $orderAddress->getCountryId(),
                'city' => $orderAddress->getCity(),
                'company' => $orderAddress->getCompany(),
                'fax' => $orderAddress->getFax(),
                'telephone' => $orderAddress->getTelephone(),
                'vat_id' => $orderAddress->getVatId(),
                'region_id' => $orderAddress->getRegionId(),
                'region' => $orderAddress->getRegion()
            ];
    }
}
