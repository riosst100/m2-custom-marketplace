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
class ShowMoreData implements ResolverInterface
{
    protected $cardGameSetsCard;
    protected $brandsFactory;
    protected $cardGameSets;
    protected $legoSets;
    protected $legoSingles;
    protected $sportCardsSets;
    protected $sportCardsSetsCard;
    protected $nonSportCardsSets;
    protected $nonSportCardsSetsCard;
    protected $miniatureGameSets;
    protected $miniatureGameSingles;
    protected $livingCardGameSets;
    protected $mappingHelper;
    protected $customFiltersHelper;
    
    public function __construct(
        \TCGCollective\ProductAttributesLink\Model\CardGamesSetsFactory $cardGameSets,
        \TCGCollective\ProductAttributesLink\Model\CardGamesSetsCardFactory $cardGameSetsCard,
        \TCGCollective\ProductAttributesLink\Model\LegoSetsFactory $legoSets,
        \TCGCollective\ProductAttributesLink\Model\LivingCardGameSetsFactory $livingCardGameSets,
        \TCGCollective\ProductAttributesLink\Model\LegoSinglesFactory $legoSingles,
        \TCGCollective\ProductAttributesLink\Model\SportCardsSetsFactory $sportCardsSets,
        \TCGCollective\ProductAttributesLink\Model\SportCardsSetsCardFactory $sportCardsSetsCard,
        \TCGCollective\ProductAttributesLink\Model\NonSportCardsSetsFactory $nonSportCardsSets,
        \TCGCollective\ProductAttributesLink\Model\NonSportCardsSetsCardFactory $nonSportCardsSetsCard,
        \TCGCollective\ProductAttributesLink\Model\MiniatureGameSetsFactory $miniatureGameSets,
        \TCGCollective\ProductAttributesLink\Model\MiniatureGameSinglesFactory $miniatureGameSingles,
        \TCGCollective\ProductAttributesLink\Helper\Data $mappingHelper,
        \TCGCollective\MarketplaceGraphQl\Helper\CustomFilters $customFiltersHelper
    ) {
        $this->cardGameSets = $cardGameSets;
        $this->cardGameSetsCard = $cardGameSetsCard;
        $this->livingCardGameSets = $livingCardGameSets;
        $this->legoSets = $legoSets;
        $this->legoSingles = $legoSingles;
        $this->sportCardsSets = $sportCardsSets;
        $this->sportCardsSetsCard = $sportCardsSetsCard;
        $this->nonSportCardsSets = $nonSportCardsSets;
        $this->nonSportCardsSetsCard = $nonSportCardsSetsCard;
        $this->miniatureGameSets = $miniatureGameSets;
        $this->miniatureGameSingles = $miniatureGameSingles;
        $this->mappingHelper = $mappingHelper;
        $this->customFiltersHelper = $customFiltersHelper;
    }
    
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $result = [];
        $pageInfo = [];
        $options = [];

        $store = $context->getExtensionAttributes()->getStore();
        $storeId = $store->getId();

        $attributeCode = $args['attributeCode'];
        $activeFilter = isset($args['activeFilter']) ? $args['activeFilter'] : '';
        $filterUrl = isset($args['filters']) ? $args['filters'] : '';
        $categoryUrlKey = isset($args['categoryUrlKey']) ? $args['categoryUrlKey'] : '';
        
        $search = isset($args['search']) ? $args['search'] : null;
        $params = [];
        parse_str(parse_url($search, PHP_URL_QUERY), $params);

        if ($attributeCode) {
            // Get attribute model
            // $attributeData = $this->customFiltersHelper->getAttributeData($attributeCode, $storeId, $filterUrl, $categoryUrlKey);
            // if ($attributeData) {

            //     $attributeLabel = $attributeData['frontend_label'];
            //     $productAttributeOptions = $attributeData['options'];

            //     // Set page title
            //     $pageInfo = [
            //         'title' => $attributeLabel
            //     ];

            //     // Custom page title
            //     if ($attributeCode == "release_year") {
            //         $pageInfo = [
            //             'title' => 'By Year'
            //         ];
            //     }

            //     if ($attributeCode == "card_game") {
            //         $pageInfo = [
            //             'title' => 'All Games'
            //         ];
            //     }
                
            //     usort($options, fn($a, $b) => $a['label'] <=> $b['label']);

            //     $result[] = [
            //         'attribute_code' => $attributeCode,
            //         'label' => $attributeLabel,
            //         'options' => $options
            //     ];
            // }

            // $attributeData = $this->mappingHelper->getCachedAttributeData($attributeCode);
            // dd($attributeData);
            // if ($attributeData) {
                $attributeOptionsArr = $this->mappingHelper->getCachedAttributeOptions($attributeCode);
                if ($attributeOptionsArr) {
                    foreach ($attributeOptionsArr as $optionArr) {
                        $label = $optionArr['label'] ?? null;
                        $value = $optionArr['value'] ?? null;
                        if ($value) {
                            $label = mb_convert_encoding($label, "UTF-8");
                            if (
                                $optionArr['label'] == "Game" 
                                || $optionArr['label'] == "Brands" 
                                || $optionArr['label'] == "Games" 
                                || $optionArr['label'] == "Card Game" 
                                || $optionArr['label'] == "Brand"
                            ) {
                                continue;
                            }
                            $options[] = [
                                'label' => $label,
                                'value' => $value,
                                'count' => $this->mappingHelper->getProductCount(
                                    $info, $context, $attributeCode, $value
                                )
                            ];
                        }
                    }

                    usort($options, fn($a, $b) => $a['label'] <=> $b['label']);

                    $result[] = [
                        'attribute_code' => $attributeCode,
                        // 'label' => $attributeData['frontend_label'],
                        'label' => '',
                        'options' => $options
                    ];
                }
            // }
        }

        return [
            'filter_id' => json_encode($args),
            'page_info' => $pageInfo,
            'data' => $result
        ];
    }
}
