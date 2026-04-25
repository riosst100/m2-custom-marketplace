<?php

namespace TCGCollective\MarketplaceGraphQl\Model\Resolver\Faq;

use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Lofmp\Faq\Model\ResourceModel\Question\CollectionFactory;
use Magento\Framework\App\ResourceConnection;

class ProductFaqList implements ResolverInterface
{
    protected $collectionFactory;
    protected $resource;

    public function __construct(
        CollectionFactory $collectionFactory,
        ResourceConnection $resource
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->resource = $resource;
    }

    public function resolve(
        $field,
        $context,
        $info,
        array $value = null,
        array $args = null
    ) {
        if (!isset($args['product_id'])) {
            throw new GraphQlNoSuchEntityException(__('Product ID is required.'));
        }

        $productId = (int)$args['product_id'];

        $collection = $this->collectionFactory->create();

        $collection->getSelect()
            ->join(
                ['question_product' => $this->resource->getTableName('lofmp_faq_question_product')],
                'question_product.question_id = main_table.question_id',
                []
            )
            ->where('question_product.product_id = ?', $productId)
            ->order('main_table.position ASC');

        $items = [];

        foreach ($collection as $item) {
            $items[] = [
                'question_id'       => $item->getQuestionId(),
                'seller_id'         => $item->getSellerId(),
                'customer_name'     => $item->getCustomerName(),
                'customer_email'    => $item->getCustomerEmail(),
                'category_id'       => $item->getCategoryId(),
                'store_id'          => $item->getStoreId(),

                'title'             => $item->getTitle(),
                'title_size'        => $item->getTitleSize(),
                'title_color'       => $item->getTitleColor(),
                'title_background'  => $item->getTitleBackground(),

                'border_width'      => $item->getBorderWidth(),
                'border_color'      => $item->getBorderColor(),
                'border_radius'     => $item->getBorderRadius(),

                'body_color'        => $item->getBodyColor(),
                'body_background'   => $item->getBodyBackground(),

                'margin_bottom'     => $item->getMarginBottom(),
                'margin_left'       => $item->getMarginLeft(),

                'icon'              => $item->getIcon(),
                'answer'            => $item->getAnswer(),
                'status'            => $item->getStatus(),
                'position'          => $item->getPosition(),

                'creation_time'     => $item->getCreationTime(),
                'update_time'       => $item->getUpdateTime(),

                'meta_keywords'     => $item->getMetaKeywords(),
                'meta_description'  => $item->getMetaDescription(),
                'page_title'        => $item->getPageTitle(),
            ];
        }

        return $items;
    }
}
