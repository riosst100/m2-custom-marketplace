<?php

namespace TCGCollective\MarketplaceProductPreorder\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Registry;
use Magento\Framework\App\RequestInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\Message\ManagerInterface;
use Lof\PreOrder\Model\ItemFactory;
use Lof\PreOrder\Helper\Data as PreorderHelper;
use Lof\PreOrder\Model\ResourceModel\Item\CollectionFactory as Items;
use Lof\PreOrder\Model\ResourceModel\PreOrder\CollectionFactory as PreorderCollection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as Products;
use Magento\Checkout\Model\CartFactory;
use Magento\Quote\Model\QuoteFactory;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Store\Model\StoreManagerInterface;

class SetPreorderData implements ObserverInterface
{
    protected $registry;
    protected $request;
    protected $customerSession;
    protected $messageManager;
    protected $itemFactory;
    protected $preorderHelper;
    protected $itemCollection;
    protected $preorderCollection;
    protected $productCollection;
    protected $cart;
    protected $quoteFactory;
    protected $priceCurrency;
    protected $storeManager;

    public function __construct(
        Registry $registry,
        RequestInterface $request,
        Session $customerSession,
        ManagerInterface $messageManager,
        ItemFactory $itemFactory,
        PreorderHelper $preorderHelper,
        Items $itemCollection,
        PreorderCollection $preorderCollection,
        Products $productCollection,
        CartFactory $cart,
        QuoteFactory $quoteFactory,
        PriceCurrencyInterface $priceCurrency,
        StoreManagerInterface $storeManager
    ) {
        $this->registry            = $registry;
        $this->request             = $request;
        $this->customerSession     = $customerSession;
        $this->messageManager      = $messageManager;
        $this->itemFactory         = $itemFactory;
        $this->preorderHelper      = $preorderHelper;
        $this->itemCollection      = $itemCollection;
        $this->preorderCollection  = $preorderCollection;
        $this->productCollection   = $productCollection;
        $this->cart                = $cart;
        $this->quoteFactory        = $quoteFactory;
        $this->priceCurrency       = $priceCurrency;
        $this->storeManager        = $storeManager;
    }

    public function execute(Observer $observer)
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/preorder.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info("Merged observer triggered");

        $event     = $observer->getEvent();
        $quoteItem = $event->getData('quote_item') ?: $event->getItem();
        $product   = $event->getData('product') ?: $quoteItem->getProduct();
        $cartId    = $event->getData('quote_id');

        if (!$quoteItem || !$product) {
            return;
        }

        $sku = $product->getSku();

