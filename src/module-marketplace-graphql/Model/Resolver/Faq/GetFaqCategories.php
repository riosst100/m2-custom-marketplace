<?php
namespace TCGCollective\MarketplaceGraphQl\Model\Resolver\Faq;

use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class GetFaqCategories implements ResolverInterface
{
    protected $productRepository;
    protected $categoryFactory;
    protected $storeManager;

    public function __construct(
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Lofmp\Faq\Model\CategoryFactory $categoryFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->productRepository = $productRepository;
        $this->categoryFactory    = $categoryFactory;
        $this->storeManager       = $storeManager;
    }

    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (!isset($args['product_id'])) {
            throw new GraphQlInputException(__('product_id is required.'));
        }

        $product = $this->productRepository->getById($args['product_id']);
        $sellerId = $product->getSellerId();
        $storeId  = $this->storeManager->getStore()->getId();

        $collection = $this->categoryFactory->create()->getCollection()
            ->addFieldToFilter('status', 1)
            ->addFieldToFilter('main_table.seller_id', ['eq' => $sellerId])
            ->addFieldToFilter('store_id', ['eq' => $storeId])
            ->setOrder('position', 'ASC');

        $result = [];

        foreach ($collection as $cat) {
            $result[] = [
                'category_id'      => (int)$cat->getCategoryId(),
                'parent_id'        => (int)$cat->getParentId(),
                'seller_id'        => (int)$cat->getSellerId(),
                'store_id'         => (int)$cat->getStoreId(),
                'title'            => $cat->getTitle(),
                'title_size'       => $cat->getTitleSize(),
                'title_color'      => $cat->getTitleColor(),
                'title_background' => $cat->getTitleBackground(),
                'border_width'     => $cat->getBorderWidth(),
                'border_color'     => $cat->getBorderColor(),
                'border_radius'    => $cat->getBorderRadius(),
                'body_background'  => $cat->getBodyBackground(),
                'margin_bottom'    => $cat->getMarginBottom(),
                'margin_left'      => $cat->getMarginLeft(),
                'icon'             => $cat->getIcon(),
                'description'      => $cat->getDescription(),
                'position'         => (int)$cat->getPosition(),
                'status'           => (int)$cat->getStatus(),
                'creation_time'    => $cat->getCreationTime(),
                'update_time'      => $cat->getUpdateTime(),
                'meta_keywords'    => $cat->getMetaKeywords(),
                'meta_description' => $cat->getMetaDescription(),
                'page_title'       => $cat->getPageTitle()
            ];
        }

        return $result;
    }
}
