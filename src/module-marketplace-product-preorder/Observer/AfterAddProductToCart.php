<?php
namespace TCGCollective\MarketplaceProductPreorder\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;
use Lof\Preorder\Model\ResourceModel\Item\CollectionFactory;
use Lof\PreOrder\Model\ResourceModel\PreOrder\CollectionFactory as PreorderCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as Products;
use Magento\Framework\Registry;
use Magento\Quote\Model\QuoteFactory;

class AfterAddProductToCart implements ObserverInterface
{
    /**
     * @var RequestInterface
     */
    protected $_request;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $_messageManager;

    /**
     * @var \Magento\Checkout\Model\CartFactory
     */
    protected $_cart;

    /**
     * @var \Lof\PreOrder\Model\ItemFactory
     */
    protected $_item;

    /**
     * @var \Lof\PreOrder\Model\CompleteFactory
     */
    protected $_complete;

    /**
     * @var \Lof\PreOrder\Helper\Data
     */
    protected $_preorderHelper;

    /**
     * @var CollectionFactory
     */
    protected $_itemCollection;

    /**
     * @var PreorderCollectionFactory
     */
    protected $_preorderCollection;

    /**
     * @var Products
     */
    protected $_productCollection;

    protected $registry;

    protected $quoteFactory;

    /**
     * @param RequestInterface $request
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Checkout\Model\CartFactory $cart
     * @param \Lof\PreOrder\Model\ItemFactory $item
     * @param \Lof\PreOrder\Model\CompleteFactory $complete
     * @param \Lof\PreOrder\Helper\Data $preorderHelper
     * @param CollectionFactory $itemCollection
     * @param Products $productCollection
     * @param PreorderCollectionFactory $preorderCollection
     */
    public function __construct(
        RequestInterface $request,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Checkout\Model\CartFactory $cart,
        \Lof\PreOrder\Model\ItemFactory $item,
        \Lof\PreOrder\Model\CompleteFactory $complete,
        \Lof\PreOrder\Helper\Data $preorderHelper,
        CollectionFactory $itemCollection,
        Products $productCollection,
        PreorderCollectionFactory $preorderCollection,
        Registry $registry,
        QuoteFactory $quoteFactory
    ) {
        $this->_request = $request;
        $this->_customerSession = $customerSession;
        $this->_messageManager = $messageManager;
        $this->_cart = $cart;
        $this->_item = $item;
        $this->_complete = $complete;
        $this->_preorderHelper = $preorderHelper;
        $this->_itemCollection = $itemCollection;
        $this->_preorderCollection = $preorderCollection;
        $this->_productCollection = $productCollection;
        $this->registry = $registry;
        $this->quoteFactory = $quoteFactory;
    }

    /**
     * Process after add product to cart
     * 
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/preorder.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info("AfterAddProductToCart preorder");
        // dd('here');
        $event     = $observer->getEvent();
        $cartId    = $event->getData('data_object')->getQuoteId();
        $quoteItem = $event->getData('quote_item') ?: $event->getItem();

        $helper = $this->_preorderHelper;
        $cartModel = $this->_cart->create();
        $itemId = 0;
        $productId = 0;
        $preorderCompleteProductId = $helper->getPreorderCompleteProductId();
        $preorderPercent = $helper->getPreorderPercent();
        $cart = $cartModel->getQuote();
        // $product = $observer->getEvent()->getData('product');
        $item = $observer->getEvent()->getItem(); // quote item
        $product = $item->getProduct();
        $sku = $product->getSku();

        /**
         * --------------------------------------------------------------
         *  PART 1 - Your logic: Set preorder custom fields on quote item
         * --------------------------------------------------------------
         */
        $map = $this->registry->registry('preorder_data');

