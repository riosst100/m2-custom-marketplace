<?php

declare(strict_types=1);

namespace TCGCollective\MarketplaceGraphQl\Model\Resolver\Category;

use Magento\CatalogGraphQl\DataProvider\Product\SearchCriteriaBuilder;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\CatalogGraphQl\Model\Resolver\Products\Query\ProductQueryInterface;

class CustomBlocks implements ResolverInterface
{
    /**
     * @var \TCGCollective\ProductAttributesLink\Helper\Data
     */
    private $mappingHelper;

    /**
     * @param \TCGCollective\ProductAttributesLink\Helper\Data $mappingHelper
     */
    public function __construct(
        \TCGCollective\ProductAttributesLink\Helper\Data $mappingHelper
    ) {
        $this->mappingHelper = $mappingHelper;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (!isset($value['model'])) {
            throw new LocalizedException(__('"model" value should be specified'));
        }

        $result = [];

        /* @var Category $category */
        $category = $value['model'];
        if ($category) {
            // $shopBy = $category->getShopBy();
            // if ($shopBy) {
                // $shopBy = explode(",", $shopBy);
                // foreach($shopBy as $attributeCode) {
                    $attributeCode = 'card_set_name';
                    $attribute =  $this->mappingHelper->getAttribute($attributeCode);
                    if ($attribute) {
                        $result[] = [
                            'label' => __('Latest Set'),
                            'code' => $attributeCode,
                            'items' => $this->getShopByItem($attribute)
                        ];
                    }
                // }
            // }
        }

        return $result;
    }

    public function getShopByItem($attribute) 
    {
        $items = [];

        $options = $attribute->getOptions();
        if ($options) {
            foreach($options as $option) {
                $items[] = [
                    'name' => $option->getLabel(),
                    'option_id' => $option->getValue(),
                    'url_path' => '/'
                ];
            }
        }

        return $items;
    }
}
