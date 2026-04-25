<?php

namespace TCGCollective\SellerMembership\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;

class QuoteCollectTotalsBefore implements ObserverInterface
{
    protected $cartRepository;
    protected $storeManager;

    public function __construct(
        CartRepositoryInterface $cartRepository,
        StoreManagerInterface $storeManager
    ) {
        $this->cartRepository = $cartRepository;
        $this->storeManager = $storeManager;
    }

    /**
     * Add the notification if there are any customer awaiting for approval.
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        
$writer = new \Zend_Log_Writer_Stream(BP . '/var/log/QUOTE--DDD.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);

        $logger->info('===collect totals==');
        


        $quote = $observer->getEvent()->getQuote();

        $logger->info('quote: '.print_r($quote->getId(),true));

        if (!$quote) {
            return;
        }

        $hasMembership = false;
        $hasOtherProduct = false;

        foreach ($quote->getAllItems() as $item) {
            if ($item->getProductType() === 'seller_membership') {
                $hasMembership = true;

                $price = 120; // atau ambil dari product

        // 💥 semua field harus konsisten USD
        $item->setPrice($price);
        $item->setBasePrice($price);

        $item->setOriginalPrice($price);
        $item->setBaseOriginalPrice($price);

        $item->setCustomPrice($price);
        $item->setOriginalCustomPrice($price);

        $item->setRowTotal($price);
        $item->setBaseRowTotal($price);

        $item->getProduct()->setIsSuperMode(true);
            } else {
                $hasOtherProduct = true;
            }
        }

        $logger->info('hasMembership: '.print_r($hasMembership,true));
        $logger->info('hasOtherProduct: '.print_r($hasOtherProduct,true));

        // 🚫 block mixed cart (recommended)
        if ($hasMembership && $hasOtherProduct) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Membership harus dibeli terpisah.')
            );
        }

        // 🚀 force USD
        if ($hasMembership) {
            $quote->setBaseCurrencyCode('USD');
            $quote->setQuoteCurrencyCode('USD');
            $quote->setStoreCurrencyCode('USD');
            $quote->setGlobalCurrencyCode('USD');

            // biar gak di override lagi
            // $quote->getStore()->setCurrentCurrencyCode('USD');
            // $quote->getStore()->setDefaultCurrencyCode('USD');

            // $store = $quote->getStore();
            // // $store->setBaseCurrency('USD');        // override base currency
            // $store->setCurrentCurrencyCode('USD'); // override current currency
            // $store->setDefaultCurrencyCode('USD'); // kadang perlu ini juga
            $store = $quote->getStore();

// force store dulu
$store->setBaseCurrencyCode('USD');
$store->setCurrentCurrencyCode('USD');

// baru quote
$quote->setBaseCurrencyCode('USD');
$quote->setQuoteCurrencyCode('USD');

// paksa recalculation
        }

        $quote->setTotalsCollectedFlag(false);

        $logger->info('getBaseCurrencyCode: '.print_r($quote->getBaseCurrencyCode(),true));
        $logger->info('setStoreCurrencyCode: '.print_r($quote->getStoreCurrencyCode(),true));
        $logger->info('===collect totals==');
        // $quote->collectTotals();

        // Cek apakah cart berisi produk membership
        // $hasMembership = false;
        // foreach ($quote->getAllItems() as $item) {
        //     $product = $item->getProduct();
        //     // Bisa cek pakai attribute, SKU, atau product type
        //     if ($product->getData('is_membership') == 1 || strpos($product->getSku(), 'MEMBERSHIP') !== false) {
        //         $hasMembership = true;
        //         break;
        //     }
        // }

        // if ($hasMembership) {
            // Force ke USD
            // $quote->setBaseCurrencyCode('USD');
            // $quote->setStoreCurrencyCode('USD');
            // $quote->setQuoteCurrencyCode('USD');
            
            

            

        // }
        // $quote->setTotalsCollectedFlag(false)->collectTotals();

        // dd($quote->getData());
        // if ($this->_helperData->isEnabled()) {
        //     $orderids = $observer->getEvent()->getOrderIds();
        //     if (count($orderids) > 0 ) {
        //         foreach ($orderids as $_orderid) {
        //             /*Buy membership package*/
        //             $order = $this->_objectManager->create('Magento\Sales\Model\Order')->load($_orderid);
        //             $this->processBuyMembership($order);
        //         }
        //     }
        // }
        // $objectManager =  \Magento\Framework\App\ObjectManager::getInstance();
        // $this->checkoutSession = $objectManager->get('\Magento\Checkout\Model\Session');

        // $quote = $this->checkoutSession->getQuote();

        // if (!$quote || !$quote->getId()) {
        //     return; // tidak ada quote aktif, hindari error
        // }

        // // set currency di quote
        // $quote->setQuoteCurrencyCode('USD');

        // // optional: update current store currency
        // $this->storeManager->getStore()->setCurrentCurrencyCode('USD');
    }
}