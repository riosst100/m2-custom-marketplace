<?php

declare(strict_types=1);

namespace TCGCollective\MarketplaceGraphQl\Model\Resolver\Category;

use Magento\CatalogGraphQl\DataProvider\Product\SearchCriteriaBuilder;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\CatalogGraphQl\Model\Resolver\Products\Query\ProductQueryInterface;

class DefaultTopFilter implements ResolverInterface
{
    /**
     * @var \TCGCollective\ProductAttributesLink\Helper\Data
     */
    private $mappingHelper;
    private $customFiltersHelper;

    /**
     * @param \TCGCollective\ProductAttributesLink\Helper\Data $mappingHelper
     */
    public function __construct(
        \TCGCollective\ProductAttributesLink\Helper\Data $mappingHelper,
        \TCGCollective\MarketplaceGraphQl\Helper\CustomFilters $customFiltersHelper
    ) {
        $this->mappingHelper = $mappingHelper;
        $this->customFiltersHelper = $customFiltersHelper;
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
            // die(json_encode($category->getData()));
            return $this->getCustomUrl($info, $context, $category);
        }

        return $result;
    }

    public function getCustomUrl($info, $context, $category) 
    {
        // dd($category);
        $categoryRequestPath = $category->getUrlPath();
        
        $firstSubCategory = $this->getFirstSubCategoryId($category);
        if ($firstSubCategory && $firstSubCategory->getUrlPath()) {
            $categoryRequestPath = $firstSubCategory->getUrlPath();
        }

        // dd($categoryRequestPath);
        // dd($firstSubCategory);
        if ($categoryRequestPath) {
            $attrCode = null;

            // dd($categoryRequestPath);

            $topFilters = $this->customFiltersHelper->getCategoriesMainAttrCode($category->getId(), null, $categoryRequestPath);

            // dd($topFilters);

            $attrCode = $topFilters['categories'] ?? null;

            // dd($attrCode);

            if ($attrCode) {                    
                $topFilter = $this->customFiltersHelper->getAttributeOptionsForTopFilters($info, $context, $attrCode, [], 1);
                // dd($topFilter);
                if ($topFilter) {
                    $firstOption = $topFilter['options'][0] ?? null;
                    // dd($firstOption);
                    if ($firstOption) {
                        return [
                            'attribute_code' => $attrCode,
                            'label' => $firstOption['label'],
                            'value' => $firstOption['value']
                        ];
                    }
                }
            }
        }

        return null;
    }

    public function getFirstSubCategoryId($category) 
    {
        $subcategories = $category->getChildrenCategories();

        return $subcategories->getFirstItem();
    }
}
