<?php

declare(strict_types=1);

namespace TCGCollective\MarketplaceGraphQl\Model\Resolver\Category;

use Magento\CatalogGraphQl\DataProvider\Product\SearchCriteriaBuilder;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\CatalogGraphQl\Model\Resolver\Products\Query\ProductQueryInterface;

class CustomSubmenu implements ResolverInterface
{
    /**
     * @var \TCGCollective\ProductAttributesLink\Helper\Data
     */
    private $mappingHelper;
    private $catalogHelper;

    /**
     * @var \TCGCollective\ProductAttributesLink\Model\CardGamesSetsFactory
     */
    private $cardGamesSetsFactory;
    
    public function __construct(
        \TCGCollective\ProductAttributesLink\Helper\Data $mappingHelper,
        \TCGCollective\MarketPlace\Helper\Catalog $catalogHelper,
        \TCGCollective\ProductAttributesLink\Model\CardGamesSetsFactory $cardGamesSetsFactory
    ) {
        $this->mappingHelper = $mappingHelper;
        $this->catalogHelper = $catalogHelper;
        $this->cardGamesSetsFactory = $cardGamesSetsFactory;
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
            $attributeCode = null;
            $moreLabel = __('Show More');
            $showMoreLink = false;

            $subCategoriesConfig = $this->catalogHelper->getSubCategoriesConfig($category->getId());
            if ($subCategoriesConfig) {
                $attributeCode = $subCategoriesConfig['attribute_code'] ?? null;
                $moreLabel = $subCategoriesConfig['show_more_label'] ?? null;
            }

            if ($attributeCode) {
                $attributeOptionsArr =  $this->mappingHelper->getCachedAttributeOptions($attributeCode);
                if ($attributeOptionsArr) {
                    if ($attributeOptionsArr) {
                        $limit = 10;
                        $x = 1;
                        if (count($attributeOptionsArr) > $limit+1) {
                            $showMoreLink = true;
                        }
                        foreach ($attributeOptionsArr as $optionArr) {
                            
                            if (
                                $optionArr['label'] == "Game" 
                                || $optionArr['label'] == "Brands" 
                                || $optionArr['label'] == "Games" 
                                || $optionArr['label'] == "Card Game" 
                                || $optionArr['label'] == "Brand"
                            ) {
                                continue;
                            }

                            $label = $optionArr['label'] ?? null;
                            $value = $optionArr['value'] ?? null;
                            if ($label && $label != ' ') {
                                $x++;
                                if (!$showMoreLink || $showMoreLink && $x <= $limit) {
                                    $label = mb_convert_encoding($label, "UTF-8");
                                    $urlPath = $category->getUrlPath() . '.html?' . http_build_query([
                                        $attributeCode => [
                                            'filter' => $label.','.$value
                                        ]
                                    ]);
                                    
                                    $result[] = [
                                        'name' => $label,
                                        'count' => $this->mappingHelper->getProductCount($info, $context, $attributeCode, $value),
                                        'path' => $urlPath
                                    ];
                                }
                            }
                        }

                        if ($showMoreLink) {
                            $result[] = [
                                'name' => $moreLabel,
                                'path' => $category->getUrlPath().'.html?show_more=' . $attributeCode
                            ];
                        }
                    }
                }
            }
        }

        return $result;
    }
}
