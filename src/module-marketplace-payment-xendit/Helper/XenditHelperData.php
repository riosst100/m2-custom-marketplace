<?php

namespace TCGCollective\MarketplacePaymentXendit\Helper;

use Magento\Sales\Model\Order;

class XenditHelperData extends \Xendit\M2Invoice\Helper\Data
{
    public function extractXenditInvoiceCustomerFromOrder(Order $order): array
    {
        $shippingAddress = $order->getShippingAddress();
        $customerObject = [
            'given_names' => $order->getCustomerFirstname(),
            'surname' => $order->getCustomerLastname(),
            'email' => $order->getCustomerEmail()
        ];

        if (!$shippingAddress) {
            $shippingAddress = $order->getBillingAddress();
        }

        if ($shippingAddress) {
            $mobileNumber = $this->phoneNumberFormatHelper->formatNumber($shippingAddress->getTelephone(), $shippingAddress->getCountryId());
            if (!empty($mobileNumber)) {
                $customerObject['mobile_number'] = $mobileNumber;
            }

            $customerObject = array_filter($customerObject);
            $addressObject = $this->extractXenditInvoiceCustomerAddress($shippingAddress);
            if (!empty($addressObject)) {
                $customerObject['addresses'] = [$addressObject];
            }
        }

        return $customerObject;
    }
}
