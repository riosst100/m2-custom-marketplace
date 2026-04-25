<?php
namespace TCGCollective\MarketplaceGraphQl\Model\Resolver\Faq;

use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Lofmp\Faq\Model\QuestionFactory;
use Lof\MarketPlace\Model\SellerProductFactory;
use Lof\MarketPlace\Model\SellerFactory;
use Magento\Store\Model\StoreManagerInterface;

class AddProductFaq implements ResolverInterface
{
    protected $questionFactory;
    protected $sellerProductFactory;
    protected $sellerFactory;
    protected $storeManager;

    public function __construct(
        QuestionFactory $questionFactory,
        SellerProductFactory $sellerProductFactory,
        SellerFactory $sellerFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->questionFactory = $questionFactory;
        $this->sellerProductFactory = $sellerProductFactory;
        $this->sellerFactory = $sellerFactory;
        $this->storeManager = $storeManager;
    }

    public function resolve(
        $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (!isset($args['input'])) {
            throw new GraphQlInputException(__('Input is required.'));
        }

        $input = $args['input'];

        // Required fields
        $productId      = $input['product_id'] ?? 0;
        $sellerId       = $input['seller_id'] ?? 0;
        $customerName   = $input['customer_name'] ?? "";
        $customerEmail  = $input['customer_email'] ?? "";
        $categoryId     = $input['category_id'] ?? "";
        $title          = isset($input['title']) ? strip_tags($input['title']) : "";

        // Load seller from product if seller_id not provided
        if ($productId && !$sellerId) {
            $sellerProduct = $this->sellerProductFactory->create()->load($productId, "product_id");
            $sellerId = $sellerProduct ? (int)$sellerProduct->getSellerId() : 0;
        }

        // Validate required fields
        if ((!$productId && !$sellerId) || !$customerEmail || !$title) {
            return [
                'success' => false,
                'message' => __('Error: missing required data!')
            ];
        }

        // Validate seller info
        if (!$sellerId) {
            return [
                'success' => false,
                'message' => __('Error: missing Seller Info!')
            ];
        }

        $seller = $this->sellerFactory->create()->load($sellerId);
        if (!$seller || !$seller->getId() || $seller->getStatus() != 1) {
            return [
                'success' => false,
                'message' => __('Error: wrong Seller Information!')
            ];
        }

        // Prepare data
        $data = [
            'product_id'     => $productId,
            'seller_id'      => $sellerId,
            'store_id'       => $this->storeManager->getStore()->getId(),
            'customer_name'  => $customerName,
            'customer_email' => $customerEmail,
            'category_id'    => $categoryId,
            'title'          => $title,
        ];

        // Save question
        try {
            $question = $this->questionFactory->create();
            $question->setData($data);
            $question->save();

            return [
                'success' => true,
                'message' => __('OK'),
                'question_id' => $question->getId()
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => __('Error: %1', $e->getMessage()),
                'question_id' => null
            ];
        }
    }
}
