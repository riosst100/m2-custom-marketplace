<?php
namespace TCGCollective\MarketplaceProductPreorder\Model\Resolver;

use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Quote\Model\QuoteIdMaskFactory as CartIdMaskFactory;
use Magento\Framework\Registry;
use Psr\Log\LoggerInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\OrderItemRepositoryInterface;
use Magento\Sales\Api\Data\OrderInterface;

class CompletePreorder implements ResolverInterface
{
    protected $cartRepository;
    protected $cartManagement;
    protected $productRepository;
    protected $cartIdMaskFactory;
    protected $registry;
    protected $logger;
    protected $_preorderHelper;
    protected $orderRepository;
    protected $orderItemRepository;
    private $orderResource;
    private $orderFactory;

    public function __construct(
        CartRepositoryInterface $cartRepository,
        CartManagementInterface $cartManagement,
        ProductRepositoryInterface $productRepository,
        CartIdMaskFactory $cartIdMaskFactory,
        Registry $registry,
        LoggerInterface $logger,
        \Lof\PreOrder\Helper\Data $preorderHelper,
        OrderRepositoryInterface $orderRepository,
        OrderItemRepositoryInterface $orderItemRepository,
        \Magento\Sales\Model\Spi\OrderResourceInterface $orderResource,
        \Magento\Sales\Api\Data\OrderInterfaceFactory $orderFactory
    ) {
        $this->cartRepository      = $cartRepository;
        $this->cartManagement      = $cartManagement;
        $this->productRepository   = $productRepository;
        $this->cartIdMaskFactory   = $cartIdMaskFactory;
        $this->registry            = $registry;
        $this->logger              = $logger;
        $this->_preorderHelper     = $preorderHelper;
        $this->orderRepository     = $orderRepository;
        $this->orderItemRepository = $orderItemRepository;
        $this->orderResource       = $orderResource;
        $this->orderFactory        = $orderFactory;
    }

    public function resolve(
        $field,
        $context,
        $info,
        array $value = null,
        array $args = null
    ) {
        if (!$context->getUserId()) {
            throw new GraphQlInputException(__('Customer not logged in.'));
        }

        $input = $args['input'];
        $input['order_id'] = $this->getOrderIdByIncrementId($input['order_reference']);
        if(!$input['order_id']) {
            throw new GraphQlInputException(__('Invalid order reference.'));
        }
        $input['item_id'] = base64_decode($input['item_id']);
        $input['product_id'] = $this->getProductIdByOrderItemId($input['item_id']);
        $input['lof_seller_id'] = $this->getSellerIdByOrderItemId($input['item_id']);
        $input['seller_id'] = $this->getSellerIdByOrderItemId($input['item_id']);
        if(!$input['product_id']) {
            throw new GraphQlInputException(__('Invalid order item reference.'));
        }

        $this->registry->register('preorder_graphql_input', $input);

        $cartIdMasked  = $input['cartId'];
        $helper        = $this->_preorderHelper;
        $preorderCompleteProductId = $helper->getPreorderCompleteProductId();
        $qty           = $input['qty'];
        $productName   = $input['product_name'];
        $orderReference = '#'.$input['order_reference'];

        try {
            /** -------------------------------------------------
             *  UNMASK CART ID
             * ------------------------------------------------- */
            $cartMask = $this->cartIdMaskFactory->create()->load($cartIdMasked, 'masked_id');
            $cartId   = $cartMask->getQuoteId();

            if (!$cartId) {
                throw new GraphQlInputException(__('Invalid cart ID.'));
            }

            /** -------------------------------------------------
             *  LOAD CUSTOMER QUOTE
             * ------------------------------------------------- */
            $quote = $this->cartRepository->get($cartId);

            /** -------------------------------------------------
             *  LOAD PREORDER PRODUCT
             * ------------------------------------------------- */
            $product = $this->productRepository->getById($preorderCompleteProductId);
            $preorderSku = $product->getSku(); // expected: preorder_complete

            /** -------------------------------------------------
             *  CHECK IF CART ALREADY HAS THIS PREORDER SKU
             * ------------------------------------------------- */
            foreach ($quote->getAllVisibleItems() as $item) {
                if ($item->getSku() === $preorderSku) {
                    throw new GraphQlInputException(__('Already added to cart.'));
                }
            }

            /** -------------------------------------------------
             *  FIND CUSTOMIZABLE OPTIONS BY TITLE
             * ------------------------------------------------- */
            $customOptionValues = [];
            foreach ($product->getOptions() as $option) {
                $title = trim($option->getTitle());

                if ($title === 'Product Name') {
                    $customOptionValues[$option->getOptionId()] = $productName;
                }
                if ($title === 'Order Refernce') {
                    $customOptionValues[$option->getOptionId()] = $orderReference;
                }
            }

            if (empty($customOptionValues)) {
                throw new GraphQlInputException(__('Required customizable options not found.'));
            }

            /** -------------------------------------------------
             *  BUILD REQUEST OBJECT
             * ------------------------------------------------- */
            $request = new \Magento\Framework\DataObject([
                'product' => $preorderCompleteProductId,
                'qty'     => $qty,
                'options' => $customOptionValues
            ]);

            /** -------------------------------------------------
             *  ADD PRODUCT TO CART
             * ------------------------------------------------- */
            $quote->addProduct($product, $request);
            $this->cartRepository->save($quote);

            // === Collect totals after adding items ===

            // 1. Convert masked cart id → real quote id
            $maskedIdModel = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Quote\Model\QuoteIdMaskFactory::class)
                ->create()
                ->load($cartIdMasked, 'masked_id');

            $quoteId = (int)$maskedIdModel->getQuoteId();

            // 2. Load the quote model
            $cartRepository = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Quote\Api\CartRepositoryInterface::class);
            $quote = $cartRepository->get($quoteId);

            // 3. Recalculate totals
            $quote->collectTotals();
            $cartRepository->save($quote);

            return [
                'success' => true,
                'message' => __('Preorder completed & added to cart.'),
                'cart_id' => $cartIdMasked
            ];

        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            // throw new GraphQlInputException(__($e->getMessage()));
            return [
                'success' => false,
                'message' => __($e->getMessage()),
                'cart_id' => $cartIdMasked
            ];
        }
    }

    public function getOrderIdByIncrementId($incrementId)
    {
        try {
            $order = $this->orderFactory->create();
            $this->orderResource->load($order, $incrementId, OrderInterface::INCREMENT_ID);                        
            return $order->getId();
        } catch (\Exception $e) {
            // dd($e->getMessage());
            return null;
        }
    }

    /**
     * Get Product ID from Order Item ID
     *
     * @param int $orderItemId
     * @return int|null
     */
    public function getProductIdByOrderItemId($orderItemId)
    {
        try {
            $orderItem = $this->orderItemRepository->get($orderItemId);
            return $orderItem->getProductId();
        } catch (\Exception $e) {
            return null;
        }
    }

    public function getSellerIdByOrderItemId($orderItemId)
    {
        try {
            $orderItem = $this->orderItemRepository->get($orderItemId);
            return $orderItem->getLofSellerId();
        } catch (\Exception $e) {
            return null;
        }
    }

}
