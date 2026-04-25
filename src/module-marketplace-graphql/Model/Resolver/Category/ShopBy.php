<?php

declare(strict_types=1);

namespace TCGCollective\MarketplaceGraphQl\Model\Resolver\Category;

use Magento\CatalogGraphQl\DataProvider\Product\SearchCriteriaBuilder;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\CatalogGraphQl\Model\Resolver\Products\Query\ProductQueryInterface;

class ShopBy implements ResolverInterface
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
        return $result; // Return empty until brands and franchise page ready

        /* @var Category $category */
        $category = $value['model'];
        if ($category) {
            $shopByAttributeDataArr = [];
            $limit = 5;

            $urlPath = $category->getData('url_path');
            if ($urlPath) {
                // ACTION TOYS
                if ($urlPath == "action-figures") {
                    $shopByAttributeDataArr = [
                        [
                            'label' => __('Brands'),
                            'code' => 'toys_brand',
                            'show_more_label' => __('More Brands'),
                            'filter_options' => true
                        ]
                    ];
                }

                // ANIME
                if ($urlPath == "anime") {
                    $shopByAttributeDataArr = [
                        [
                            'label' => __('Brands'),
                            'code' => 'toys_brand',
                            'show_more_label' => __('More Brands'),
                            'filter_options' => true
                        ],
                        [
                            'label' => __('Franchise'),
                            'code' => 'toys_franchise',
                            'show_more_label' => __('More Franchise'),
                            'filter_options' => true
                        ]
                    ];
                }

                // FASHION DOLLS
                if ($urlPath == "fashion-dolls") {
                    $shopByAttributeDataArr = [
                        [
                            'label' => __('Brands'),
                            'code' => 'dolls_brand',
                            'show_more_label' => __('More Brands'),
                            'filter_options' => true
                        ]
                    ];
                }

                // CARDS
                if ($urlPath == "cards") {
                    $shopByAttributeDataArr = [
                        [
                            'label' => __('Brands'),
                            'code' => 'sc_brand',
                            'show_more_label' => __('More Brands'),
                            'filter_options' => true
                        ]
                    ];
                }

                // LEGO
                if ($urlPath == "bricks") {
                    $shopByAttributeDataArr = [
                        [
                            'label' => __('Brands'),
                            'code' => 'bricks_brand',
                            'show_more_label' => __('More Brands'),
                            'filter_options' => true
                        ]
                    ];
                }

                // Process shop by data
                if ($shopByAttributeDataArr) {
                    $categoryUrl = $urlPath.'.html';
                    
                    foreach($shopByAttributeDataArr as $attributeData) 
                    {
                        $attributeCode = $attributeData['code'];
                        $label = $attributeData['label'];
                        $filterOptions = $attributeData['filter_options'];
                        $allOptionsLabel = $attributeData['show_more_label'] ?? null;

                        $items = [];

                        $attribute = $this->mappingHelper->getAttribute($attributeCode);
                        if ($attribute) {
                            $options = $attribute->getOptions();
                            if ($options) {
                                $allowedOptions = $this->mappingHelper->getFilteredAttributeOptionsByCategory($category->getData('url_path'), $attributeCode);

                                $x = 0;
                                foreach($options as $option) {
                                    if ($option->getValue()) {
                                        if ($filterOptions && is_array($allowedOptions) && !in_array($option->getValue(), $allowedOptions)) {
                                            continue;
                                        }

                                        $x++;

                                        if ($x <= $limit) {
                                            $urlPath = $categoryUrl . '?' . http_build_query([
                                                $attributeCode => [
                                                    'filter' => $option->getLabel().','.$option->getValue()
                                                ]
                                            ]);

                                            $items[] = [
                                                'name' => $option->getLabel(),
                                                'option_id' => $option->getValue(),
                                                'url_path' => $urlPath
                                            ];
                                        }
                                    }
                                }

                                if ($x > $limit) {
                                    $items[] = [
                                        'name' => $allOptionsLabel ?? __('All %1', $label),
                                        'option_id' => '',
                                        'url_path' => $categoryUrl.'?shopby='.$attributeCode
                                    ];
                                }
                            }
                        }

                        $result[] = [
                            'name' => $label,
                            'code' => $attributeCode ?? '',
                            'items' => $items
                        ];
                    }
                }
            }
        }

        return $result;
    }
}
