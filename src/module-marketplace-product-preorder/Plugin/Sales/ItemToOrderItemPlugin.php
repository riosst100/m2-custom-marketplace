<?php

namespace TCGCollective\MarketplaceProductPreorder\Plugin\Sales;

class ItemToOrderItemPlugin
{
    public function aroundConvert(
        \Magento\Quote\Model\Quote\Item\ToOrderItem $subject,
        callable $proceed,
        \Magento\Quote\Model\Quote\Item\AbstractItem $quoteItem
    ) {
        $orderItem = $proceed($quoteItem);

        $orderItem->setData('is_preorder', $quoteItem->getData('is_preorder'));
        $orderItem->setData('pre_order_payment_type', $quoteItem->getData('pre_order_payment_type'));
        $orderItem->setData('pre_order_deposit', $quoteItem->getData('pre_order_deposit'));

        return $orderItem;
    }
}
