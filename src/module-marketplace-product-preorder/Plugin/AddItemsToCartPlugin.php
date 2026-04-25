<?php

namespace TCGCollective\MarketplaceProductPreorder\Plugin;

use Magento\Quote\Model\Cart\Data\CartItem;

class AddItemsToCartPlugin
{
    /**
     * Read preorder payload from GraphQL before adding to quote
     */
    public function beforeAddItemsToCart(
        \Magento\Quote\Model\Cart\AddProductsToCart $subject,
        \Magento\Quote\Model\Quote $quote,
        array $cartItems
    ) {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/addtocart.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info('beforeAddItemsToCart');

        foreach ($cartItems as $i => $cartItem) {

            // Ensure object type is CartItem
            if (!$cartItem instanceof CartItem) {
                continue;
            }

            $ext = $cartItem->getExtensionAttributes();

            if ($ext && $ext->getPreorder()) {

                $preorder = $ext->getPreorder();

                $cartItem->setData('is_preorder', 1);
                $cartItem->setData('pre_order_payment_type', $preorder->getPreOrderPaymentType());
                $cartItem->setData('pre_order_deposit', $preorder->getPreOrderDeposit());

                $cartItems[$i] = $cartItem; // update entry
            }
        }

        return [$quote, $cartItems];
    }
}