        if ($map && isset($map[$sku])) {
            $dataPreorder = $map[$sku];

            // $quoteItem->setData('is_preorder', 1);
            // $quoteItem->setData('pre_order_payment_type', $data['pre_order_payment_type']);
            // $quoteItem->setData('pre_order_deposit', $data['pre_order_deposit']);

            $logger->info("Preorder data applied for SKU {$sku}");

            if ($product && $product instanceof \Magento\Catalog\Model\Product ) {
                $data = $this->_request->getParams();
                if ($product->getTypeId() == \Magento\GroupedProduct\Model\Product\Type\Grouped::TYPE_CODE) {
                    $typeInstance = $product->getTypeInstance();
                    $associatedProducts = $typeInstance->setStoreFilter(
                        $product->getStore(),
                        $product
                    )->getAssociatedProducts(
                        $product
                    );
                    $super_group = isset($data['super_group'])?$data['super_group']:[];
                    if ($super_group) {
                        $_childProducts = [];
                        foreach ($associatedProducts as $childProduct) {
                            $_childProductId = $childProduct->getId();
                            if (!isset($super_group[$_childProductId])) { //check exists child product by id
                                continue;
                            }
                            if (!$super_group[$_childProductId]) { //check qty greater than 0
                                continue;
                            }
                            if (!$helper->isPreorder($_childProductId)) { // check preorder
                                continue;
                            }
                            $_childProducts[$_childProductId] = $childProduct;
                        }
                        if ($_childProducts) {
                            foreach ($cart->getAllItems() as $item) {
                                $itemId = $item->getId();
                                $productId = $item->getProductId();
                                if (isset($_childProducts[$productId]) && $itemId > 0 && $productId > 0) {
                                    $product = $_childProducts[$productId];
                                    //create preorder partial payment item
                                    $this->createPreorderPartialPayItem($itemId, $productId, $preorderPercent);
                                    //create complete order or preorder product
                                    $this->createCompletePreorder($productId, $preorderCompleteProductId, $itemId);
                                    //Set Preorder Custom Price
                                    $this->setPreorderPrice($item, $_childProducts[$productId], $productId);
                                    $cart_warning_msg = $helper->getMsgWarningQtyInCart($productId, $product->getname(), $item->getQty());
                                    if ($cart_warning_msg) {
                                        $item->setMessage($cart_warning_msg);
                                    }
                                    if ($productId == $preorderCompleteProductId) {
                                        $result = $this->processPreorderCompleteData($item, $item->getQty());
                                        if ($result['error']) {
                                            $this->_messageManager->addNotice(__($result['msg']));
                                            throw new \Magento\Framework\Exception\LocalizedException($result['msg']);
                                        }
                                    }
                                    if ($helper->isPreorder($productId)) {
                                        $msg = $helper->getPreOrderInfoBlock($productId, $product);
                                        if ($msg) {
                                            $item->setPreorderMsg($msg);
                                        }
                                    }
                                }
                            }
                        }
                    }
                } elseif ($product->getTypeId() == \Magento\Bundle\Model\Product\Type::TYPE_CODE) {
                //
                } else {
                    foreach ($cart->getAllItems() as $item) {
                        $itemId = $item->getId();
                        $productId = $item->getProductId();
                    }
                    if ($itemId > 0 && $productId > 0) {
                        // dd('masuk: '.$productId);
                        $depositPercent = 0;
                        $paymentType = $dataPreorder['pre_order_payment_type'];
                        // dd($quoteItem->getIsPreorder());
                        if($paymentType == 'deposit')
                        {
                            $depositPercent = $dataPreorder['pre_order_deposit'];
                        }                    

                        if ($quoteItem->getIsPreorder() || $productId == $preorderCompleteProductId) { // check preorder
                            // dd($cartId);
                            $mageQuoteModel = $this->quoteFactory->create()->load((int)$cartId);
                            $quoteItem = $mageQuoteModel->getItemById($itemId);
                            // dd($quoteItem);
                            $quoteItem->getProduct()->setIsSuperMode(true);
                            $mageQuoteModel->setTotalsCollectedFlag(false);
                            $mageQuoteModel->collectTotals();
                            // dd($quoteItem);
                            // $mageQuoteModel->save();                            

                            $this->createPreorderPartialPayItem($itemId, $productId, $depositPercent);            
                            
                            $this->createCompletePreorder($productId, $preorderCompleteProductId, $itemId);
                        }
                    }
                }
            }
        }
    }
    /**
     * Get Product Id
     *
     * @param object $quoteItem
     * @param mixed $qty
     * @return array
     */
    public function processPreorderCompleteData($quoteItem, $qty = null)
    {
        $itemId = (int) $quoteItem->getId();
        $helper = $this->_preorderHelper;
        if (!$this->_customerSession->isLoggedIn()) {
            $msg = 'There was some error while processing your request.';
            $result = ['error'=> true, 'msg'=> $msg];
            return $result;
        }
        $data = $this->_request->getParams();
        if (!$qty) {
            $qty = $data['qty'];
        }
        $qty = (int)$qty;
        $orderId = $data['order_id'];
        $orderItemId = $data['item_id'];
        $preorderProductId = $data['pro_id'];
        $stockStatus = 0;
        $preorderQty = 0;
        $collection = $this->_productCollection->create();
        $table = 'cataloginventory_stock_item';
        $bind = 'product_id = entity_id';
        $cond = '{{table}}.stock_id = 1';
        $type = 'left';
        $alias = 'is_in_stock';
        $field = 'is_in_stock';
        $collection->joinField($alias, $table, $field, $bind, $cond, $type);
        $alias = 'qty';
        $field = 'qty';
        $collection->joinField($alias, $table, $field, $bind, $cond, $type);
        $collection->addFieldToFilter('entity_id', $preorderProductId);
        foreach ($collection as $value) {
            $stockStatus = $value->getIsInStock();
            $preorderQty = $value->getQty();
        }
        if ($stockStatus == 0 || $qty > $preorderQty) {
            $msg = 'Product is not available.';
            $result = ['error'=> true, 'msg'=> $msg];
            return $result;
        }
        if ($itemId > 0) {
            $msg = 'Already added to cart.';
            $result = ['error'=> true, 'msg'=> $msg];
            return $result;
        }
        $collection = $this->_preorderCollection->create();
        $values = [$orderItemId, $orderId];
        $fields = ['item_id', 'order_id'];
        $item = $helper->getDataByField($values, $fields, $collection);
        if ($item) {
            $remainingAmount = $item->getRemainingAmount();
            $unitPrice = $remainingAmount;
            $quoteItem->setCustomPrice($unitPrice);
            $quoteItem->setOriginalCustomPrice($unitPrice);
            $quoteItem->getProduct()->setIsSuperMode(true);
            return ['error' => false];
        }
        $msg = 'Something went wrong.';
        $result = ['error'=> true, 'msg'=> $msg];
        return $result;
    }
    /**
     * Set Preorder Product Price
     *
     * @param object $item
     * @param object $product
     * @param int $productId
     * @return int
     */
    public function setPreorderPrice($item, $product, $productId)
    {
        $helper = $this->_preorderHelper;
        if ($helper->isPartialPreorder($productId)) {
            $item = ($item->getParentItem() ? $item->getParentItem() : $item);
            $price = $helper->getPreorderPrice($product, $productId);
            $item->setCustomPrice($price);
            $item->setOriginalCustomPrice($price);
            $item->getProduct()->setIsSuperMode(true);
        }
    }
    /**
     * Create Complete PreOrder
     */
    public function createCompletePreorder($productId, $preorderCompleteProductId, $itemId = 0)
    {
        if ($productId == $preorderCompleteProductId) {
            if (!$this->_customerSession->isLoggedIn()) {
                $msg = 'There was some error while processing your request.';
                $this->_messageManager->addNotice(__($msg));
                throw new \Magento\Framework\Exception\LocalizedException(__($msg));
            }
            $customerId = (int) $this->_customerSession->getCustomerId();
            $data = $this->_request->getParams();
            $qty = 0;
            if (isset($data['qty'])) {
                $qty = (int)$data['qty'];
            }
            $qty = (int)$qty;
            $orderId = $data['order_id'];
            $orderItemId = $data['item_id'];
            $preorderProductId = $data['pro_id'];
            $completeData = [
                                'order_id' => $orderId,
                                'order_item_id' => $orderItemId,
                                'customer_id' => $customerId,
                                'product_id' => $preorderProductId,
                                'quote_item_id' => $itemId,
                                'qty' => $qty,
                            ];
            $this->_complete->create()->addData($completeData)->save();
        }
    }
    /**
     * Create Preorder partial pay item
     */
    public function createPreorderPartialPayItem($itemId, $productId, $preorderPercent = 0)
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/preorder.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info("createPreorderPartialPayItem");

        $helper = $this->_preorderHelper;
        if ($preorderPercent > 0) {
            $collection = $this->_itemCollection->create();
            $field = 'item_id';
            $item = $helper->getDataByField($itemId, $field, $collection);
            if (!$item) {
                $data = [
                    'item_id' => $itemId,
                    'preorder_percent' => $preorderPercent
                ];
                $this->_item->create()->setData($data)->save();
            }
        }
    }
}
