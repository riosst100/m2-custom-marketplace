<?php

namespace TCGCollective\MarketplacePaymentXendit\Model\Payment;

use Xendit\M2Invoice\Model\Payment\AbstractInvoice;
use Magento\Quote\Api\Data\CartInterface;

class AbstractInvoicePlugin
{
    protected $marketplaceData;

    protected $marketplaceConfig;

    public function __construct(
        \Lof\MarketPlace\Helper\Data $marketplaceData,
        \Lof\MarketPlace\Model\ConfigFactory $marketplaceConfig
    ) {
        $this->marketplaceData = $marketplaceData;
        $this->marketplaceConfig = $marketplaceConfig;
    }

    /**
     * After plugin for isAvailable()
     *
     * @param AbstractInvoice $subject
     * @param bool $result
     * @param CartInterface|null $quote
     * @return bool
     */
    public function afterIsAvailable(AbstractInvoice $subject, $result, CartInterface $quote = null)
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/payment.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        // $logger->info('isAvailable xendit Plugin'); 

        // If the original function returned false, don't change anything
        if ($result === false) {
            return $result;
        }

        // custom logic here        
        $quotes = [];
        foreach ($quote->getAllItems() as $item) {
            $product = $item->getProduct()->load($item->getProductId());
            if ($item->getParentItem() || $product->isVirtual()) {
                continue;
            }

            // Identify seller
            $sellerId = $item->getSellerId() ?: $product->getSellerId() ?: 'no_seller';
            
            if (!isset($quotes[$sellerId])) {
                $quotes[$sellerId] = [];
            }
            $quotes[$sellerId][] = $item;
        }

        $config = null;

        // Process each seller’s items 
        if ($quotes) {       
            foreach ($quotes as $sellerId => $items) {            
                // $logger->info("Check xendit for seller: $sellerId");
                try {
                    $key = 'payment/xendit/active';
                    $keyTable = $this->marketplaceData->getTableKey('key');
                    $sellerKeyTable = $this->marketplaceData->getTableKey('seller_id');
                    $config = $this->marketplaceConfig->create()
                        ->loadByField([$keyTable, $sellerKeyTable], [$key, $sellerId])->getValue();
                } catch (\Exception $e) {
                    $config = 0;
                }
            }
        }
        
        if ($config) {
            return true;
        } else {
            return false;
        }
    }
}