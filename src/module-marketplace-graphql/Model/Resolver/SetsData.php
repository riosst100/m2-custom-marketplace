<?php
declare(strict_types=1);

namespace TCGCollective\MarketplaceGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Directory\Api\CountryInformationAcquirerInterface;
use Magento\Directory\Api\Data\CountryInformationInterface;
use TCGCollective\ProductAttributesLink\Helper\Data as MappingHelper;

class SetsData implements ResolverInterface
{
    /**
     * @var DataObjectProcessor
     */
    private $dataProcessor;

    /**
     * @var CountryInformationAcquirerInterface
     */
    private $countryInformationAcquirer;

    /**
     * @var MappingHelper
     */
    protected $_mappingHelper;

    /**
     * @var \TCGCollective\ProductAttributesLink\Model\LegoSetsFactory
     */
    protected $legoSetsFactory;

    /**
     * @var \TCGCollective\ProductAttributesLink\Model\LegoSinglesFactory
     */
    protected $legoSinglesFactory;

    protected $cardGameSets;
    protected $legoSets;
    protected $sportCardsSets;
    protected $nonSportCardsSets;
    protected $miniatureGameSets;
    protected $livingCardGameSets;
    protected $customFiltersHelper;

    /**
     * @param DataObjectProcessor $dataProcessor
     * @param CountryInformationAcquirerInterface $countryInformationAcquirer
     * @param MappingHelper $mappingHelper
     * @param \TCGCollective\ProductAttributesLink\Model\LegoSetsFactory $legoSetsFactory
     * @param \TCGCollective\ProductAttributesLink\Model\LegoSinglesFactory $legoSinglesFactory
     */
    public function __construct(
        DataObjectProcessor $dataProcessor,
        CountryInformationAcquirerInterface $countryInformationAcquirer,
        MappingHelper $mappingHelper,
        \TCGCollective\ProductAttributesLink\Model\CardGamesSetsFactory $cardGameSets,
        \TCGCollective\ProductAttributesLink\Model\LegoSetsFactory $legoSets,
        \TCGCollective\ProductAttributesLink\Model\LivingCardGameSetsFactory $livingCardGameSets,
        \TCGCollective\ProductAttributesLink\Model\SportCardsSetsFactory $sportCardsSets,
        \TCGCollective\ProductAttributesLink\Model\NonSportCardsSetsFactory $nonSportCardsSets,
        \TCGCollective\ProductAttributesLink\Model\MiniatureGameSetsFactory $miniatureGameSets,
        \TCGCollective\MarketplaceGraphQl\Helper\CustomFilters $customFiltersHelper
    ) {
        $this->dataProcessor = $dataProcessor;
        $this->countryInformationAcquirer = $countryInformationAcquirer;
        $this->_mappingHelper = $mappingHelper;
        $this->cardGameSets = $cardGameSets;
        $this->livingCardGameSets = $livingCardGameSets;
        $this->legoSets = $legoSets;
        $this->sportCardsSets = $sportCardsSets;
        $this->nonSportCardsSets = $nonSportCardsSets;
        $this->miniatureGameSets = $miniatureGameSets;
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
        $store = $context->getExtensionAttributes()->getStore();

        $websiteId = $store->getWebsiteId();
        $storeId = $store->getId();

        $categoryUrlPath = isset($args['categoryUrlKey']) ? $args['categoryUrlKey'] : null;
        $activeTab = isset($args['activeTab']) ? $args['activeTab'] : null;
        $activeFilter = isset($args['activeFilter']) ? $args['activeFilter'] : null;

        $filters = $args['filters'];
        $setFilters = $args['set_filters'];
        

        $shopby = isset($args['shopby']) ? $args['shopby'] : null;

        $result = [];
        $attribute = [];
        $setsGrouping = [];
        $availableReleaseYears = [];
        $availableFilters = [];
        $pageInfo = [
            'title' => 'Singles',
            'hide_title' => false,
            'filter_group' => false
        ];

        $sort = "ASC";

        if ($categoryUrlPath) {
            $categoryUrlPathArr = explode("/", $categoryUrlPath);
            if ($categoryUrlPathArr) {
                $categoryUrlKey = str_replace(".html", "", $categoryUrlPathArr[array_key_last($categoryUrlPathArr)]);
                if ($categoryUrlKey) {

                    $activeOption = null;

                    if (isset($filters['card_game'])) {
                        $activeOption = $filters['card_game']['eq'];
                    }

                    // $categoryName = $this->_mappingHelper->getCategoryNameByUrlKey($categoryUrlKey);
                    if ($activeOption) {
                        // Card Games
                        if (str_contains($categoryUrlPath, 'collectible-game')) {
                            
                            if ($shopby == "expansion_sets") {
                                $pageInfo['title'] = 'Series & Expansion';
                                $pageInfo['hide_title'] = true;
                                $pageInfo['filter_group'] = true;
                            }

                            if ($shopby == "secret_lair") {
                                $pageInfo['title'] = 'All Drop';
                                $pageInfo['hide_title'] = true;
                                $pageInfo['filter_group'] = true;
                            }

                            if ($shopby == "release_year") {
                                $pageInfo['title'] = 'Year';
                                $pageInfo['hide_title'] = true;
                                $pageInfo['filter_group'] = true;

                                $sort = "DESC";
                            }

                            if ($shopby == "release") {
                                $pageInfo['title'] = 'Sealed Products';
                                $pageInfo['hide_title'] = false;
                                $pageInfo['filter_group'] = false;

                                $sort = "ASC";
                            }
                            
                            if ($activeOption) {
                                $collection = $this->cardGameSets->create()
                                ->getCollection()
                                ->addFieldToFilter('main_table.card_game', ['eq' => $activeOption]);

                                if ($setFilters) {
                                    foreach($setFilters as $filter) {
                                        $collection->addFieldToFilter('main_table.' . $filter['code'], ['eq' => $filter['value']]);

                                        // dd($collection->getSelect()->__toString());
                                    }
                                } else {
                                    $collection->addFieldToFilter('main_table.language_version', ['eq' => 'English']);
                                }

                                if ($shopby != "secret_lair") {
                                    if ($shopby == "release") {
                                        $collection->getSelect()->group('main_table.card_release');
                                        $collection->getSelect()->order('main_table.card_release ASC');
                                    } else {
                                        // $collection->getSelect()->group('main_table.release_year');
                                        $collection->getSelect()->order('main_table.release_year ASC');
                                    }
                                }

                                if ($shopby == "secret_lair") {
                                    $collection->getSelect()->join(
                                        ['mcgd' => 'marketplace_card_games_drop'],
                                        'mcgd.entity_id=main_table.card_drop',
                                        [
                                            'drop_release_year' => 'mcgd.release_year',
                                            'card_drop_value' => 'mcgd.card_drop'
                                        ]
                                    );
                                    $collection->getSelect()->order('mcgd.release_year DESC');
                                }

                                // die($collection->getSelect()->__toString());

                                if ($collection->getSize()) {

                                    // fetch attribute options
                                    if ($shopby == "release") {
                                        $setsAttributeCode = 'card_release';
                                    } else {
                                        $setsAttributeCode = 'card_set';
                                    }
                                    $attributesOptions = [];
                                    $categoryId = $this->_mappingHelper->getCategoryIdByUrlKey($categoryUrlKey);
                                    $cardGameLabel = $this->_mappingHelper->getOptionLabelById('card_game', $activeOption);
                                    $attributePrefix = $this->_mappingHelper->getPrefixByCategoryId($categoryId, $cardGameLabel);
                                    if ($attributePrefix) {
                                        $setsAttributeCodeWithPrefix = $attributePrefix . $setsAttributeCode;
                                        $options = $this->_mappingHelper->getAttributeOptions(
                                            $setsAttributeCodeWithPrefix
                                        );
                                        if ($options) {
                                            foreach($options as $option) {
                                                $attributesOptions[$option->getLabel()] = $option->getValue();
                                            }
                                        }
                                    }

                                    $attribute = [
                                        'attribute_code' => $setsAttributeCodeWithPrefix,
                                        'label' => ''
                                    ];

                                    $languageVersionOptions = [];

                                    $filtersCollection = $this->cardGameSets->create()
                                    ->getCollection()
                                    ->addFieldToFilter('main_table.card_game', ['eq' => $activeOption])
                                    ->addFieldToSelect(['language_version']);

                                    $filtersCollection->getSelect()->group('main_table.language_version');
                                    if ($filtersCollection->getSize()) {
                                        $languageVersionOptions = array_column($filtersCollection->getData(), 'language_version');
                                    }

                                    $availableFilters = [
                                        [
                                            'code' => 'language_version',
                                            'options' => $languageVersionOptions
                                        ]
                                    ];

                                    // die($collection->getSelect()->__toString());
                                    
                                    foreach($collection as $model) {
                                        // $setsGrouping[$model->getReleaseType()][] = [
                                        $setName = $model->getData('card_set');
                                        
                                        $group = substr($setName, 0, 1);
                                        if (!ctype_alpha($group)) {
                                            $group = '#';
                                        }

                                        if ($shopby == "expansion_sets") {
                                            $group = $model->getData('card_series');
                                        }

                                        if ($shopby == "secret_lair") {
                                            $group = $model->getData('card_drop_value');
                                            $setName = $model->getData('card_set');
                                        }

                                        if ($shopby == "release_year") {
                                            $group = $model->getData('release_year');
                                        }

                                        if ($shopby == "release") {
                                            $setName = $model->getData('card_release');
                                        }

                                        if ($group) {
                                            $releaseYear = $model->getData('release_year');
                                            if ($shopby == "secret_lair") {
                                                $releaseYear = $model->getData('drop_release_year');
                                            }
                                            if ($releaseYear) {
                                                $availableReleaseYears[$group] = $releaseYear;
                                            }

                                            $optionId = isset($attributesOptions[$setName]) ? $attributesOptions[$setName] : 0;

                                            $productCount = $this->getSetsProductsCount(
                                                $setsAttributeCodeWithPrefix,
                                                $optionId,
                                                $storeId
                                            );

                                            if ($productCount) {
                                                $setsGrouping[$group][] = [
                                                    'products_count' => $productCount,
                                                    'set_name' => $setName,
                                                    'option_id' => $optionId,
                                                    'set_abbreviation' => $model->getCardSetAbbreviation(),
                                                    'release_number' => $model->getReleaseNumber(),
                                                    'release_date' => $model->getReleaseDate(),
                                                    'release_year' => $releaseYear
                                                ];
                                            }
                                        }
                                    }
                                }
                            }
                        }

                        // Sorting sets
                        if ($setsGrouping) {
                            foreach($setsGrouping as $group => $sets) {
                                if ($sets) {
                                    usort($sets, fn($a, $b) => $a['set_name'] <=> $b['set_name']);
                                }
                                
                                $result[] = [
                                    'group' => $group,
                                    'sets' => $sets,
                                    'release_year' => $availableReleaseYears[$group] ?? ''
                                ];
                            }

                            if ($result) {
                                if ($sort == "DESC") {
                                    usort($result, fn($b, $a) => $a['group'] <=> $b['group']);
                                } else {
                                    usort($result, fn($a, $b) => $a['group'] <=> $b['group']);
                                }
                            }
                        }
                    }
                }
            }
        }

        return [
            'available_filters' => $availableFilters,
            'filter_id' => json_encode($args),
            'page_info' => $pageInfo,
            'attribute' => $attribute,
            'data' => $result
        ];
    }

    public function getSetsProductsCount($attributeCode, $optionId, $storeId) 
    {
        if ($optionId) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $productCollectionFactory = $objectManager->create(\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory::class);

            $collection = $productCollectionFactory->create();
            $collection->addFieldToSelect('e.entity_id');
            $collection->addStoreFilter($storeId);
            $collection->addAttributeToFilter($attributeCode, ['eq' => $optionId]);
            // $collection->addAttributeToFilter('publish_status', ['eq' => 1]);

            $customTable = $collection->getTable('catalog_product_entity_int');
            $collection->getSelect()->joinLeft(
                ['cpei' => $customTable],
                'e.entity_id = cpei.entity_id AND cpei.value = 1 AND cpei.attribute_id = 1304',
                []
            );

            $collection->getSelect()->where('cpei.value=1');

            if ($optionId == 33731) {
                // dd($collection->getData());
            }

            $count = $collection->getSize();
            
            return $count;
        }

        return 0;
    }
}
