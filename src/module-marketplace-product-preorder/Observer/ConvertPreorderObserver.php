<?php

namespace TCGCollective\MarketplaceProductPreorder\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class ConvertPreorderObserver implements ObserverInterface
{
    public function execute(Observer $observer)
    {
        $quoteItem = $observer->getItem();
        $orderItem = $observer->getOrderItem();

        if ($quoteItem->getData('is_preorder')) {
            $orderItem->setData('is_preorder', 1);
            $orderItem->setData('pre_order_payment_type', $quoteItem->getData('pre_order_payment_type'));
            $orderItem->setData('pre_order_deposit', $quoteItem->getData('pre_order_deposit'));
        }

        return $this;
    }
}
