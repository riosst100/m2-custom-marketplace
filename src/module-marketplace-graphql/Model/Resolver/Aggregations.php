<?php

declare(strict_types=1);

namespace TCGCollective\MarketplaceGraphQl\Model\Resolver;

use Magento\CatalogGraphQl\DataProvider\Product\LayeredNavigation\LayerBuilder;
use Magento\CatalogGraphQl\DataProvider\Product\LayeredNavigation\Builder\Aggregations\Category;
use Magento\Directory\Model\PriceCurrency;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Store\Api\Data\StoreInterface;

/**
 * Layered navigation filters resolver, used for GraphQL request processing.
 */
class Aggregations extends \Magento\CatalogGraphQl\Model\Resolver\Aggregations
{
    /**
     * @var Layer\DataProvider\Filters
     */
    private $filtersDataProvider;

    /**
     * @var LayerBuilder
     */
    private $layerBuilder;

    /**
     * @var PriceCurrency
     */
    private $priceCurrency;

    /**
     * @var Category\IncludeDirectChildrenOnly
     */
    private $includeDirectChildrenOnly;
    protected $catalogHelper;

    /**
     * @param \Magento\CatalogGraphQl\Model\Resolver\Layer\DataProvider\Filters $filtersDataProvider
     * @param LayerBuilder $layerBuilder
     * @param PriceCurrency $priceCurrency
     * @param Category\IncludeDirectChildrenOnly $includeDirectChildrenOnly
     */
    public function __construct(
        \Magento\CatalogGraphQl\Model\Resolver\Layer\DataProvider\Filters $filtersDataProvider,
        LayerBuilder $layerBuilder,
        \TCGCollective\MarketPlace\Helper\Catalog $catalogHelper,
        PriceCurrency $priceCurrency = null,
        Category\IncludeDirectChildrenOnly $includeDirectChildrenOnly = null
    ) {
        parent::__construct(
            $filtersDataProvider,
            $layerBuilder,
            $priceCurrency,
            $includeDirectChildrenOnly
        );
        $this->catalogHelper = $catalogHelper;
        $this->filtersDataProvider = $filtersDataProvider;
        $this->layerBuilder = $layerBuilder;
        $this->priceCurrency = $priceCurrency ?: ObjectManager::getInstance()->get(PriceCurrency::class);
        $this->includeDirectChildrenOnly = $includeDirectChildrenOnly
            ?: ObjectManager::getInstance()->get(Category\IncludeDirectChildrenOnly::class);
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
        if (!isset($value['layer_type']) || !isset($value['search_result'])) {
            return null;
        }

        $categoryUid = $value['category_uid'] ?? null;

        $aggregations = $value['search_result']->getSearchAggregation();
        if ($aggregations) {
            $categoryFilter = $value['categories'] ?? [];
            $includeDirectChildrenOnly = $args['filter']['category']['includeDirectChildrenOnly'] ?? false;
            if ($includeDirectChildrenOnly && !empty($categoryFilter)) {
                $this->includeDirectChildrenOnly->setFilter(['category' => $categoryFilter]);
            }
            /** @var StoreInterface $store */
            $store = $context->getExtensionAttributes()->getStore();
            $storeId = (int)$store->getId();
            $results = $this->layerBuilder->build($aggregations, $storeId);
            if (isset($results['price_bucket'])) {
                foreach ($results['price_bucket']['options'] as &$option) {
                    list($from, $to) = explode('-', $option['label']);
                    $newLabel = $this->priceCurrency->convertAndRound($from)
                        . '-'
                        . $this->priceCurrency->convertAndRound($to);
                    $option['label'] = $newLabel;
                    $option['value'] = str_replace('-', '_', $newLabel);
                }
            }

            $gamesSubcategoriesAttrCode = null;
            
            $subCategoriesConfig = $this->catalogHelper->getSubCategoriesConfig(base64_decode($categoryUid));
            if ($subCategoriesConfig) {
                $gamesSubcategoriesAttrCode = $subCategoriesConfig['attribute_code'] ?? null;
            }

            $filters = $value['args']['filter'] ?? null;
            if ($filters) {
                $aggregations2 = $value['search_result2']->getSearchAggregation();
                // dd($aggregations2);
                if ($aggregations2) {
                    $results2 = $this->layerBuilder->build($aggregations2, $storeId);
                    foreach($results as $layer) {
                        $attrCode = $layer['attribute_code'];
                        if ($attrCode == "ship_from_bucket") {
                            $attrCode = "ship_from";
                        } 
                        if (!isset($filters[$attrCode]) || $attrCode == $gamesSubcategoriesAttrCode) {
                            continue;
                        }
                        $bucket = $attrCode . '_bucket';
                        if (isset($results[$bucket])) {
                            $results[$bucket]['options'] = $results2[$bucket]['options'];
                        }
                    }
                    // dd($results2);
                }
            }

            $filters = $value['args']['filter'] ?? null;
            if ($filters) {
                $aggregationsWithoutFilter = $value['search_result_without_filter']->getSearchAggregation();
                // dd($aggregationsWithoutFilter);
                if ($aggregationsWithoutFilter) {
                    $resultsWithoutFilter = $this->layerBuilder->build($aggregationsWithoutFilter, $storeId);

                    if ($gamesSubcategoriesAttrCode) {
                        foreach($results as $key => $layer) {
                            $attrCode = $layer['attribute_code'];
                            if ($attrCode == $gamesSubcategoriesAttrCode) {
                                $bucket = $attrCode . '_bucket';
                                if (isset($results[$bucket])) {
                                    $results[$bucket]['options'] = $resultsWithoutFilter[$bucket]['options'];
                                }
                                break;
                            }
                        }
                    }
                }
            }

            // Sort Side Filters
            $results = $this->catalogHelper->pwaFilteringAndSortingSideFilters($results, $categoryUid);
            return $results;
        } else {
            return [];
        }
    }
}
