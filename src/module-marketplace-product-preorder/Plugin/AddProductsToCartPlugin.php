<?php

namespace TCGCollective\MarketplaceProductPreorder\Plugin;

use Magento\Quote\Api\Data\CartItemInterface;

class AddProductsToCartPlugin
{
    public function beforeAddProducts(
        \Magento\Quote\Model\Quote $quote,
        array $cartItems
    ) {
        foreach ($cartItems as $cartItem) {

            if (!$cartItem instanceof CartItemInterface) {
                continue;
            }

            $extAttributes = $cartItem->getExtensionAttributes();
            if (!$extAttributes || !$extAttributes->getPreorder()) {
                continue;
            }

            $preorder = $extAttributes->getPreorder();

            $paymentType = $preorder->getPreOrderPaymentType();
            $deposit     = $preorder->getPreOrderDeposit();

            // Debugging
            // error_log("PREORDER >> $paymentType | $deposit");

            /**
             * OPTIONAL:
             * Attach preorder info to quote to process later during addProduct()
             */
            $quote->setData('current_preorder_payment_type', $paymentType);
            $quote->setData('current_preorder_deposit', $deposit);
        }

        return [$quote, $cartItems];
    }
}
