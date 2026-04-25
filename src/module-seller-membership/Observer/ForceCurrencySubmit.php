<?php

namespace TCGCollective\SellerMembership\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class ForceCurrencySubmit implements ObserverInterface
{
    public function execute(Observer $observer)
    {
        $quote = $observer->getEvent()->getQuote();

        if (!$quote || !$quote->getItemsCount()) {
            return;
        }

        $hasMembership = false;

        foreach ($quote->getAllItems() as $item) {
            if ($item->getProductType() === 'membership') {
                $hasMembership = true;
                break;
            }
        }

        if (!$hasMembership) {
            return;
        }

        // 🚀 FORCE USD (final lock sebelum jadi order)
        $quote->setBaseCurrencyCode('USD');
        $quote->setQuoteCurrencyCode('USD');
        $quote->setStoreCurrencyCode('USD');
        $quote->setGlobalCurrencyCode('USD');

        $quote->getStore()->setCurrentCurrencyCode('USD');

        // 🔥 extra safety: update semua item price
        foreach ($quote->getAllItems() as $item) {
            $item->setBasePrice($item->getPrice());
            $item->setOriginalCustomPrice($item->getPrice());
            $item->setCustomPrice($item->getPrice());
        }
    }
}