        /**
         * --------------------------------------------------------------
         *  PART 1 - Your logic: Set preorder custom fields on quote item
         * --------------------------------------------------------------
         */
        $map = $this->registry->registry('preorder_data');
        // dd($sku);
        if ($map && isset($map[$sku])) {
            $data = $map[$sku];

            $quoteItem->setData('is_preorder', 1);
            $quoteItem->setData('pre_order_payment_type', $data['pre_order_payment_type']);
            $quoteItem->setData('pre_order_deposit', $data['pre_order_deposit']);

            $logger->info("Preorder data applied for SKU {$sku}");        

            /**
             * ------------------------------------------------------
             *  PART 2 - Lof PreOrder logic (CustomPrice observer)
             * ------------------------------------------------------
             */
            try {
                $helper                    = $this->preorderHelper;
                $productId                 = $this->getFinalProductId($product);
                $preorderCompleteProductId = $helper->getPreorderCompleteProductId();

                if ($product->getTypeId() == \Magento\GroupedProduct\Model\Product\Type\Grouped::TYPE_CODE) {
                    // Do nothing
                } elseif ($product->getTypeId() == \Magento\Bundle\Model\Product\Type::TYPE_CODE) {
                    $logger->info("Bundle product detected, skipping");
                } else {

                    // Warning message if needed
                    $cartWarningMsg = $helper->getMsgWarningQtyInCart(
                        $productId,
                        $product->getName(),
                        $quoteItem->getQty()
                    );

                    if ($cartWarningMsg) {
                        $quoteItem->setMessage($cartWarningMsg);
                    }

                    // Apply preorder price logic
                    $paymentType = $data['pre_order_payment_type'];
                    $depositPercent = $data['pre_order_deposit'];

                    if ($paymentType == 'deposit') {
                        $mageQuoteModel = $this->quoteFactory->create()->load((int)$cartId);
                        // dd($quoteItem->getId());
                        // $quoteItem = $mageQuoteModel->getItemById($quoteItem->getId());
                        $this->setPreorderPrice($quoteItem, $product, $productId, $depositPercent);

                        $mageQuoteModel->setTotalsCollectedFlag(false);
                        $mageQuoteModel->collectTotals();
                        $mageQuoteModel->save();

                        // Handle preorder-complete product flow
                        $data = $this->registry->registry('preorder_graphql_input');
                        if ($productId == $preorderCompleteProductId && $data) {
                            $result = $this->processPreorderCompleteData($quoteItem);
                            if ($result['error']) {
                                $this->messageManager->addNotice(__($result['msg']));
                                throw new \Magento\Framework\Exception\LocalizedException($result['msg']);
                            }
                        }

                        // Add preorder message block
                        // if ($helper->isPreorder($productId)) {
                            $msg = $helper->getPreOrderInfoBlock($productId, $product);
                            if ($msg) {
                                $quoteItem->setPreorderMsg($msg);
                            }
                        // }   
                    }                    
                }
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $logger->info("catch: ".$e->getMessage());
                $this->messageManager->addError($e->getMessage());
            }
        } else {
            try {
                $helper                    = $this->preorderHelper;
                $productId                 = $this->getFinalProductId($product);
                $preorderCompleteProductId = $helper->getPreorderCompleteProductId();

                if ($product->getTypeId() == \Magento\GroupedProduct\Model\Product\Type\Grouped::TYPE_CODE) {
                    // Do nothing
                } elseif ($product->getTypeId() == \Magento\Bundle\Model\Product\Type::TYPE_CODE) {
                    $logger->info("Bundle product detected, skipping");
                } else {

                    // Warning message if needed
                    $cartWarningMsg = $helper->getMsgWarningQtyInCart(
                        $productId,
                        $product->getName(),
                        $quoteItem->getQty()
                    );

                    if ($cartWarningMsg) {
                        $quoteItem->setMessage($cartWarningMsg);
                    }

                    // Apply preorder price logic
                    // $paymentType = $data['pre_order_payment_type'];
                    // $depositPercent = $data['pre_order_deposit'];
                    // $depositPercent = 5;

                    // if ($paymentType == 'deposit') {
                        // $this->setPreorderPrice($quoteItem, $product, $productId, $depositPercent);

                        // Handle preorder-complete product flow
                        $data = $this->registry->registry('preorder_graphql_input');
                        if ($productId == $preorderCompleteProductId && $data) {
                            $result = $this->processPreorderCompleteData($quoteItem);
                            // dd($result);
                            if ($result['error']) {
                                $this->messageManager->addNotice(__($result['msg']));
                                throw new \Magento\Framework\GraphQl\Exception\GraphQlInputException(
                                    __($result['msg'])
                                );
                                // dd($result);
                            }
                        }

                        // Add preorder message block
                        // if ($helper->isPreorder($productId)) {
                            $msg = $helper->getPreOrderInfoBlock($productId, $product);
                            if ($msg) {
                                $quoteItem->setPreorderMsg($msg);
                            }
                        // }   
                    // }                    
                }
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $logger->info("catch: ".$e->getMessage());
                $this->messageManager->addError($e->getMessage());
            }
        }
        return;
    }

    /**
     * Get configurable associated product ID
     */
    protected function getFinalProductId($product)
    {
        $helper    = $this->preorderHelper;
        $productId = $product->getId();
        $data      = $this->request->getParams();

        if (isset($data['selected_configurable_option']) && $data['selected_configurable_option'] != '') {
            return (int)$data['selected_configurable_option'];
        }

        if (isset($data['super_attribute'])) {
            return $helper->getAssociatedId($data['super_attribute'], $product);
        }

        return $productId;
    }

    /**
     * Apply preorder partial price
     */
    protected function setPreorderPrice($item, $product, $productId, $depositPercent)
    {
        $helper = $this->preorderHelper;
        // $preorderPercent = $helper->getPreorderPercent($productId, $product);
        $preorderPercent = (int)$depositPercent;

        // if ($helper->isPartialPreorder($productId)) {
            $id    = (int) $item->getId();
            $item  = ($item->getParentItem() ? $item->getParentItem() : $item);
            $price = $this->getPreorderPrice($product, $productId, $preorderPercent);
            $basePrice = $this->getPreorderBasePrice($product, $productId, $preorderPercent);

            $item->setCustomPrice($price);
            $item->setOriginalCustomPrice($basePrice);
            $item->getProduct()->setIsSuperMode(true);
            
            if ($id > 0) {
                $collection = $this->itemCollection->create();
                // dd($id);
                $row = $helper->getDataByField($id, 'item_id', $collection);

                if ($row) {
                    $this->itemFactory->create()
                        ->addData([
                            'item_id'          => $id,
                            'preorder_percent' => $preorderPercent,
                        ])
                        ->setId($row->getId())
                        ->save();
                }
            }
        // }
    }

    /**
     * Full preorder-complete logic
     */
    protected function processPreorderCompleteData($quoteItem, $qty = null)
    {
        // Original logic kept fully intact
        // (unchanged for reliability)

        $itemId = (int) $quoteItem->getId();
        $helper = $this->preorderHelper;

        if (!$this->customerSession->isLoggedIn()) {
            return ['error' => true, 'msg' => 'There was some error while processing your request.'];
        }

        // $data = $this->request->getParams();
        $data = $this->registry->registry('preorder_graphql_input');
        // dd($data);
        $data['pro_id'] = $data['product_id'];

        $qty  = $qty ?: (int)$data['qty'];

        $orderId           = $data['order_id'];
        $orderItemId       = $data['item_id'];
        $preorderProductId = $data['pro_id'];
        $sellerId          = $data['seller_id'];

        $collection  = $this->productCollection->create();
        $collection->joinField('is_in_stock', 'cataloginventory_stock_item', 'is_in_stock', 'product_id=entity_id', '{{table}}.stock_id=1', 'left');
        $collection->joinField('qty', 'cataloginventory_stock_item', 'qty', 'product_id=entity_id', '{{table}}.stock_id=1', 'left');
        $collection->addFieldToFilter('entity_id', $preorderProductId);

        $stockStatus = 0;
        $preorderQty = 0;

        foreach ($collection as $value) {
            $stockStatus = $value->getIsInStock();
            $preorderQty = $value->getQty();
        }

        if ($stockStatus == 0 || $qty > $preorderQty) {
            return ['error' => true, 'msg' => 'Product is not available.'];
        }

        if ($itemId > 0) {
            return ['error' => true, 'msg' => 'Already added to cart.'];
        }

        $collection = $this->preorderCollection->create();
        $item       = $helper->getDataByField([$orderItemId, $orderId], ['item_id', 'order_id'], $collection);

        if ($item) {
            $remainingAmount = $item->getRemainingAmount();
            $unitPrice       = $remainingAmount;
            $baseUnitPrice   = $this->getBaseRemainingPrice($remainingAmount);

            $quoteItem->setCustomPrice($unitPrice);
            $quoteItem->setOriginalCustomPrice($baseUnitPrice);
            $quoteItem->setLofSellerId($sellerId);
            $quoteItem->setSellerId($sellerId);
            $quoteItem->getProduct()->setIsSuperMode(true);

            return ['error' => false];
        }

        return ['error' => true, 'msg' => 'Something went wrong.'];
    }

    public function getPreorderPrice($product, $productId, $preorderPercent)
    {
        $price = $this->getPrice($product);
        // if ($this->isPartialPreorder($productId)) {
            // $preorderPercent = $this->getPreorderPercent();
            if ($preorderPercent > 0) {
                $price = ($price * $preorderPercent) / 100;
            } else {
                $price = 0.0;
            }
        // }

        return $price;
    }

    public function getPreorderBasePrice($product, $productId, $preorderPercent)
    {
        $price = $this->getBasePrice($product);
        // if ($this->isPartialPreorder($productId)) {
            // $preorderPercent = $this->getPreorderPercent();
            if ($preorderPercent > 0) {
                $price = ($price * $preorderPercent) / 100;
            } else {
                $price = 0.0;
            }
        // }

        return $price;
    }

    public function getPrice($product)
    {
        $price = $product->getFinalPrice();

        return $price;
    }

    protected function getBasePrice($product)
    {
        // final price in display currency
        $displayFinal = $product->getFinalPrice();

        $store = $this->storeManager->getStore();

        $baseCurrency = $store->getBaseCurrency();
        $currentCurrency = $store->getCurrentCurrency();

        // rate from current → base
        $rate = $currentCurrency->getRate($baseCurrency->getCode());

        if (!$rate) {
            // fallback, if the rate is missing
            return $displayFinal;
        }

        // convert display → base
        $baseFinal = $displayFinal / $rate;

        return round($baseFinal, 2);
    }

    protected function getBaseRemainingPrice($displayFinal)
    { 
        $store = $this->storeManager->getStore();

        $baseCurrency = $store->getBaseCurrency();
        $currentCurrency = $store->getCurrentCurrency();

        // rate from current → base
        $rate = $currentCurrency->getRate($baseCurrency->getCode());

        if (!$rate) {
            // fallback, if the rate is missing
            return $displayFinal;
        }

        // convert display → base
        $baseFinal = $displayFinal / $rate;

        return round($baseFinal, 2);
    }
}
