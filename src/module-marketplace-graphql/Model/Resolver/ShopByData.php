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
class ShopByData implements ResolverInterface
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
            $attributeData = $this->customFiltersHelper->getAttributeData($attributeCode, $storeId, $filterUrl, $categoryUrlKey);
            if ($attributeData) {

                $attributeLabel = $attributeData['frontend_label'];
                $productAttributeOptions = $attributeData['options'];

                // Set page title
                $pageInfo = [
                    'title' => $attributeLabel
                ];

                // Custom page title
                if ($attributeCode == "release_year") {
                    $pageInfo = [
                        'title' => 'Year'
                    ];
                }

                if ($attributeCode == "card_game") {
                    $pageInfo = [
                        'title' => 'All Games'
                    ];
                }
                
                if ($productAttributeOptions) {

                    $categoryName = $this->mappingHelper->getCategoryNameByUrlKey($categoryUrlKey);
                    if ($categoryName) {
                        $customTableMetadata = $this->mappingHelper->getCustomTableMetadata(
                            null, 
                            $categoryName
                        );

                        if ($customTableMetadata) {

                            $singlesCollection = $customTableMetadata['singlesCollection'] ?? null;
                            $setsTableName = $customTableMetadata['setsTableName'] ?? null;
                            $mainAttrCode = $customTableMetadata['mainAttrCode'] ?? '';

                            $mainAttrVal = null;
                            foreach($filterUrl as $attrCode => $filter) {
                                $val = $filter['eq'] ?? '';
                                if ($val) {
                                    if ($attrCode == $mainAttrCode) {
                                        $mainAttrVal = $val;
                                    }
                                }
                            }

                            $attributeCodeWithoutPrefix = preg_replace('/^[^_]+_/', '', $attributeCode);

                            // CARD NAME FILTER TOP
                            if ($attributeCodeWithoutPrefix == "card_name") {
                                $collection = $singlesCollection;
                                $collection->getSelect()->joinLeft(
                                    ['sets' => $setsTableName],
                                    'sets.entity_id=main_table.set_id',
                                    []
                                )->where("sets.{$mainAttrCode}='".$mainAttrVal."'");
                                $collection->getSelect()->group('main_table.' . $attributeCodeWithoutPrefix);
                                $collection->addFieldToSelect([$attributeCodeWithoutPrefix]);
                                if ($collection->getSize()) {
                                    // dd($collection->getData());
                                    foreach($collection as $model) {
                                        $label = $model->getData($attributeCodeWithoutPrefix);
                                        if ($label) {
                                            $matchOption = array_filter($productAttributeOptions, fn($item) => $item['label'] === $label);
                                            if ($matchOption) {
                                                $options[] = [
                                                    'label' => $label,
                                                    'value' => current(array_column($matchOption, 'value')),
                                                    'products_count' => current(array_column($matchOption, 'products_count'))
                                                ];
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }

                    usort($options, fn($a, $b) => $a['label'] <=> $b['label']);

                    $result[] = [
                        'attribute_code' => $attributeCode,
                        'label' => $attributeLabel,
                        'options' => $options
                    ];
                }
            }
        }

        return [
            'filter_id' => json_encode($args),
            'page_info' => $pageInfo,
            'data' => $result
        ];
    }
}
