<?php
namespace TCGCollective\MarketplaceProductPreorder\Model\Processor;

use Magento\Framework\Exception\LocalizedException;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Checkout\Model\Cart;

class CompleteProcessor
{
    protected $cart;
    protected $productRepository;

    public function __construct(
        Cart $cart,
        ProductRepositoryInterface $productRepository
    ) {
        $this->cart = $cart;
        $this->productRepository = $productRepository;
    }

    /**
     * Replicates the AJAX POST:
     * data: { pro_id, options, order_id, item_id, product, qty }
     */
    public function process(
        $productId,
        $itemId,
        $qty,
        $orderId,
        $preorderCompleteProductId,
        array $options = []
    ) {
        try {
            // load product assigned for preorder completion
            $product = $this->productRepository->getById($preorderCompleteProductId);

            $params = [
                'qty' => $qty,
                'product' => $preorderCompleteProductId,
                'options' => $options,
                'order_id' => $orderId,
                'item_id' => $itemId,
                'pro_id' => $productId
            ];

            $this->cart->addProduct($product, $params);
            $this->cart->save();

        } catch (\Exception $e) {
            throw new LocalizedException(__('Unable to complete preorder: %1', $e->getMessage()));
        }
    }
}
