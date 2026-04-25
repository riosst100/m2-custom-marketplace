<?php

namespace TCGCollective\ProductAttributesLink\Helper;

use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\CacheInterface;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const CACHE_TAG = 'custom_table_cache';

    protected $nextMappingPriority = null;

    protected $legoSinglesFactory;
    protected $sportCardsSinglesFactory;
    protected $sportCardsSetsFactory;
    protected $actionToysSetsFactory;
    protected $actionToysSinglesFactory;
    protected $_redirect;
    protected $redirect;
    protected $directory;
    protected $attributeSetRepository;
    protected $customAttributesFactory;
    protected $attribute;
    protected $attributeGroup;
    protected $cache;
    // protected $sportCardsSinglesFactory;
    // protected $sportCardsSinglesFactory;

    /**
     * @var \Magento\Catalog\Api\ProductAttributeRepositoryInterface
     */
    protected $attributeRepository;

    /**
     * @var array
     */
    protected $attributeValues;

    /**
     * @var \Magento\Eav\Model\Entity\Attribute\Source\TableFactory
     */
    protected $tableFactory;

    /**
     * @var \Magento\Eav\Api\AttributeOptionManagementInterface
     */
    protected $attributeOptionManagement;

    /**
     * @var \Magento\Eav\Api\Data\AttributeOptionLabelInterfaceFactory
     */
    protected $optionLabelFactory;

    /**
     * @var \Magento\Eav\Api\Data\AttributeOptionInterfaceFactory
     */
    protected $optionFactory;
    
    /**
     * @var \TCGCollective\ProductAttributesLink\Model\ResourceModel\ProductAttributesLink\CollectionFactory
     */
    protected $prdAttrLinkCollFactory;

    /**
     * @var \TCGCollective\ProductAttributesLink\Model\CardGamesMappingFactory
     */
    protected $cardGamesMappingFactory;

    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $eavConfig;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute
     */
    protected $attributeFactory;

    /**
     * @var \Magento\Catalog\Model\Product\AttributeSet\Options
     */
    protected $attributeSetOptions;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    protected $categoryCollectionFactory;

    /**
     * @var \TCGCollective\ProductAttributesLink\Model\MappingGroupFactory
     */
    protected $mappingGroupFactory;
    protected $cardGameSets;
    protected $legoSets;
    protected $legoSingles;
    protected $sportCardsSets;
    protected $sportCardsSetsCard;
    protected $nonSportCardsSets;
    protected $nonSportCardsSetsCard;
    protected $nonSportCardsSealedProducts;
    protected $miniatureGameSets;
    protected $miniatureGameSingles;
    protected $livingCardGameSets;
    protected $_attributeSetCollection;
    protected $actionToySetsFactory;
    protected $actionToySinglesFactory;
    protected $cardGameSealedProducts;
    protected $cardGameDropFactory;
    protected $dollsSetsFactory;
    protected $dollsSinglesFactory;

    protected $diecastSetsFactory;
    protected $diecastSinglesFactory;

    protected $modelkitsSetsFactory;
    protected $modelkitsSinglesFactory;

    protected $railwaySetsFactory;
    protected $railwaySinglesFactory;

    protected $diecastModelKitsSetsFactory;
    protected $diecastModelKitsSinglesFactory;
    protected $cardGameReleaseFactory;
    protected $sportCardsSealedProducts;
    protected $request;
    protected $registry;
    protected $legoSealedProducts;
    protected $miniatureGameSealedProducts;
    protected $actionToySealedProducts;
    protected $diecastModelKitsSealedProductsFactory;
    protected $railwaySealedProductsFactory;
    protected $dollsSealedProductsFactory;
    protected $livingCardGameSealedProducts;
    protected $timezone;
    protected $cardGameSetsCard;
    protected $serializer;
    protected $customAttributesCache;
    protected $resource;
    protected $animeSetsFactory;
    protected $animeSinglesFactory;

    protected $gamesWorkshopSetsFactory;
    protected $gamesWorkshopSinglesFactory;

    protected $searchQuery;
    
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \TCGCollective\ProductAttributesLink\Model\ResourceModel\ProductAttributesLink\CollectionFactory $prdAttrLinkCollFactory,
        \TCGCollective\ProductAttributesLink\Model\CardGamesMappingFactory $cardGamesMappingFactory,
        \Magento\Catalog\Api\ProductAttributeRepositoryInterface $attributeRepository,
        \Magento\Eav\Model\Entity\Attribute\Source\TableFactory $tableFactory,
        \Magento\Eav\Api\AttributeOptionManagementInterface $attributeOptionManagement,
        \Magento\Eav\Api\Data\AttributeOptionLabelInterfaceFactory $optionLabelFactory,
        \Magento\Eav\Api\Data\AttributeOptionInterfaceFactory $optionFactory,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attributeFactory,
        \Magento\Catalog\Model\Product\AttributeSet\Options $attributeSetOptions,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        \TCGCollective\ProductAttributesLink\Model\MappingGroupFactory $mappingGroupFactory,
        \TCGCollective\ProductAttributesLink\Model\CardGamesSetsFactory $cardGameSets,
        \TCGCollective\ProductAttributesLink\Model\CardGamesSetsCardFactory $cardGameSetsCard,
        \TCGCollective\ProductAttributesLink\Model\CardGamesSealedProductsFactory $cardGameSealedProducts,
        \TCGCollective\ProductAttributesLink\Model\CardGamesDropFactory $cardGameDropFactory,
        \TCGCollective\ProductAttributesLink\Model\CardGamesReleaseFactory $cardGameReleaseFactory,
        \TCGCollective\ProductAttributesLink\Model\LegoSetsFactory $legoSets,
        \TCGCollective\ProductAttributesLink\Model\LegoSealedProductsFactory $legoSealedProducts,
        \TCGCollective\ProductAttributesLink\Model\LivingCardGameSetsFactory $livingCardGameSets,
        \TCGCollective\ProductAttributesLink\Model\LivingCardGameSealedProductsFactory $livingCardGameSealedProducts,
        \TCGCollective\ProductAttributesLink\Model\LegoSinglesFactory $legoSingles,
        \TCGCollective\ProductAttributesLink\Model\SportCardsSetsFactory $sportCardsSets,
        \TCGCollective\ProductAttributesLink\Model\SportCardsSetsCardFactory $sportCardsSetsCard,
        \TCGCollective\ProductAttributesLink\Model\SportCardsSealedProductsFactory $sportCardsSealedProducts,
        \TCGCollective\ProductAttributesLink\Model\NonSportCardsSetsFactory $nonSportCardsSets,
        \TCGCollective\ProductAttributesLink\Model\NonSportCardsSetsCardFactory $nonSportCardsSetsCard,
        \TCGCollective\ProductAttributesLink\Model\NonSportCardsSealedProductsFactory $nonSportCardsSealedProducts,
        \TCGCollective\ProductAttributesLink\Model\MiniatureGameSetsFactory $miniatureGameSets,
        \TCGCollective\ProductAttributesLink\Model\MiniatureGameSinglesFactory $miniatureGameSingles,
        \TCGCollective\ProductAttributesLink\Model\MiniatureGameSealedProductsFactory $miniatureGameSealedProducts,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $attributeSetCollection,
        \TCGCollective\ProductAttributesLink\Model\ActionToySetsFactory $actionToySetsFactory,
        \TCGCollective\ProductAttributesLink\Model\ActionToySinglesFactory $actionToySinglesFactory,
        \TCGCollective\ProductAttributesLink\Model\ActionToySealedProductsFactory $actionToySealedProducts,
        \TCGCollective\ProductAttributesLink\Model\DollsSetsFactory $dollsSetsFactory,
        \TCGCollective\ProductAttributesLink\Model\DollsSinglesFactory $dollsSinglesFactory,
        \TCGCollective\ProductAttributesLink\Model\DollsSealedProductsFactory $dollsSealedProductsFactory,
        \TCGCollective\ProductAttributesLink\Model\RailwaySetsFactory $railwaySetsFactory,
        \TCGCollective\ProductAttributesLink\Model\RailwaySinglesFactory $railwaySinglesFactory,
        \TCGCollective\ProductAttributesLink\Model\RailwaySealedProductsFactory $railwaySealedProductsFactory,
        \TCGCollective\ProductAttributesLink\Model\DiecastModelKitsSetsFactory $diecastModelKitsSetsFactory,
        \TCGCollective\ProductAttributesLink\Model\DiecastModelKitsSinglesFactory $diecastModelKitsSinglesFactory,
        \TCGCollective\ProductAttributesLink\Model\DiecastModelKitsSealedProductsFactory $diecastModelKitsSealedProductsFactory,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        \TCGCollective\MarketPlace\Model\CustomAttributesCache $customAttributesCache,
        \Magento\Framework\App\ResourceConnection $resource,
        CacheInterface $cache,
        \TCGCollective\ProductAttributesLink\Model\DiecastSetsFactory $diecastSetsFactory,
        \TCGCollective\ProductAttributesLink\Model\DiecastSinglesFactory $diecastSinglesFactory,
        \TCGCollective\ProductAttributesLink\Model\ModelKitsSetsFactory $modelkitsSetsFactory,
        \TCGCollective\ProductAttributesLink\Model\ModelKitsSinglesFactory $modelkitsSinglesFactory,
        \TCGCollective\ProductAttributesLink\Model\AnimeSetsFactory $animeSetsFactory,
        \TCGCollective\ProductAttributesLink\Model\AnimeSinglesFactory $animeSinglesFactory,
        \TCGCollective\ProductAttributesLink\Model\GamesWorkshopSetsFactory $gamesWorkshopSetsFactory,
        \TCGCollective\ProductAttributesLink\Model\GamesWorkshopSinglesFactory $gamesWorkshopSinglesFactory,
        \Magento\CatalogGraphQl\Model\Resolver\Products\Query\ProductQueryInterface $searchQuery
    ) {
        parent::__construct(
            $context
        );

        $this->gamesWorkshopSetsFactory = $gamesWorkshopSetsFactory;
        $this->gamesWorkshopSinglesFactory = $gamesWorkshopSinglesFactory;

        $this->animeSetsFactory = $animeSetsFactory;
        $this->animeSinglesFactory = $animeSinglesFactory;
        
        $this->diecastSetsFactory = $diecastSetsFactory;
        $this->diecastSinglesFactory = $diecastSinglesFactory;

        $this->modelkitsSetsFactory = $modelkitsSetsFactory;
        $this->modelkitsSinglesFactory = $modelkitsSinglesFactory;
        
        $this->attributeRepository = $attributeRepository;
        $this->tableFactory = $tableFactory;
        $this->attributeOptionManagement = $attributeOptionManagement;
        $this->optionLabelFactory = $optionLabelFactory;
        $this->optionFactory = $optionFactory;
        $this->prdAttrLinkCollFactory = $prdAttrLinkCollFactory;
        $this->cardGamesMappingFactory = $cardGamesMappingFactory;
        $this->eavConfig = $eavConfig;
        $this->attributeFactory = $attributeFactory;
        $this->attributeSetOptions = $attributeSetOptions;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->mappingGroupFactory = $mappingGroupFactory;
        $this->cardGameSets = $cardGameSets;
        $this->cardGameSetsCard = $cardGameSetsCard;
        $this->cardGameSealedProducts = $cardGameSealedProducts;
        $this->cardGameDropFactory = $cardGameDropFactory;
        $this->cardGameReleaseFactory = $cardGameReleaseFactory;
        $this->livingCardGameSets = $livingCardGameSets;
        $this->legoSets = $legoSets;
        $this->legoSingles = $legoSingles;
        $this->sportCardsSets = $sportCardsSets;
        $this->sportCardsSetsCard = $sportCardsSetsCard;
        $this->sportCardsSealedProducts = $sportCardsSealedProducts;
        $this->nonSportCardsSets = $nonSportCardsSets;
        $this->nonSportCardsSetsCard = $nonSportCardsSetsCard;
        $this->nonSportCardsSealedProducts = $nonSportCardsSealedProducts;
        $this->miniatureGameSets = $miniatureGameSets;
        $this->miniatureGameSingles = $miniatureGameSingles;
        $this->_attributeSetCollection = $attributeSetCollection;
        $this->actionToySetsFactory = $actionToySetsFactory;
        $this->actionToySinglesFactory = $actionToySinglesFactory;
        $this->dollsSetsFactory = $dollsSetsFactory;
        $this->dollsSinglesFactory = $dollsSinglesFactory;
        $this->railwaySetsFactory = $railwaySetsFactory;
        $this->railwaySinglesFactory = $railwaySinglesFactory;
        $this->diecastModelKitsSetsFactory = $diecastModelKitsSetsFactory;
        $this->diecastModelKitsSinglesFactory = $diecastModelKitsSinglesFactory;
        $this->request = $request;   
        $this->registry = $registry;
        $this->legoSealedProducts = $legoSealedProducts;
        $this->miniatureGameSealedProducts = $miniatureGameSealedProducts;
        $this->actionToySealedProducts = $actionToySealedProducts;
        $this->diecastModelKitsSealedProductsFactory = $diecastModelKitsSealedProductsFactory;
        $this->railwaySealedProductsFactory = $railwaySealedProductsFactory;
        $this->dollsSealedProductsFactory = $dollsSealedProductsFactory;
        $this->livingCardGameSealedProducts = $livingCardGameSealedProducts;
        $this->timezone = $timezone;
        $this->customAttributesCache = $customAttributesCache;
        $this->resource = $resource;
        $this->cache = $cache;
        $this->searchQuery = $searchQuery;
    }

    public function getProductCountByCategory($info, $context, $categoryId) 
    {
        $args = [
            "filter" => [
                "category_uid" => [
                    "eq" => base64_encode($categoryId)
                ]
            ],
            "currentPage" => 1,
            "pageSize" => 20,
            "category_uid" => base64_encode($categoryId)
        ];
        
        $searchResult = $this->searchQuery->getResult($args, $info, $context);
        return $searchResult->getTotalCount();
    }

    public function getProductCount($info, $context, $attrCode, $value) 
    {
        $args = [
            "filter" => [
                $attrCode => [
                    "eq" => $value
                ]
            ],
            "currentPage" => 1,
            "pageSize" => 20
        ];
        
        $searchResult = $this->searchQuery->getResult($args, $info, $context);
        return $searchResult->getTotalCount();
    }

    public function validateImportProductOption($attrCode, $value, $rowData) {
        if (!isset($rowData['categories'])) {
            return false;
        }

        $categoryName = basename($rowData['categories']);
        $customTableMetadata = $this->getCustomTableMetadata(null, $categoryName);
        if ($customTableMetadata) {

            $column = preg_replace('/^[^_]+_/', '', $attrCode);
            // dd($customTableMetadata);
            $productTypeAttrCode = $customTableMetadata['productTypeAttrCode'] ?? null;
            $productTypeVal = $rowData[$productTypeAttrCode] ?? null;
            // dd($productTypeVal);
            // dd($column);
            $tables = [];

            $mainAttrCode = $customTableMetadata['mainAttrCode'] ?? null;
            $mainAttrValLabel = $rowData[$mainAttrCode] ?? null;
            $mainAttrVal = $this->getOptionId($mainAttrCode, $mainAttrValLabel);


            $singlesTableName = $customTableMetadata['singlesTableName'] ?? null;
            if ($singlesTableName) {
                array_push($tables, $singlesTableName);
            }

            $setsTableName = $customTableMetadata['setsTableName'] ?? null;
            if ($setsTableName) {
                array_push($tables, $setsTableName);
            }

            $sealedProductsTableName = $customTableMetadata['sealedProductsTableName'] ?? null;
            if ($sealedProductsTableName) {
                array_push($tables, $sealedProductsTableName);
            }

            $connection = $this->resource->getConnection();
            
            $tableNameArr = $this->getCustomTableNameArr($column, $tables);
            if ($tableNameArr) {
                foreach($tableNameArr as $tableName) {
                    if ($tableName == $singlesTableName) {
                        $select = $connection->select()
                        ->from(['a' => $tableName], [$column]) // alias tabel a
                        ->join(
                            ['b' => $setsTableName],                 // alias tabel b
                            'a.set_id = b.entity_id',             // join condition
                            []                                    // [] = tidak ambil kolom dari b
                        )
                        ->where('a.' . $column . ' = ?', $value)
                        ->where('b.' . $mainAttrCode . ' = ?', $mainAttrVal)
                        ->limit(1);

                        // dd($select->__toString());
                    } else {
                        $select = $connection->select()
                        ->from($tableName, [$column])
                        ->where($column . ' = ?', $value)
                        ->where($mainAttrCode . ' = ?', $mainAttrVal)
                        ->limit(1);
                    }
                    

                    $result = $connection->fetchOne($select);
                    if ($result !== false) {
                        $this->getOrInputAttributeOptionId($attrCode, $value);
                        return true;
                    }
                }
            }
        }

        return false;
    }

    public function getCustomTableNameArr($column, $tables) 
    {
        $connection = $this->resource->getConnection();

        $existColumnTableArr = [];

        foreach ($tables as $table) {
            $fullTableName = $this->resource->getTableName($table);

            if ($connection->tableColumnExists($fullTableName, $column)) {
                $existColumnTableArr[] = $fullTableName;
            }
        }

        return $existColumnTableArr;
    }

    public function filterByCustomAttributes($attributeCode, $mainAttrCode, $mainAttrVal, $options) 
    {
        $customAttributes = $this->customAttributesCache->getCustomAttributesData();
    }

    public function getAllowedCustomAttributesOptions($attributeCode, $mainAttrCode, $mainAttrVal) {
        return $this->customAttributesCache->getCustomAttributesOptions($attributeCode, $mainAttrCode, $mainAttrVal);
    }

    public function getCustomAttributesOptions($attributeCode, $mainAttrCode, $mainAttrVal) {
        return $this->customAttributesCache->getCustomAttributesOptions($attributeCode, $mainAttrCode, $mainAttrVal);
    }

    public function getDateTime($format)
    {
        // This returns a \DateTime object set to the store's timezone
        $dateTime = $this->timezone->date();
        return $dateTime->format($format); // Format like PHP's date('H:i:s')
    }

    public function getCustomTableFormPageTitle($customTableName, $mainAttrCode) 
    {
        $title = [];

        $setsTableName = null;
        $mainSetsAttrCode = null;
        $sealedProductsAttrCode = null;
        $model = null;

        // Get model
        if ($customTableName == "sealedproducts") {
            // Action Toys / Anime
            if ($mainAttrCode == "toys_brand") {
                $setsTableName = "marketplace_action_toy_sets";
                $mainSetsAttrCode = "set_name";
                $sealedProductsAttrCode = 'sealed_products';
                $model = $this->actionToySealedProducts->create();
            }

            if ($mainAttrCode == "sc_categories") {
                $setsTableName = "marketplace_sport_cards_sets";
                $mainSetsAttrCode = "sc_set_name";
                $sealedProductsAttrCode = 'sealed_products';
                $model = $this->sportCardsSealedProducts->create();
            }

            // Non Sport Cards
            if ($mainAttrCode == "nsc_categories") {
                $setsTableName = "marketplace_non_sport_cards_sets";
                $mainSetsAttrCode = "nsc_set_name";
                $sealedProductsAttrCode = 'sealed_products';
                $model = $this->nonSportCardsSealedProducts->create();
            }
        }

        // Get custom table data
        if ($model) {
            $collection = $model->getCollection();

            $entityId = $this->request->getParam('entity_id');
            if ($entityId) {
                $collection->addFieldToFilter('entity_id', $entityId);
            }

            // if ($customTableName == "sealedproducts") {
            //     $collection->getSelect()->join(
            //         ['sets' => $setsTableName],
            //         'sets.entity_id = main_table.set_id',
            //         [$mainSetsAttrCode]
            //     );
            // }

            if ($collection->getSize()) {
                if ($customTableName == "sealedproducts") {
                    $sealedProducts = $collection->getFirstItem();

                    $this->registry->register('current_edit_sealedproducts', $sealedProducts);

                    $title[] = $sealedProducts->getData($sealedProductsAttrCode);
                }
            }
        }

        return $title ? implode(" | ", $title) : 'Untitled';
    }

    public function getCustomTableListingPageTitle($customTableName, $mainAttrCode) 
    {
        $title = [];

        $model = null;
        if ($customTableName == "sealedproducts") {
            $title[] = 'Sealed Products Database';
        }

        if ($customTableName == "sets") {
            $title[] = 'Sets / Release Database';
        }

        if ($customTableName == "singles") {
            $title[] = 'Singles Database';
        }

        $setId = $this->request->getParam('set_id');

        // Get param value
        $mainAttrValue = $this->request->getParam($mainAttrCode);
        if ($mainAttrValue) {
            // Set main categories label
            $mainAttrValueLabel = $this->getOptionLabelById($mainAttrCode, $mainAttrValue);
            if ($mainAttrValueLabel) {
                $title[] = $mainAttrValueLabel;
            }
        }

        // Custom table data
        if ($mainAttrCode) {
            // Action Toys / Anime
            if ($mainAttrCode == "toys_brand") {
                $setsTableName = "marketplace_action_toy_sets";
                $mainSetsAttrCode = "set_name";

                if ($customTableName == "sealedproducts") {
                    $model = $this->actionToySealedProducts->create();
                }
            }

            // Fashion Dolls
            if ($mainAttrCode == "dolls_brand") {
                $setsTableName = "marketplace_dolls_sets";
                $mainSetsAttrCode = "dolls_set_name";

                if ($customTableName == "sealedproducts") {
                    $model = $this->dollsSealedProductsFactory->create();
                }
            }

            // Card Game
            if ($mainAttrCode == "card_game") {
                $setsTableName = "marketplace_card_games_sets";
                $mainSetsAttrCode = "card_set";
                if ($customTableName == "sets") {
                    $model = $this->cardGameSets->create();
                }

                if ($customTableName == "singles") {
                    $model = $this->cardGameSetsCard->create();
                }

                if ($customTableName == "sealedproducts") {
                    $model = $this->cardGameSealedProducts->create();
                }
            }

            // Living Card Game
            if ($mainAttrCode == "living_card_game") {
                $setsTableName = "marketplace_living_card_games_sets";
                $mainSetsAttrCode = "living_card_set";
                if ($customTableName == "sealedproducts") {
                    $model = $this->livingCardGameSealedProducts->create();
                }
            }

            // Miniature Game
            if ($mainAttrCode == "miniature_game") {
                $setsTableName = "marketplace_miniature_games_sets";
                $mainSetsAttrCode = "miniature_set_name";
                if ($customTableName == "sealedproducts") {
                    $model = $this->miniatureGameSealedProducts->create();
                }
            }

            if ($mainAttrCode == "sc_categories") {
                $setsTableName = "marketplace_sport_cards_sets";
                $mainSetsAttrCode = "sc_set_name";
                if ($customTableName == "sealedproducts") {
                    $model = $this->sportCardsSealedProducts->create();
                }
            }

            // Non Sport Cards
            if ($mainAttrCode == "nsc_categories") {
                $setsTableName = "marketplace_non_sport_cards_sets";
                $mainSetsAttrCode = "nsc_set_name";
                if ($customTableName == "sealedproducts") {
                    $model = $this->nonSportCardsSealedProducts->create();
                }
            }

            // Bricks
            if ($mainAttrCode == "bricks_brand") {
                $setsTableName = "marketplace_lego_sets";
                $mainSetsAttrCode = "bricks_set_name";
                if ($customTableName == "sealedproducts") {
                    $model = $this->legoSealedProducts->create();
                }
            }

            // Trains
            if ($mainAttrCode == "railway_brand") {
                $setsTableName = "marketplace_railway_sets";
                $mainSetsAttrCode = "railway_set_name";
                if ($customTableName == "sealedproducts") {
                    $model = $this->railwaySealedProductsFactory->create();
                }
            }
        }

        // Get custom table data
        if ($model && $customTableName != "sets" && $setId) {
            $collection = $model->getCollection();

            if ($mainAttrValue) {
                if ($customTableName == "sealedproducts" || $customTableName == "singles") {
                    $collection->addFieldToFilter('sets.' . $mainAttrCode, $mainAttrValue);
                } else {
                    $collection->addFieldToFilter('main_table.' . $mainAttrCode, $mainAttrValue);
                }
            }

            if ($customTableName == "sealedproducts" || $customTableName == "singles") {
                $collection->getSelect()->join(
                    ['sets' => $setsTableName],
                    'sets.entity_id = main_table.set_id',
                    [$mainSetsAttrCode]
                );
            }

            if ($collection->getSize()) {
                $title[] = $collection->getFirstItem()->getData($mainSetsAttrCode);
            }
        }

        return $title ? implode(" | ", $title) : 'Untitled';
    }

    public function getSinglesTableName($mainAttrCode) 
    {
        // Sport Cards | Singles
        if ($mainAttrCode == "sc_categories") {
            return 'marketplace_sport_cards_sets_card';
        }

        // Card Game | Singles
        if ($mainAttrCode == "card_game") {
            return 'marketplace_card_games_sets_card';
        }

        return null;
    }

    public function getSealedProductsAllowedFields($mainAttrCode) 
    {
        $allowedFields = [];

        // Card Game | Sealed Products | Export Fields
        if ($mainAttrCode == "card_game") {
            $allowedFields = [
                "images",
                "name",
                "supplier_sku",
                "card_release",
                "card_release_jp",
                "sealed_products",
                "release_type",
                "release_date",
                "release_year",
                "card_print_version",
                "bar_code",
                "bar_code_no",
                "language"
            ];
        }

        if ($mainAttrCode == "sc_categories") {
            $allowedFields = ['images','sc_categories', 'sc_brand', 'sc_brand_line', 'sc_league', 'sc_product_type', 'sc_sealed_products', 'sc_release', 'sc_release_jp', 'name','supplier_sku', 'release_date', 'release_year', 'sc_card_manufacturer', 'language', 'bar_code', 'bar_code_no', 'age_level'];
        }

        return $allowedFields;
    }

    public function getSetsAllowedFields($mainAttrCode) 
    {
        $allowedFields = [];

        // Sport Cards | Sets
        if ($mainAttrCode == "sc_categories") {
            $allowedFields = [
                'images',
                'sc_categories',
                'sc_product_type',
                'sc_league',
                'sc_release',
                'sc_set_type',
                'sc_release_jp',
                'sc_set_name',
                'name',
                'sc_feature',
                'sc_brand',
                'sc_brand_line',
                'release_date',
                'release_year',
                'sc_card_manufacturer',
                'language',
                'bar_code',
                'bar_code_no',
                'age_level',
                'number_of_cards',
                'sc_players',
                'sc_teams',
                'sc_card_feature',
                'rookie_card',
                'autographed',
                'serial_numbered',
                'graded',
                'item_number',
                'sc_card_condition',
                'sc_card_grade',
                'sc_card_grader'
            ];
        }

        // Card Game | Sets
        if ($mainAttrCode == "card_game") {
            $allowedFields = [
                'images',
                'name',
                'card_game',
                'card_set',
                'card_set_abbreviation',
                'card_release',
                'release_type',
                'release_date',
                'release_year',
                'card_manufacturer',
                'language',
                'card_series',
                'card_print_version',
                'bar_code',
                'bar_code_no',
                'age_level',
                'card_set_jp',
                'number_of_cards',
                'language_version',
                'card_release_jp'
            ];
        }

        return $allowedFields;
    }

    public function getSetsModel($mainAttrCode) 
    {
        // Sport Cards | Sets Model
        if ($mainAttrCode == "sc_categories") {
            return $this->sportCardsSets->create();
        }

        // Card Game | Sets Model
        if ($mainAttrCode == "card_game") {
            return $this->cardGameSets->create();
        }

        return null;
    }

    public function getSealedProductsModel($mainAttrCode) 
    {
        // Card Game | Sealed Products Model
        if ($mainAttrCode == "card_game") {
            return $this->cardGameSealedProducts->create();
        }

        // Card Game | Sealed Products Model
        if ($mainAttrCode == "sc_categories") {
            return $this->sportCardsSealedProducts->create();
        }

        return null;
    }

    public function convertToFileName($str) 
    {
        // Convert the string to lowercase
        $string = strtolower($str);

        // Replace spaces and special characters with underscores
        $string = preg_replace('/[^a-z0-9]+/', '_', $string);

        // Trim any leading or trailing underscores
        $string = trim($string, '_');

        return $string;
    }

    public function getCategoriesIdentifier($mainAttrCode) 
    {
        if ($mainAttrCode == "sc_categories") {
            return 'sport_card';
        }

        return $mainAttrCode;
    }

    public function getPlatformDatabaseCategoryName() 
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->redirect = $objectManager->get(\Magento\Framework\App\Response\RedirectInterface::class);

        $refererUrl = $this->redirect->getRefererUrl();
        // dd($refererUrl);

        $categoryName = null;

        // CARD GAME
        if (str_contains($refererUrl, 'dolls')) 
        {
            $categoryName = 'Dolls';
        }
        // dd($categoryName)

        // CARD GAME
        if (str_contains($refererUrl, 'cardgame')) 
        {
            $categoryName = 'Collectible Card Games';
        }

        // CARD GAME
        if (str_contains($refererUrl, 'sportcard')) 
        {
            $categoryName = 'Sport Cards';
        }

        return $categoryName;
    }

    public function exportSealedProducts($mainAttrCode) 
    {
        $objectManager =  \Magento\Framework\App\ObjectManager::getInstance();
        $this->request = $objectManager->get('\Magento\Framework\App\RequestInterface');
        $this->_redirect = $objectManager->get('\Magento\Store\App\Response\Redirect');
        $filesystem = $objectManager->get('\Magento\Framework\Filesystem');
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);

        $mainSetsAttrCode = null;
        $setsTableName = null;

        $categoryName = $this->getPlatformDatabaseCategoryName();

        $customTableMetadata = $this->getCustomTableMetadata(null, $categoryName);
        if ($customTableMetadata) {
            $mainSetsAttrCode = $customTableMetadata['mainSetsAttrCode'] ?? null;
            $setsTableName = $customTableMetadata['setsTableName'] ?? null;
        }

        $result = [
            'success' => false,
            'name' => null,
            'file' => null,
            'refererUrl' => null
        ];

        // Get singles table name
        $singlesTableName = $this->getSinglesTableName($mainAttrCode);

        // Get sets collection
        $sealedProductsModel = $this->getSealedProductsModel($mainAttrCode);

        // Get main attribute code value
        $mainAttrValue = null;
        $refererUrl = $this->_redirect->getRefererUrl();
        if ($refererUrl) {
            
            $result['refererUrl'] = $refererUrl;
            
            if (preg_match('/'.$mainAttrCode.'\/(\d+)/', $refererUrl, $matches)) {
                $mainAttrValue = $matches[1];
            }
        }

        // Make sure attribute code value is exist
        if ($mainAttrValue && $sealedProductsModel) {

            // Get sets data
            $collection = $sealedProductsModel->getCollection();

            // Filter by main attribute code
            if ($mainAttrCode) {
                $collection->addFieldToFilter($mainAttrCode, $mainAttrValue);
            }

            // Only select allowed fields
            $allowedFields = $this->getSealedProductsAllowedFields($mainAttrCode);
            $collection->addFieldToSelect($allowedFields);

            // if ($mainAttrCode == "card_game") {
            //     $releaseAttrCode = 'card_release';

            //     $collection->getSelect()->joinLeft(
            //         ['sets' => $setsTableName],
            //         "main_table.{$releaseAttrCode}=sets.{$releaseAttrCode}",
            //         [
            //             $mainAttrCode
            //         ]
            //     )->group('main_table.entity_id');
            // }

            if ($mainAttrCode == "sc_categories") {
                $releaseAttrCode = 'sc_release';
            }

            // Handle data not found
            if (!$collection->getSize()) {
                $this->messageManager->addError(__('We can\'t find a record to exportcsv.'));

                return $result;
            }

            $mainSetsAttrValLabel = null;

            // Prepare headers and data
            $params = [];
            $headers = [];
            foreach ($collection as $model) {
                $data = $model->getData();
                if ($data) {
                    $params[] = $data;
                    
                    if ($mainSetsAttrCode) {
                        $mainSetsAttrValLabel = $data[$mainSetsAttrCode] ?? null;
                    }

                    $headers = array_keys($data);
                }
            }

            $headers[] = $mainAttrCode;

            // Set filename
            $fileName = '';
        
            $mainAttrValueLabel = $this->getOptionLabelById($mainAttrCode, $mainAttrValue);
            if ($mainAttrValueLabel) {
                $fileName .= $this->convertToFileName($mainAttrValueLabel);
            }
            
            $fileName .= '-' . 'sealed_products';

            $fileName .= '__' . $this->getDateTime('Y-m-d') . '_' . $this->getDateTime('H-i-s') . '.csv';
            
            // File path
            $file = 'export/sealed_products/'. $fileName;

            // Generate csv file
            $this->directory->create('export');
            $stream = $this->directory->openFile($file, 'w+');
            $stream->lock();
            $stream->writeCsv($headers);

            // Process data before write to CSV
            foreach ($params as $row) {
                // Display only image name and its extension without path
                $imageName = $row['images'] ? basename($row['images']) : '';
                $row['images'] = $imageName;

                // Add main attribute code
                $row[$mainAttrCode] = $mainAttrValueLabel;

                // dd($row);

                $stream->writeCsv($row);
            }
            
            $stream->unlock();
            $stream->close();

            $file = [
                'type' => 'filename',
                'value' => $file,
                'rm' => true  // can delete file after use
            ];
            
            $result = [
                'success' => true,
                'name' => $fileName,
                'file' => $file
            ];
        }

        return $result;
    }

    public function exportSets($mainAttrCode) 
    {
        $objectManager =  \Magento\Framework\App\ObjectManager::getInstance();
        $this->request = $objectManager->get('\Magento\Framework\App\RequestInterface');
        $this->_redirect = $objectManager->get('\Magento\Store\App\Response\Redirect');
        $filesystem = $objectManager->get('\Magento\Framework\Filesystem');
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);

        $mainSetsAttrCode = null;

        $categoryName = $this->getPlatformDatabaseCategoryName();

        $customTableMetadata = $this->getCustomTableMetadata(null, $categoryName);
        if ($customTableMetadata) {
            $mainSetsAttrCode = $customTableMetadata['mainSetsAttrCode'] ?? null;
        }

        $result = [
            'success' => false,
            'name' => null,
            'file' => null,
            'refererUrl' => null
        ];

        // Get singles table name
        $singlesTableName = $this->getSinglesTableName($mainAttrCode);

        // Get sets collection
        $setsModel = $this->getSetsModel($mainAttrCode);

        // Get main attribute code value
        $mainAttrValue = null;
        $refererUrl = $this->_redirect->getRefererUrl();
        if ($refererUrl) {
            
            $result['refererUrl'] = $refererUrl;
            
            if (preg_match('/'.$mainAttrCode.'\/(\d+)/', $refererUrl, $matches)) {
                $mainAttrValue = $matches[1];
            }
        }

        // Make sure attribute code value is exist
        if ($mainAttrValue && $setsModel) {

            // Get sets data
            $collection = $setsModel->getCollection();

            // Filter by main attribute code
            $collection->addFieldToFilter($mainAttrCode, $mainAttrValue);

            // Only select allowed fields
            $allowedFields = $this->getSetsAllowedFields($mainAttrCode);
            $collection->addFieldToSelect($allowedFields);

            // Filter sets that only having singles
            $singles = $this->request->getParam('singles') ? true : false;
            if ($singles) {
                $collection->getSelect()->join(
                    ['singles' => $singlesTableName],
                    'main_table.entity_id = singles.set_id',
                    []
                )->group('main_table.entity_id');
            }

            // Handle data not found
            if (!$collection->getSize()) {
                $this->messageManager->addError(__('We can\'t find a record to exportcsv.'));

                return $result;
            }

            $mainSetsAttrValLabel = null;

            // Prepare headers and data
            $params = [];
            $headers = [];
            foreach ($collection as $model) {
                $data = $model->getData();
                if ($data) {
                    $params[] = $data;
                    
                    if ($mainSetsAttrCode) {
                        $mainSetsAttrValLabel = $data[$mainSetsAttrCode] ?? null;
                    }

                    $headers = array_keys($data);
                }
            }

            // Set filename
            $fileName = '';
        
            $mainAttrValueLabel = $this->getOptionLabelById($mainAttrCode, $mainAttrValue);
            if ($mainAttrValueLabel) {
                $fileName .= $this->convertToFileName($mainAttrValueLabel);
            }
            
            $fileName .= '-' . 'sets';

            $fileName .= '__' . $this->getDateTime('Y-m-d') . '_' . $this->getDateTime('H-i-s') . '.csv';
            
            // File path
            $file = 'export/sets/'. $fileName;

            // Generate csv file
            $this->directory->create('export');
            $stream = $this->directory->openFile($file, 'w+');
            $stream->lock();
            $stream->writeCsv($headers);

            // Process data before write to CSV
            foreach ($params as $row) {
                // Display only image name and its extension without path
                $imageName = $row['images'] ? basename($row['images']) : '';
                $row['images'] = $imageName;

                // Add main attribute code
                $row[$mainAttrCode] = $mainAttrValueLabel;

                $stream->writeCsv($row);
            }
            
            $stream->unlock();
            $stream->close();

            $file = [
                'type' => 'filename',
                'value' => $file,
                'rm' => true  // can delete file after use
            ];
            
            $result = [
                'success' => true,
                'name' => $fileName,
                'file' => $file
            ];
        }

        return $result;
    }

    public function getSetsTableName($mainAttrCode) 
    {
        // Sport Cards | Sets
        if ($mainAttrCode == "sc_categories") {
            return 'marketplace_sport_cards_sets';
        }

        // Card Game | Sets
        if ($mainAttrCode == "card_game") {
            return 'marketplace_card_games_sets';
        }

        return null;
    }

    public function getSinglesAllowedFields($mainAttrCode) 
    {
        $allowedFields = [];

        // Sport Cards | Singles
        if ($mainAttrCode == "sc_categories") {
            $allowedFields = [
                'images',
                'name',
                'supplier_sku',
                'sc_card_name',
                'sc_card_number',
                'sc_players',
                'sc_teams',
                'sc_card_feature',
                'rookie_card',
                'autographed',
                'serial_numbered',
                'graded',
                'item_number',
                'sc_card_condition',
                'sc_card_grade',
                'sc_card_grader'
            ];
        }

        // Card Game | Singles
        if ($mainAttrCode == "card_game") {
            $allowedFields = [
                "images",
                "name",
                "supplier_sku",
                "card_name",
                "card_name_jp",
                "card_number",
                "card_rarity",
                "card_foil",
                "card_type",
                "card_sub_type",
                "card_feature",
                "card_artist",
                "card_condition",
                "card_grade",
                "card_grader",
                "card_energy_type",
                "card_hp",
                "card_stage",
                "card_illustration",
                "card_category",
                "card_attribute",
                "card_ink",
                "card_color",
                "card_franchise"
            ];
        }

        return $allowedFields;
    }

    public function getSinglesModel($mainAttrCode) 
    {
        // Sport Cards | Singles Model
        if ($mainAttrCode == "sc_categories") {
            return $this->sportCardsSetsCard->create();
        }

        // Card Game | Singles Model
        if ($mainAttrCode == "card_game") {
            return $this->cardGameSetsCard->create();
        }

        return null;
    }

    public function getFieldToSelectFromSets($mainAttrCode) 
    {
        if ($mainAttrCode == "card_game") {
            return [
                'card_game',
                'card_set',
                'card_set_abbreviation',
                'card_release',
                'release_type',
                'release_date',
                'release_year',
                'card_manufacturer',
                'language',
                'card_series',
                'card_print_version',
                'bar_code',
                'bar_code_no',
                'age_level',
                'card_set_jp',
                'number_of_cards',
                'language_version',
                'card_release_jp'
            ];
        }

        if ($mainAttrCode == "sc_categories") {
            return [
                "sc_categories",
                "sc_product_type",
                "sc_release",
                "sc_release_jp",
                "sc_season",
                "sc_league",
                "sc_set_type",
                "sc_set_name",
                "sc_brand",
                "sc_brand_line",
                "release_date",
                "release_year",
                "sc_card_manufacturer",
                "language",
                "bar_code",
                "bar_code_no",
                "age_level",
                "number_of_cards",
                "language"
            ];
        }

        return null;
    }

    public function exportSingles($mainAttrCode) 
    {
        $objectManager =  \Magento\Framework\App\ObjectManager::getInstance();
        $this->request = $objectManager->get('\Magento\Framework\App\RequestInterface');
        $this->_redirect = $objectManager->get('\Magento\Store\App\Response\Redirect');
        $filesystem = $objectManager->get('\Magento\Framework\Filesystem');
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);

        $mainSetsAttrCode = null;

        $categoryName = $this->getPlatformDatabaseCategoryName();

        $customTableMetadata = $this->getCustomTableMetadata(null, $categoryName);
        if ($customTableMetadata) {
            $mainSetsAttrCode = $customTableMetadata['mainSetsAttrCode'] ?? null;
        }

        $result = [
            'success' => false,
            'name' => null,
            'file' => null,
            'refererUrl' => null
        ];

        // Get singles table name
        $setsTableName = $this->getSetsTableName($mainAttrCode);

        // Get singles collection
        $singlesModel = $this->getSinglesModel($mainAttrCode);

        // Get singles collection
        $fieldToSelectFromSets = $this->getFieldToSelectFromSets($mainAttrCode);
        if (!$fieldToSelectFromSets) {
            $fieldToSelectFromSets = [$mainAttrCode];
        }

        // Get main attribute code value
        $setId = null;
        $mainAttrVal = null;
        $refererUrl = $this->_redirect->getRefererUrl();
        if ($refererUrl) {
            $result['refererUrl'] = $refererUrl;
            
            if (preg_match('/set_id\/(\d+)/', $refererUrl, $matches)) {
                $setId = $matches[1]; // The first capturing group contains the set_id
            }
            
            if (preg_match('/' . $mainAttrCode.'\/(\d+)/', $refererUrl, $matches)) {
                $mainAttrVal = $matches[1]; // The first capturing group contains the set_id
            }
        }

        // Make sure set id is exist
        if ($setId || $mainAttrVal) {

            // Get sets data
            $collection = $singlesModel->getCollection();

            // Filter by set id
            if ($setId) {
                $collection->addFieldToFilter('set_id', $setId);
            }

            if ($mainAttrVal) {
                $collection->addFieldToFilter($mainAttrCode, $mainAttrVal);
            }

            // Only select allowed fields
            $allowedFields = $this->getSinglesAllowedFields($mainAttrCode);
            $collection->addFieldToSelect($allowedFields);

            // Get main attribute data from sets
            if ($setsTableName) {
                $collection->getSelect()->join(
                    ['sets' => $setsTableName],
                    'sets.entity_id = main_table.set_id',
                    $fieldToSelectFromSets
                )->group('main_table.entity_id');

                // dd($fieldToSelectFromSets);
            }

            // Handle data not found
            if (!$collection->getSize()) {
                $this->messageManager->addError(__('We can\'t find a record to exportcsv.'));

                return $result;
            }

            $mainAttrValue = null;
            $mainSetsAttrValLabel = null;

            // Prepare headers and data
            $params = [];
            $headers = [];
            foreach ($collection as $model) {
                $data = $model->getData();
                if ($data) {
                    $params[] = $data;

                    if ($mainSetsAttrCode) {
                        $mainSetsAttrValLabel = $data[$mainSetsAttrCode] ?? null;
                    }

                    if (isset($data[$mainAttrCode])) {
                        $mainAttrValue = $data[$mainAttrCode];
                    }

                    $headers = array_keys($data);
                }
            }

            // Set filename
            $fileName = '';
    
            if ($mainAttrValue) {
                $mainAttrValueLabel = $this->getOptionLabelById($mainAttrCode, $mainAttrValue);
                if ($mainAttrValueLabel) {
                    $fileName .= $this->convertToFileName($mainAttrValueLabel);
                }
            }
            
            // Include set name on file name
            if ($mainSetsAttrValLabel && $setId) {
                $fileName .= '-' . $this->convertToFileName($mainSetsAttrValLabel);
            }

            $fileName .= '__' . $this->getDateTime('Y-m-d') . '_' . $this->getDateTime('H-i-s') . '.csv';
            
            // File path
            $file = 'export/singles/'. $fileName;

            // Generate csv file
            $this->directory->create('export');
            $stream = $this->directory->openFile($file, 'w+');
            $stream->lock();
            $stream->writeCsv($headers);

            // Process data before write to CSV
            foreach ($params as $row) {
                // Display only image name and its extension without path
                $imageName = $row['images'] ? basename($row['images']) : '';
                $row['images'] = $imageName;

                // Add main attribute code
                $row[$mainAttrCode] = $mainAttrValueLabel;

                $stream->writeCsv($row);
            }
            
            $stream->unlock();
            $stream->close();

            $file = [
                'type' => 'filename',
                'value' => $file,
                'rm' => true  // can delete file after use
            ];
            
            $result = [
                'success' => true,
                'name' => $fileName,
                'file' => $file
            ];
        }

        return $result;
    }

    public function getSetsById($customTableName, $setId) 
    {
        if ($customTableName == "actiontoys" || $customTableName == "action_toys") {
            if ($setId) {
                $collection = $this->actionToySetsFactory->create()
                ->getCollection()
                ->addFieldToFilter('entity_id', $setId);
                if ($collection->getSize()) {
                    return $collection->getFirstItem();
                }
            }

            if ($input) {
                return $this->actionToySetsFactory->create();
            }
        }

        if ($customTableName == "diecastmodelkits") {
            if ($setId) {
                $collection = $this->diecastModelKitsSetsFactory->create()
                ->getCollection()
                ->addFieldToFilter('entity_id', $setId);
                if ($collection->getSize()) {
                    return $collection->getFirstItem();
                }
            }

            if ($input) {
                return $this->diecastModelKitsSetsFactory->create();
            }
        }

        if ($customTableName == "dolls") {
            if ($setId) {
                $collection = $this->dollsSetsFactory->create()
                ->getCollection()
                ->addFieldToFilter('entity_id', $setId);
                if ($collection->getSize()) {
                    return $collection->getFirstItem();
                }
            }

            if ($input) {
                return $this->dollsSetsFactory->create();
            }
        }

        if ($customTableName == "trains") {
            if ($setId) {
                $collection = $this->railwaySetsFactory->create()
                ->getCollection()
                ->addFieldToFilter('entity_id', $setId);
                if ($collection->getSize()) {
                    return $collection->getFirstItem();
                }
            }

            if ($input) {
                return $this->railwaySetsFactory->create();
            }
        }

        return null;
    }

    public function getSinglesById($customTableName, $singlesId) 
    {
        if ($customTableName == "diecastmodelkits") {
            if ($singlesId) {
                $collection = $this->diecastModelKitsSinglesFactory->create()
                ->getCollection()
                ->addFieldToFilter('entity_id', $singlesId);
                if ($collection->getSize()) {
                    return $collection->getFirstItem();
                }
            }

            if ($input) {
                return $this->diecastModelKitsSinglesFactory->create();
            }
        }

        if ($customTableName == "dolls") {
            if ($singlesId) {
                $collection = $this->dollsSinglesFactory->create()
                ->getCollection()
                ->addFieldToFilter('entity_id', $singlesId);
                if ($collection->getSize()) {
                    return $collection->getFirstItem();
                }
            }

            if ($input) {
                return $this->dollsSinglesFactory->create();
            }
        }

        if ($customTableName == "trains") {
            if ($singlesId) {
                $collection = $this->railwaySinglesFactory->create()
                ->getCollection()
                ->addFieldToFilter('entity_id', $singlesId);
                if ($collection->getSize()) {
                    return $collection->getFirstItem();
                }
            }

            if ($input) {
                return $this->railwaySinglesFactory->create();
            }
        }

        return null;
    }

    public function getCustomTableMetadata($categoryId = null, $categoryName = null) 
    {
        if ($categoryId) {
            $category = $this->getCategoryById($categoryId);
            if ($category) {
                $categoryName = $category->getName();
            }
        }

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->legoSets = $objectManager->create(\TCGCollective\ProductAttributesLink\Model\LegoSetsFactory::class);
        // $this->railwaySets = $objectManager->create(\TCGCollective\ProductAttributesLink\Model\RailwaySetsFactory::class);
        // $this->railwaySinglesFactory = $objectManager->create(\TCGCollective\ProductAttributesLink\Model\RailwaySinglesFactory::class);
        $this->legoSinglesFactory = $objectManager->create(\TCGCollective\ProductAttributesLink\Model\LegoSinglesFactory::class);
        $this->cardGameSetsCard = $objectManager->create(\TCGCollective\ProductAttributesLink\Model\CardGamesSetsCardFactory::class);
        $this->cardGameSets = $objectManager->create(\TCGCollective\ProductAttributesLink\Model\CardGamesSetsFactory::class);
        $this->livingCardGameSets = $objectManager->create(\TCGCollective\ProductAttributesLink\Model\LivingCardGameSetsFactory::class);
        $this->miniatureGameSingles = $objectManager->create(\TCGCollective\ProductAttributesLink\Model\MiniatureGameSinglesFactory::class);
        $this->miniatureGameSets = $objectManager->create(\TCGCollective\ProductAttributesLink\Model\MiniatureGameSetsFactory::class);
        $this->nonSportCardsSets = $objectManager->create(\TCGCollective\ProductAttributesLink\Model\NonSportCardsSetsFactory::class);
        $this->sportCardsSinglesFactory = $objectManager->create(\TCGCollective\ProductAttributesLink\Model\SportCardsSetsCardFactory::class);
        $this->sportCardsSetsFactory = $objectManager->create(\TCGCollective\ProductAttributesLink\Model\SportCardsSetsFactory::class);
        $this->actionToysSetsFactory = $objectManager->create(\TCGCollective\ProductAttributesLink\Model\ActionToySetsFactory::class);
        $this->actionToysSinglesFactory = $objectManager->create(\TCGCollective\ProductAttributesLink\Model\ActionToySinglesFactory::class);

        $this->actionToysModelKitsSetsFactory = $objectManager->create('\TCGCollective\ProductAttributesLink\Model\ActionToysModelKitsSetsFactory');
        $this->actionToysModelKitsSinglesFactory = $objectManager->create('\TCGCollective\ProductAttributesLink\Model\ActionToysModelKitsSinglesFactory');

        $this->animeToysSetsFactory = $objectManager->create('\TCGCollective\ProductAttributesLink\Model\AnimeSetsFactory');
        $this->animeToysSinglesFactory = $objectManager->create('\TCGCollective\ProductAttributesLink\Model\AnimeSinglesFactory');

        $this->animeModelKitsSetsFactory = $objectManager->create('\TCGCollective\ProductAttributesLink\Model\AnimeModelKitsSetsFactory');
        $this->animeModelKitsSinglesFactory = $objectManager->create('\TCGCollective\ProductAttributesLink\Model\AnimeModelKitsSinglesFactory');

        $this->gundamSetsFactory = $objectManager->create('\TCGCollective\ProductAttributesLink\Model\GundamSetsFactory');
        $this->gundamSinglesFactory = $objectManager->create('\TCGCollective\ProductAttributesLink\Model\GundamSinglesFactory');

        $this->fungoodsSetsFactory = $objectManager->create('\TCGCollective\ProductAttributesLink\Model\FunGoodsSetsFactory');
        $this->fungoodsSinglesFactory = $objectManager->create('\TCGCollective\ProductAttributesLink\Model\FunGoodsSinglesFactory');

        // DIECAST
        $this->diecastVehiclesSetsFactory = $objectManager->create('\TCGCollective\ProductAttributesLink\Model\DiecastVehiclesSetsFactory');
        $this->diecastVehiclesSinglesFactory = $objectManager->create('\TCGCollective\ProductAttributesLink\Model\DiecastVehiclesSinglesFactory');

        $this->diecastAircraftsSetsFactory = $objectManager->create('\TCGCollective\ProductAttributesLink\Model\DiecastAircraftsSetsFactory');
        $this->diecastAircraftsSinglesFactory = $objectManager->create('\TCGCollective\ProductAttributesLink\Model\DiecastAircraftsSinglesFactory');

        $this->diecastMilitarySetsFactory = $objectManager->create('\TCGCollective\ProductAttributesLink\Model\DiecastMilitarySetsFactory');
        $this->diecastMilitarySinglesFactory = $objectManager->create('\TCGCollective\ProductAttributesLink\Model\DiecastSinglesFactory');

        // MODEL KITS
        $this->modelKitsVehiclesSetsFactory = $objectManager->create('\TCGCollective\ProductAttributesLink\Model\ModelKitsVehiclesSetsFactory');
        $this->modelKitsVehiclesSinglesFactory = $objectManager->create('\TCGCollective\ProductAttributesLink\Model\ModelKitsVehiclesSinglesFactory');

        $this->modelKitsAircraftsSetsFactory = $objectManager->create('\TCGCollective\ProductAttributesLink\Model\ModelKitsAircraftsSetsFactory');
        $this->modelKitsAircraftsSinglesFactory = $objectManager->create('\TCGCollective\ProductAttributesLink\Model\ModelKitsAircraftsSinglesFactory');

        $this->modelKitsMilitarySetsFactory = $objectManager->create('\TCGCollective\ProductAttributesLink\Model\ModelKitsMilitarySetsFactory');
        $this->modelKitsMilitarySinglesFactory = $objectManager->create('\TCGCollective\ProductAttributesLink\Model\ModelKitsSinglesFactory');

        $mainAttrCode = null;
        $mainSetsAttrCode = null;
        $mainReleaseAttrCode = null;
        $mainSetsNumberAttrCode = null;
            
        $mainSinglesAttrCode = null;
        $mainSinglesNumberAttrCode = null;
        $singlesCollection = null;

        $setsTableName = null;
        $singlesTableName = null;
        $sealedProductsTableName = null;
        $setsFieldToSelet = null;
    
        $setsCollection = null;
        $sealedProductsCollection = null;

        $setsOrdering = null;
        $sealedProductsOrdering = null;

        $productTypeAttrCode = null;
        $viewSinglesUrlPath = null;

        $setsFieldToSelectFromSingles = [];

        $setsFieldToSelectFromSealedProducts = null;
        $viewSetsUrlPath = null;
        $viewSealedProductsUrlPath = null;

        // ACTION TOYS
        if ($categoryName == "Action Toys") 
        {
            $productTypeAttrCode = 'toys_product_type';
            $mainAttrCode = 'toys_brand';
            $mainSetsAttrCode = 'set_name';
            $mainReleaseAttrCode = 'toys_release';

            $viewSinglesUrlPath = 'actiontoyssingles';
            $viewSetsUrlPath = 'actiontoyssets';
            
            $mainSinglesAttrCode = 'singles_name';

            $singlesCollection = $this->actionToysSinglesFactory->create()->getCollection();

            $sealedProductsOrdering = ['main_table.sealed_products ASC'];

            // Field to select from sets on singles grid
            $setsFieldToSelectFromSingles = [];
            $setsFieldToSelectFromSealedProducts = ['sets.card_series'];

            $setsTableName = 'marketplace_action_toy_sets';
            $singlesTableName = 'marketplace_action_toy_singles';
            $sealedProductsTableName = 'marketplace_action_toy_sealedproducts';
            $setsFieldToSelet = $this->getSetsAllowedFields($mainAttrCode);
            $setsFieldToSelet = array_filter($setsFieldToSelet, function($item) {
                return $item !== 'images';
            });

            $setsCollection = $this->actionToysSetsFactory->create()->getCollection();
            // $sealedProductsCollection = $this->cardGameSealedProducts->create()->getCollection();
            
        }

        // ACTION TOYS MODEL KITS
        if ($categoryName == "Action Toys Model Kits") 
        {
            $productTypeAttrCode = 'toys_product_type';
            $mainAttrCode = 'toys_brand';
            $mainSetsAttrCode = 'set_name';
            $mainReleaseAttrCode = 'toys_release';

            $viewSinglesUrlPath = 'actiontoysmodelkitssingles';
            $viewSetsUrlPath = 'actiontoysmodelkitssets';
            
            $mainSinglesAttrCode = 'singles_name';

            $singlesCollection = $this->actionToysModelKitsSinglesFactory->create()->getCollection();

            $sealedProductsOrdering = ['main_table.sealed_products ASC'];

            // Field to select from sets on singles grid
            $setsFieldToSelectFromSingles = [];
            $setsFieldToSelectFromSealedProducts = ['sets.card_series'];

            $setsTableName = 'marketplace_actiontoys_modelkits_sets';
            $singlesTableName = 'marketplace_actiontoys_modelkits_singles';
            $setsFieldToSelet = $this->getSetsAllowedFields($mainAttrCode);
            $setsFieldToSelet = array_filter($setsFieldToSelet, function($item) {
                return $item !== 'images';
            });

            $setsCollection = $this->actionToysModelKitsSetsFactory->create()->getCollection();
        }

        // ANIME
        if ($categoryName == "Anime Toys") 
        {
            $productTypeAttrCode = 'toys_product_type';
            $mainAttrCode = 'toys_brand';
            $mainSetsAttrCode = 'set_name';
            
            $mainSinglesAttrCode = 'singles_name';

            $singlesCollection = $this->animeSinglesFactory->create()->getCollection();

            $sealedProductsOrdering = ['main_table.sealed_products ASC'];

            // Field to select from sets on singles grid
            $setsFieldToSelectFromSingles = ['sets.card_series','sets.language_version'];
            $setsFieldToSelectFromSealedProducts = ['sets.card_series'];

            $setsTableName = 'marketplace_anime_sets';
            $singlesTableName = 'marketplace_anime_singles';
            $sealedProductsTableName = 'marketplace_anime_sealedproducts';
            $setsFieldToSelet = $this->getSetsAllowedFields($mainAttrCode);
            $setsFieldToSelet = array_filter($setsFieldToSelet, function($item) {
                return $item !== 'images';
            });

            $setsCollection = $this->animeSetsFactory->create()->getCollection();

            $viewSinglesUrlPath = 'animetoyssingles';
            $viewSetsUrlPath = 'animetoyssets';
            // $sealedProductsCollection = $this->cardGameSealedProducts->create()->getCollection();
            
        }

        // ANIME MODEL KITS
        if ($categoryName == "Anime Model Kits") 
        {
            $productTypeAttrCode = 'toys_product_type';
            $mainAttrCode = 'toys_brand';
            $mainSetsAttrCode = 'set_name';
            $mainReleaseAttrCode = 'toys_release';

            $viewSinglesUrlPath = 'animemodelkitssingles';
            $viewSetsUrlPath = 'animemodelkitssets';
            
            $mainSinglesAttrCode = 'singles_name';

            $singlesCollection = $this->animeModelKitsSinglesFactory->create()->getCollection();

            $sealedProductsOrdering = ['main_table.sealed_products ASC'];

            // Field to select from sets on singles grid
            $setsFieldToSelectFromSingles = [];
            $setsFieldToSelectFromSealedProducts = ['sets.card_series'];

            $setsTableName = 'marketplace_anime_modelkits_sets';
            $singlesTableName = 'marketplace_anime_modelkits_singles';
            $setsFieldToSelet = $this->getSetsAllowedFields($mainAttrCode);
            $setsFieldToSelet = array_filter($setsFieldToSelet, function($item) {
                return $item !== 'images';
            });

            $setsCollection = $this->animeModelKitsSetsFactory->create()->getCollection();
        }

        // GUNDAM
        if ($categoryName == "Gundam") 
        {
            $productTypeAttrCode = 'toys_product_type';
            $mainAttrCode = 'toys_brand';
            $mainSetsAttrCode = 'set_name';
            $mainReleaseAttrCode = 'toys_release';

            $viewSinglesUrlPath = 'gundamsingles';
            $viewSetsUrlPath = 'gundamsets';
            
            $mainSinglesAttrCode = 'singles_name';

            $singlesCollection = $this->gundamSinglesFactory->create()->getCollection();

            $sealedProductsOrdering = ['main_table.sealed_products ASC'];

            // Field to select from sets on singles grid
            $setsFieldToSelectFromSingles = [];
            $setsFieldToSelectFromSealedProducts = ['sets.card_series'];

            $setsTableName = 'marketplace_gundam_sets';
            $singlesTableName = 'marketplace_gundam_singles';
            $setsFieldToSelet = $this->getSetsAllowedFields($mainAttrCode);
            $setsFieldToSelet = array_filter($setsFieldToSelet, function($item) {
                return $item !== 'images';
            });

            $setsCollection = $this->gundamSetsFactory->create()->getCollection();
        }

        // FUN GOODS
        if ($categoryName == "Fun Goods") 
        {
            $productTypeAttrCode = 'toys_product_type';
            $mainAttrCode = 'toys_brand';
            $mainSetsAttrCode = 'set_name';
            $mainReleaseAttrCode = 'toys_release';

            $viewSinglesUrlPath = 'fungoodssingles';
            $viewSetsUrlPath = 'fungoodssets';
            
            $mainSinglesAttrCode = 'singles_name';

            $singlesCollection = $this->fungoodsSinglesFactory->create()->getCollection();

            $sealedProductsOrdering = ['main_table.sealed_products ASC'];

            // Field to select from sets on singles grid
            $setsFieldToSelectFromSingles = [];
            $setsFieldToSelectFromSealedProducts = ['sets.card_series'];

            $setsTableName = 'marketplace_fungoods_sets';
            $singlesTableName = 'marketplace_fungoods_singles';
            $setsFieldToSelet = $this->getSetsAllowedFields($mainAttrCode);
            $setsFieldToSelet = array_filter($setsFieldToSelet, function($item) {
                return $item !== 'images';
            });

            $setsCollection = $this->fungoodsSetsFactory->create()->getCollection();
        }

        // DOLLS
        if ($categoryName == "Dolls") 
        {
            $mainAttrCode = 'toys_brand';
            $mainSetsAttrCode = 'dolls_set_name';
            $mainSinglesAttrCode = 'dolls_singles_name';

            $singlesCollection = $this->dollsSinglesFactory->create()->getCollection();

            $setsTableName = 'marketplace_dolls_sets';
            $singlesTableName = 'marketplace_dolls_singles';
            $setsFieldToSelet = ['sets.set_name','sets.set_item_number'];
            
            $setsCollection = $this->dollsSetsFactory->create()->getCollection();

            $viewSinglesUrlPath = 'dollssingles';
            $viewSetsUrlPath = 'dollssets';

            $mainReleaseAttrCode = 'dolls_release';
        }

        // CARD GAME
        if ($categoryName == "Collectible Card Games") 
        {
            $productTypeAttrCode = 'card_product_type';
            $mainAttrCode = 'card_game';
            $mainSetsAttrCode = 'card_set';
            $mainReleaseAttrCode = 'card_release';
            $mainSetsNumberAttrCode = 'card_set_abbreviation';

            
            $mainSinglesAttrCode = 'card_name';
            $mainSinglesNumberAttrCode = 'card_number';

            $singlesCollection = $this->cardGameSetsCard->create()->getCollection();

            $sealedProductsOrdering = ['main_table.sealed_products ASC'];

            // Field to select from sets on singles grid
            $setsFieldToSelectFromSingles = ['sets.card_series','sets.language_version'];
            $setsFieldToSelectFromSealedProducts = ['sets.card_series'];

            $setsTableName = 'marketplace_card_games_sets';
            $singlesTableName = 'marketplace_card_games_sets_card';
            $sealedProductsTableName = 'marketplace_card_games_sealed_products';
            $setsFieldToSelet = $this->getSetsAllowedFields($mainAttrCode);
            $setsFieldToSelet = array_filter($setsFieldToSelet, function($item) {
                return $item !== 'images';
            });

            $setsCollection = $this->cardGameSets->create()->getCollection();
            $sealedProductsCollection = $this->cardGameSealedProducts->create()->getCollection();

            $viewSinglesUrlPath = 'cardgamesingles';
            $viewSetsUrlPath = 'cardgamesets';
            $viewSealedProductsUrlPath = 'cardgamesealedproducts';
            
        }

        // MINIATURE GAME
        if ($categoryName == "Miniature Games") 
        {
            $mainAttrCode = 'miniature_game';
            $mainSetsAttrCode = 'miniature_set_name';

            
                $mainSinglesAttrCode = 'figure_name';

                $singlesCollection = $this->miniatureGameSingles->create()->getCollection();

                $setsTableName = 'marketplace_miniature_games_sets';
                $singlesTableName = 'marketplace_miniature_games_sets_singles';

                $setsFieldToSelet = ['sets.miniature_set_name','sets.miniature_set_abbreviation'];
            

            
                $setsCollection = $this->miniatureGameSets->create()->getCollection();

                $viewSinglesUrlPath = 'miniaturegamesingles';
            $viewSetsUrlPath = 'miniaturegamesets';
            
        }

        // GAMES WORKSHOP
        if ($categoryName == "Games Workshop") 
        {
            $mainAttrCode = 'gw_miniature_game';
            $mainSetsAttrCode = 'miniature_set';
            
            $mainSinglesAttrCode = 'figure_name';

            $singlesCollection = $this->gamesWorkshopSinglesFactory->create()->getCollection();

            $setsTableName = 'marketplace_gamesworkshop_sets';
            $singlesTableName = 'marketplace_gamesworkshop_singles';
            $setsFieldToSelet = ['sets.miniature_set','sets.miniature_set_abbreviation'];
        
            $setsCollection = $this->gamesWorkshopSetsFactory->create()->getCollection();

            $viewSinglesUrlPath = 'gamesworkshopsingles';
            $viewSetsUrlPath = 'gamesworkshopsets';
            
        }

        // LIVING CARD GAME
        if ($categoryName == "Living Card Games") 
        {
            $mainAttrCode = 'living_card_game';
            $mainSetsAttrCode = 'living_card_set';

            $setsTableName = 'marketplace_living_card_games_sets';
            $singlesTableName = 'marketplace_living_card_games_singles';
            $mainReleaseAttrCode = 'living_card_release';
            $sealedProductsTableName = 'marketplace_living_card_games_sealedproducts';

                $setsCollection = $this->livingCardGameSets->create()->getCollection();
            $viewSinglesUrlPath = 'livingcardgamesingles';
            $viewSetsUrlPath = 'livingcardgamesets';
            $viewSealedProductsUrlPath = 'livingcardgamesealedproducts';
        }

        // SPORT CARDS
        if ($categoryName == "Sport Cards") 
        {
            $mainAttrCode = 'sc_categories';
            $mainSetsAttrCode = 'sc_set_name';
            $mainReleaseAttrCode = 'sc_release';
            $mainSinglesAttrCode = 'sc_card_name';

            $singlesCollection = $this->sportCardsSinglesFactory->create()->getCollection();

            $setsTableName = 'marketplace_sport_cards_sets';
            $singlesTableName = 'marketplace_sport_cards_sets_card';
            $sealedProductsTableName = 'marketplace_sport_cards_sealedproducts';
            $setsFieldToSelet = ['sets.sc_set_name'];
            
            $setsCollection = $this->sportCardsSetsFactory->create()->getCollection();
            $sealedProductsCollection = $this->sportCardsSealedProducts->create()->getCollection();
            $setsOrdering = [
                'main_table.release_year DESC',
                'main_table.sc_set_type ASC',
                'main_table.sc_set_name ASC'
            ];

            // $setsFieldToSelectFromSingles = ['sets.card_series','sets.language_version'];

            $viewSinglesUrlPath = 'sportcardssingles';
            $viewSetsUrlPath = 'sportcardssets';
            $viewSealedProductsUrlPath = 'sportcardssealedproducts';
        }

        // NON SPORT CARDS
        if ($categoryName == "Non Sports Cards") 
        {
            $mainAttrCode = 'nsc_categories';
            $mainSetsAttrCode = 'nsc_set_name';

            $setsCollection = $this->nonSportCardsSets->create()->getCollection();

            $mainReleaseAttrCode = 'nsc_release';
            $mainSinglesAttrCode = 'nsc_card_name';

            $singlesCollection = $this->nonSportCardsSetsCard->create()->getCollection();

            $setsTableName = 'marketplace_non_sport_cards_sets';
            $singlesTableName = 'marketplace_non_sport_cards_sets_card';
            $sealedProductsTableName = 'marketplace_non_sport_cards_sealedproducts';
            $setsFieldToSelet = ['sets.nsc_set_name'];
            
            $sealedProductsCollection = $this->nonSportCardsSealedProducts->create()->getCollection();
            
            $viewSinglesUrlPath = 'nonsportcardssingles';
            $viewSetsUrlPath = 'nonsportcardssets';
            $viewSealedProductsUrlPath = 'nonsportcardssealedproducts';
        }

        // dd($categoryName);

        // LEGO
        if ($categoryName == "Bricks" || $categoryName == "Box Sets & Packs" || $categoryName == "Minifigures") 
        {
            $mainAttrCode = 'bricks_brand';
            $mainSetsAttrCode = 'bricks_set_name';
            $mainSinglesAttrCode = 'lego_minifigures_name';

            $singlesCollection = $this->legoSinglesFactory->create()->getCollection();

            $setsTableName = 'marketplace_lego_sets';
            $singlesTableName = 'marketplace_lego_sets_singles';
            $setsFieldToSelet = ['sets.bricks_set_name','sets.bricks_set_number'];
            
            $setsCollection = $this->legoSets->create()->getCollection();

            $viewSinglesUrlPath = 'brickssingles';
            $viewSetsUrlPath = 'brickssets';
        }

        // DIECAST
        if ($categoryName == "Diecast Vehicles") 
        {
            $mainAttrCode = 'diecast_brand';
            $mainSetsAttrCode = 'set_name';
            $mainSinglesAttrCode = '';

            $singlesCollection = $this->diecastVehiclesSinglesFactory->create()->getCollection();

            $setsTableName = 'marketplace_diecast_vehicles_sets';
            $singlesTableName = 'marketplace_diecast_vehicles_singles';
            $setsFieldToSelet = ['sets.set_name','sets.set_item_number'];
            
            $setsCollection = $this->diecastVehiclesSetsFactory->create()->getCollection();

            $viewSinglesUrlPath = 'diecastvehiclessingles';
            $viewSetsUrlPath = 'diecastvehiclessets';
        }

        if ($categoryName == "Diecast Aircrafts") 
        {
            $mainAttrCode = 'diecast_brand';
            $mainSetsAttrCode = 'set_name';
            $mainSinglesAttrCode = '';

            $singlesCollection = $this->diecastAircraftsSinglesFactory->create()->getCollection();

            $setsTableName = 'marketplace_diecast_aircrafts_sets';
            $singlesTableName = 'marketplace_diecast_aircrafts_singles';
            $setsFieldToSelet = ['sets.set_name','sets.set_item_number'];
            
            $setsCollection = $this->diecastAircraftsSetsFactory->create()->getCollection();

            $viewSinglesUrlPath = 'diecastaircraftssingles';
            $viewSetsUrlPath = 'diecastaircraftssets';
        }

        if ($categoryName == "Diecast Military") 
        {
            $mainAttrCode = 'diecast_brand';
            $mainSetsAttrCode = 'set_name';
            $mainSinglesAttrCode = '';

            $singlesCollection = $this->diecastMilitarySinglesFactory->create()->getCollection();

            $setsTableName = 'marketplace_diecast_military_sets';
            $singlesTableName = 'marketplace_diecast_military_singles';
            $setsFieldToSelet = ['sets.set_name','sets.set_item_number'];
            
            $setsCollection = $this->diecastMilitarySetsFactory->create()->getCollection();

            $viewSinglesUrlPath = 'diecastmilitarysingles';
            $viewSetsUrlPath = 'diecastmilitarysets';
        }

        // MODEL KITS
        if ($categoryName == "Model Kits Vehicles") 
        {
            $mainAttrCode = 'model_kits_brand';
            $mainSetsAttrCode = 'set_name';
            $mainSinglesAttrCode = '';

            $singlesCollection = $this->modelKitsVehiclesSinglesFactory->create()->getCollection();

            $setsTableName = 'marketplace_modelkits_vehicles_sets';
            $singlesTableName = 'marketplace_modelkits_vehicles_singles';
            $setsFieldToSelet = ['sets.set_name','sets.set_item_number'];
            
            $setsCollection = $this->modelKitsVehiclesSetsFactory->create()->getCollection();

            $viewSinglesUrlPath = 'modelkitsvehiclessingles';
            $viewSetsUrlPath = 'modelkitsvehiclessets';
        }

        if ($categoryName == "Model Kits Aircrafts") 
        {
            $mainAttrCode = 'model_kits_brand';
            $mainSetsAttrCode = 'set_name';
            $mainSinglesAttrCode = '';

            $singlesCollection = $this->modelKitsAircraftsSinglesFactory->create()->getCollection();

            $setsTableName = 'marketplace_modelkits_aircrafts_sets';
            $singlesTableName = 'marketplace_modelkits_aircrafts_singles';
            $setsFieldToSelet = ['sets.set_name','sets.set_item_number'];
            
            $setsCollection = $this->modelKitsAircraftsSetsFactory->create()->getCollection();

            $viewSinglesUrlPath = 'modelkitsaircraftssingles';
            $viewSetsUrlPath = 'modelkitsaircraftssets';
        }

        if ($categoryName == "Model Kits Military") 
        {
            $mainAttrCode = 'model_kits_brand';
            $mainSetsAttrCode = 'set_name';
            $mainSinglesAttrCode = '';

            $singlesCollection = $this->modelKitsMilitarySinglesFactory->create()->getCollection();

            $setsTableName = 'marketplace_modelkits_military_sets';
            $singlesTableName = 'marketplace_modelkits_military_singles';
            $setsFieldToSelet = ['sets.set_name','sets.set_item_number'];
            
            $setsCollection = $this->modelKitsMilitarySetsFactory->create()->getCollection();

            $viewSinglesUrlPath = 'modelkitsmilitarysingles';
            $viewSetsUrlPath = 'modelkitsmilitarysets';
        }

        // TRAINS
        // dd($categoryName);
        if ($categoryName == "Trains") 
        {
            $mainAttrCode = 'railway_brand';
            $mainSetsAttrCode = 'railway_set_name';
            $mainSinglesAttrCode = '';

            $singlesCollection = $this->railwaySinglesFactory->create()->getCollection();

            $setsTableName = 'marketplace_railway_sets';
            $singlesTableName = 'marketplace_railway_singles';
            $setsFieldToSelet = ['sets.railway_set_name','sets.railway_catalog_number'];
            
            $setsCollection = $this->railwaySetsFactory->create()->getCollection();

            $viewSinglesUrlPath = 'trainssingles';
            $viewSetsUrlPath = 'trainssets';
        }

        return [
            'mainAttrCode' => $mainAttrCode,
            'mainSetsAttrCode' => $mainSetsAttrCode,
            'mainSinglesAttrCode' => $mainSinglesAttrCode,
            'singlesCollection' => $singlesCollection,
            'setsCollection' => $setsCollection,
            'setsTableName' => $setsTableName,
            'singlesTableName' => $singlesTableName,
            'setsFieldToSelet' => $setsFieldToSelet,
            'mainSinglesNumberAttrCode' => $mainSinglesNumberAttrCode,
            'mainSetsNumberAttrCode' => $mainSetsNumberAttrCode,
            'mainReleaseAttrCode' => $mainReleaseAttrCode,
            'sealedProductsCollection' => $sealedProductsCollection,
            'setsOrdering' => $setsOrdering,
            'productTypeAttrCode' => $productTypeAttrCode,
            'sealedProductsTableName' => $sealedProductsTableName,
            'viewSinglesUrlPath' => $viewSinglesUrlPath,
            'viewSetsUrlPath' => $viewSetsUrlPath,
            'viewSealedProductsUrlPath' => $viewSealedProductsUrlPath,
            'setsFieldToSelectFromSingles' => $setsFieldToSelectFromSingles,
            'setsFieldToSelectFromSealedProducts' => $setsFieldToSelectFromSealedProducts
        ];
    }

    public function getCustomAttributesById($id) 
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->customAttributesFactory = $objectManager->get('\TCGCollective\ProductAttributesLink\Model\CustomAttributesFactory');
        if ($id) {
            $customAttr = $this->customAttributesFactory->create()
            ->getCollection()
            ->addFieldToFilter('entity_id', $id);
            if ($customAttr->getSize()) {
                return $customAttr->getFirstItem();
            }
        }

        return null;
    }

    public function getAdminId() 
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $admin = $objectManager->get('Magento\Backend\Model\Auth\Session')->getUser();
        if ($admin) {
            return $admin->getId();
        }

        return null;
    }

    public function getAttributeSetDataByCategories($rowData, $categoriesName, $customTableMetadata) 
    {
        $data = [];

        $attributeSetId = null;
        $processedAttributeCode = [];

        try {
            if ($categoriesName) {
                if ($customTableMetadata) {
                    $mainAttrCode = $customTableMetadata['mainAttrCode'];

                    $mainAttributeSet = null;

                    // GET ATTRIBUTE SET BY MASTER ATTRIBUTE
                    if ($mainAttrCode) {
                        $mainAttrVal = $rowData[$mainAttrCode] ?? null;
                        if ($mainAttrVal) {
                            if (is_numeric($mainAttrVal)) {
                                $attrOptionId = $mainAttrVal;
                            } else {
                                $attrOptionId = $this->getOptionId($mainAttrCode, $mainAttrVal);
                            }
                            
                            if ($attrOptionId) {
                                // $mapping = $this->getAttributeSetMappingByAttributeOption($mainAttrCode, $attrOptionId);
                                $collection = $this->prdAttrLinkCollFactory->create()
                                ->addFieldToFilter('attribute_code', $mainAttrCode)
                                ->addFieldToFilter('attribute_option_id', $attrOptionId)
                                ->addFieldToFilter('mapping_type', 'attribute_set');

                                // $collection->getSelect()->join(
                                //     ['link_group' => 'marketplace_product_attributes_link_group'],
                                //     'link_group.entity_id = main_table.group_id',
                                //     ['group_name' => 'link_group.name']
                                // );

                                $collection->setOrder('priority', 'ASC');

                                if ($collection->getSize()) {
                                    $mappingModel = $collection->getFirstItem();

                                    $data = [];
                                    $data['attribute_set_id'] = $mappingModel->getTargetAttributeSetId();
                                    $data['mapping_group_id'] = $mappingModel->getGroupId();
                                    $data['mapping_group_name'] = $mappingModel->getGroupName();
                                    $data['mapping_priority'] = $mappingModel->getPriority();
                                    $data['attribute_code'] = $mappingModel->getAttributeCode();

                                    $mainAttributeSet = $data;
                                    $processedAttributeCode[] = $data['attribute_code'];
                                }
                            }
                        }
                    }

                    // CONTINUE GET ATTRIBUTE SET MAPPING BY OTHERS ATTRIBUTE
                    if ($mainAttributeSet) {
                        $currentMapping = [];
                        
                        $attributeSetId = $data['attribute_set_id'] ?? null;
                        $mappingPriority = $data['mapping_priority'] ?? null;
                        $mappingGroupName = $data['mapping_group_name'] ?? null;
                        if ($attributeSetId) {
                            while ($attributeSetId) {
                                if ($currentMapping) {
                                    $mappingGroupName = $currentMapping['mapping_group_name'] ?? $mappingGroupName;
                                    $mappingPriority = $currentMapping['mapping_priority'] ?? $mappingPriority;
                                }

                                $mapping = $this->getMappingDataByAttributeSetId(
                                    $rowData, 
                                    $attributeSetId, 
                                    $mappingGroupName,
                                    $mappingPriority,
                                    $processedAttributeCode
                                );

                                // dd($mapping);

                                if (!$mapping) {
                                    // dd($currentMapping);
                                    break; // Exit the loop if no mapping is found
                                }

                                $currentMapping = $mapping;

                                $data = $mapping;
                                $processedAttributeCode[] = $data['attribute_code'];
                                $attributeSetId = $data['attribute_set_id'] ?? null;
                            }
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            dd($e->getMessage());
        }

        if ($data) {
            $attributeSetId = $data['attribute_set_id'] ?? null;
            if ($attributeSetId) {
                $data['attribute_set_name'] = $this->getAttributeSetNameById($attributeSetId);
            }
        }

        // dd($processedAttributeCode);

        // dd($data);

        return $data;
    }

    public function getMappingDataByAttributeSetId(
        $rowData, 
        $attributeSetId, 
        $selectedGroupName,
        $mappingPriority,
        $processedAttributeCode
    ) {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->attributeSetRepository = $objectManager->create('Magento\Eav\Api\AttributeSetRepositoryInterface');
        $this->attributeGroup = $objectManager->get('Magento\Eav\Model\Entity\Attribute\Group');
        $this->attribute = $objectManager->get('Magento\Eav\Model\Entity\Attribute');

        $data = [];

        $currentMapping = [];

        $vvvv = [];
        
        if ($attributeSetId) {
            // Get attribute set data by id
            $attributeSetRepository = $this->attributeSetRepository->get($attributeSetId);
            if ($attributeSetRepository) {
                $attributeSetName = $attributeSetRepository->getData('attribute_set_name');
                // dd($attributeSetName);
                $attributeGroupCollection = $this->attributeGroup->getCollection();
                $attributeGroupCollection->addFieldToFilter('attribute_group_name', 'Category Details');
                $attributeGroupCollection->addFieldToFilter('attribute_set_id', $attributeSetId);
                if ($attributeGroupCollection->getSize()) {
                    $attributeGroupId = $attributeGroupCollection->getFirstItem()->getId();
                    if ($attributeGroupId) {
                        $attributeCollection = $this->attribute->getCollection();
                        $attributeCollection->setAttributeSetFilter($attributeSetId);
                        $attributeCollection->setAttributeGroupFilter($attributeGroupId);
                        if ($attributeCollection->getSize() > 0) {
                            foreach ($attributeCollection as $attribute) {
                                $attributeCode = $attribute->getAttributeCode();
                                $vvvv[] = $attributeCode;
                                // if ($attributeCode == 'category_ids' || $attributeCode == 'sealed_products_type' || $attributeCode == 'mtg_sealed_products') {
                                //     continue;
                                // }

                                if ($attributeCode == 'category_ids') {
                                    continue;
                                }

                                if (in_array($attributeCode, $processedAttributeCode)) {
                                    continue;
                                }

                                // dd($rowData);

                                $attributeVal = $rowData[$attributeCode] ?? null;
                                if ($attributeVal) {
                                    $attrOptionId = $this->getOptionId($attributeCode, $attributeVal);
                                    if ($attrOptionId) {
                                        $mapping = $this->getAttributeSetMappingByAttributeOption(
                                            $attributeCode, 
                                            $attrOptionId,
                                            $selectedGroupName,
                                            $mappingPriority
                                        );
                                        
                                        // $mapping = $this->getAttributeSetData($attributeCode, $mappingPriority, $attrOptionId, $mappingGroupId);

                                        // $mapping = $this->getAttributeSetData($attributeCode, $mappingPriority, $attrOptionId, null, null, null, $selectedGroupName);

                                        // $attributeCode, $mappingPriority, $selectedOptionId, $selectedGroupId, $selectedCategoryId = null, $currentAttributeSet = null, $selectedGroupName = null, $selectedAttributeCode = null) 

                                        // dd($selectedGroupName);

                                        // dd($mapping);

                                        if ($mapping && $mapping['attribute_code']) {
                                            // dd($mapping);
                                            $data = $mapping;
                                            break;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        return $data;
    }
    

    public function getAttributeSetMappingByAttributeOption(
        $attributeCode, 
        $selectedOptionId,
        $selectedGroupName = null,
        $mappingPriority = null
    ) {
        $data = [];

        // GET MAPPING BY ATTRIBUTE OPTION AND ATTRIBUTE CODE
        if ($selectedOptionId && $attributeCode) 
        {
            // Get mapping with same group and > priority
            if ($selectedGroupName) {
                $collection = $this->prdAttrLinkCollFactory->create()
                ->addFieldToFilter('attribute_code', $attributeCode)
                ->addFieldToFilter('attribute_option_id', $selectedOptionId)
                ->addFieldToFilter('mapping_type', 'attribute_set');

                $collection->setOrder('priority', 'ASC');

                // $collection->getSelect()->join(
                //     ['link_group' => 'marketplace_product_attributes_link_group'],
                //     'link_group.entity_id = main_table.group_id',
                //     ['group_name' => 'link_group.name']
                // );

                // Get current group name
                // $connection = $collection->getSelect()->getConnection();
                // $subquery = $connection->select()
                //     ->from('marketplace_product_attributes_link_group', 'name')
                //     ->where('entity_id = ?', $selectedGroupId);

                // $currentGroupName = $connection->fetchOne($subquery);

                // Remove last item from group name
                $splitedCurrentGroupName = $selectedGroupName;

                $lastPipePosition = strrpos($selectedGroupName, '|');
                if ($lastPipePosition !== false) {
                    $splitedCurrentGroupName = trim(substr($selectedGroupName, 0, $lastPipePosition));
                }

                if ($collection->getSize()) 
                {
                    // if ($attributeCode == "card_product_type") {
                    // die('<pre>'.print_r($collection->getData(),true));
                    // }

                    // GET SAME GROUP ID
                    foreach($collection as $mapping) {
                        if ($mapping->getGroupName() == $selectedGroupName) {
                            $data['attribute_set_id'] = $mapping->getTargetAttributeSetId();
                            $data['mapping_group_id'] = $mapping->getGroupId();
                            $data['mapping_priority'] = $mapping->getPriority();
                            $data['attribute_code'] = $mapping->getAttributeCode();

                            return $data;
                        }
                    }

                    // GET CONTAINS GROUP NAME
                    foreach($collection as $mapping) {
                        if ($mapping->getGroupName() 
                            && $selectedGroupName 
                            && str_contains($mapping->getGroupName(), $selectedGroupName)
                        ) {
                            $data['attribute_set_id'] = $mapping->getTargetAttributeSetId();
                            $data['mapping_group_id'] = $mapping->getGroupId();
                            $data['mapping_priority'] = $mapping->getPriority();
                            $data['attribute_code'] = $mapping->getAttributeCode();

                            return $data;
                        }
                    }

                    // 
                    foreach($collection as $mapping) {
                        if (str_contains($mapping->getGroupName(), $splitedCurrentGroupName)) {
                            $data['attribute_set_id'] = $mapping->getTargetAttributeSetId();
                            $data['mapping_group_id'] = $mapping->getGroupId();
                            $data['mapping_priority'] = $mapping->getPriority();
                            $data['attribute_code'] = $mapping->getAttributeCode();

                            return $data;
                        }
                    }
                }
            }

            // Get current group name
            if ($selectedGroupName) {

                // Remove last item from group name
                $splitedCurrentGroupName = null;

                $lastPipePosition = strrpos($selectedGroupName, '|');
                if ($lastPipePosition !== false) {
                    $splitedCurrentGroupName = trim(substr($selectedGroupName, 0, $lastPipePosition));
                }

                if ($collection->getSize()) {
                    foreach($collection as $mapping) {
                        // die('<pre>'.print_r($collection->getData(),true));
                        if ($mapping->getGroupName() == $selectedGroupName) {
                            $data['attribute_set_id'] = $mapping->getTargetAttributeSetId();
                            $data['mapping_group_id'] = $mapping->getGroupId();
                            $data['mapping_priority'] = $mapping->getPriority();
                            $data['attribute_code'] = $mapping->getAttributeCode();

                            return $data;
                        }

                        if ($mapping->getGroupName() == $splitedCurrentGroupName) {
                            $data['attribute_set_id'] = $mapping->getTargetAttributeSetId();
                            $data['mapping_group_id'] = $mapping->getGroupId();
                            $data['mapping_priority'] = $mapping->getPriority();
                            $data['attribute_code'] = $mapping->getAttributeCode();

                            return $data;
                        }

                        if (str_contains($mapping->getGroupName(), $selectedGroupName)) {
                            $data['attribute_set_id'] = $mapping->getTargetAttributeSetId();
                            $data['mapping_group_id'] = $mapping->getGroupId();
                            $data['mapping_priority'] = $mapping->getPriority();
                            $data['attribute_code'] = $mapping->getAttributeCode();

                            return $data;
                        }
                    }
                }
            }

                        

            $collection = $this->prdAttrLinkCollFactory->create()
            // ->addFieldToFilter('attribute_code', $attributeCode)
            // ->addFieldToFilter('attribute_option_id', $selectedOptionId)
            ->addFieldToFilter('mapping_type', 'attribute_set');

            $collection->setOrder('priority', 'ASC');

            // $collection->getSelect()->join(
            //     ['link_group' => 'marketplace_product_attributes_link_group'],
            //     'link_group.entity_id = main_table.group_id',
            //     ['group_name' => 'link_group.name']
            // );

            if ($collection->getSize()) 
            {
                $lastPipePosition = strrpos($selectedGroupName, '|');

                            if ($lastPipePosition !== false) {
                                // Trim the string to remove the last '| Secret Lair'
                                $selectedGroupName = trim(substr($selectedGroupName, 0, $lastPipePosition));
                            }

                // Handle if more than 1 mapping
                if ($collection->getSize() > 1) {
                    if ($mappingPriority > 0) {
                        foreach($collection as $mapping) {
                            $lastPipePosition = strrpos($selectedGroupName, '|');

                            if ($lastPipePosition !== false) {
                                // Trim the string to remove the last '| Secret Lair'
                                $selectedGroupName = trim(substr($selectedGroupName, 0, $lastPipePosition));
                            }
                            
                            if ($selectedGroupName && $mapping->getGroupName() == $selectedGroupName) {
                                $data['attribute_set_id'] = $mapping->getTargetAttributeSetId();
                                $data['mapping_group_id'] = $mapping->getGroupId();
                                $data['mapping_priority'] = $mapping->getPriority();
                                $data['attribute_code'] = $mapping->getAttributeCode();

                                return $data;
                            }
                        }
                    }
                }

                $mapping = $collection->getFirstItem();
                if ($mapping) {
                    if ($mapping->getPriority() >= 3) {
                        // if ($groupName && str_contains($mapping->getGroupName(), $groupName)) {
                        //     $data['attribute_set_id'] = $mapping->getTargetAttributeSetId();
                        //     $data['mapping_group_id'] = $mapping->getGroupId();
                        //     $data['mapping_priority'] = $mapping->getPriority();

                        //     return $data;
                        // }
                    }

                    if ($mapping->getPriority() < 3) {
                        $data['attribute_set_id'] = $mapping->getTargetAttributeSetId();
                        $data['mapping_group_id'] = $mapping->getGroupId();
                        $data['mapping_priority'] = $mapping->getPriority();
                        $data['attribute_code'] = $mapping->getAttributeCode();

                        return $data;
                    }
                }
            }

            // GET LOWER PRIORITY MAPPING ON SAME GROUP
            if ($selectedGroupName) {
                $collection = $this->prdAttrLinkCollFactory->create()
                ->addFieldToFilter('group_name', ['like' => $selectedGroupName.'%'])
                ->addFieldToFilter('attribute_code', ['neq' => $attributeCode])
                ->addFieldToFilter('priority', ['lteq' => $mappingPriority])
                ->addFieldToFilter('mapping_type', 'attribute_set')
                ->setOrder('priority', 'ASC');
                
                if ($collection->getSize()) {
                    $mapping = $collection->getFirstItem();
                    if ($mapping) {
                        $data['attribute_set_id'] = $mapping->getTargetAttributeSetId();
                        $data['mapping_group_id'] = $mapping->getGroupId();
                        $data['mapping_priority'] = $mapping->getPriority();
                        $data['attribute_code'] = $mapping->getAttributeCode();

                        return $data;
                    }
                }
            }
        }

        return $data;
    }

    public function getAttributeSetNameById($attributeSetId) 
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->attributeSetRepository = $objectManager->create('Magento\Eav\Api\AttributeSetRepositoryInterface');
        
        $attributeSetName = null;

        if ($attributeSetId) {
            // dd($attributeSetId);
            $attributeSetRepository = $this->attributeSetRepository->get($attributeSetId);
            if ($attributeSetRepository) {
                $attributeSetName = $attributeSetRepository->getData('attribute_set_name');
            }
        }

        return $attributeSetName;
    }

    public function getNextMappingPriority($attributeCode, $selectedOptionId, $mappingPriority, $source = null) 
    {
        $collection = $this->prdAttrLinkCollFactory->create()
        ->addFieldToFilter('attribute_code', $attributeCode)
        ->addFieldToFilter('mapping_type', 'attribute_set');

        if ($mappingPriority == 0 && $source != "database") {
            $genericAttrValLabelArr = ['Brands','Card Game','Games'];

            $conditions = [];

            foreach ($genericAttrValLabelArr as $label) {
                $conditions[] = ['like' => '% | ' . $label];
            }

            $collection->addFieldToFilter('group_name', $conditions);
        } else {
            $collection->addFieldToFilter('attribute_option_id', $selectedOptionId);
        }

        if ($collection->getSize()) {
            $mapping = $collection->getFirstItem();
            if ($mapping) {
                $this->nextMappingPriority = $mapping->getPriority();
            }
        }
    }

    public function handleBackToPreviousMapping(
        $attributeCode, 
        $mappingPriority, 
        $selectedOptionId, 
        $selectedGroupId, 
        $selectedCategoryId, 
        $currentAttributeSet, 
        $selectedGroupName, 
        $selectedAttributeCode
    ) {
        if ($this->nextMappingPriority && $this->nextMappingPriority < $mappingPriority) {

            // GET MAPPING BY GROUP
            if ($selectedGroupName) {

                // Get previous group name
                $previousGroupName = $selectedGroupName;
                $lastPipePosition = strrpos($selectedGroupName, '|');
                if ($lastPipePosition !== false) {
                    $previousGroupName = trim(substr($selectedGroupName, 0, $lastPipePosition));
                }

                // dd($previousGroupName);

                $collection = $this->prdAttrLinkCollFactory->create()
                ->addFieldToFilter('group_name', ['like' => $previousGroupName.'%'])
                ->addFieldToFilter('attribute_code', $attributeCode)
                ->addFieldToFilter('attribute_option_id', $selectedOptionId)
                ->addFieldToFilter('mapping_type', 'attribute_set');

                if ($collection->getSize()) 
                {
                    $mapping = $collection->getFirstItem();
                    if ($mapping) {
                        $data['attribute_set_id'] = $mapping->getTargetAttributeSetId();
                        $data['mapping_group_id'] = null;
                        $data['mapping_attribute_code'] = $mapping->getData('attribute_code');
                        $data['mapping_group_name'] = $mapping->getGroupName();
                        $data['mapping_priority'] = $mapping->getPriority();
                        $data['attribute_code'] = $mapping->getAttributeCode();

                        return $data;
                    }
                }
                
                $lastPipePosition = strrpos($previousGroupName, '|');
                if ($lastPipePosition !== false) {
                    $previousGroupName = trim(substr($previousGroupName, 0, $lastPipePosition));
                }

                // dd($previousGroupName);

                $collection = $this->prdAttrLinkCollFactory->create()
                ->addFieldToFilter('group_name', ['like' => $previousGroupName.'%'])
                ->addFieldToFilter('attribute_code', $attributeCode)
                ->addFieldToFilter('attribute_option_id', $selectedOptionId)
                ->addFieldToFilter('mapping_type', 'attribute_set');

                if ($collection->getSize()) 
                {
                    $mapping = $collection->getFirstItem();
                    if ($mapping) {
                        $data['attribute_set_id'] = $mapping->getTargetAttributeSetId();
                        $data['mapping_group_id'] = null;
                        $data['mapping_attribute_code'] = $mapping->getData('attribute_code');
                        $data['mapping_group_name'] = $mapping->getGroupName();
                        $data['mapping_priority'] = $mapping->getPriority();
                        $data['attribute_code'] = $mapping->getAttributeCode();

                        return $data;
                    }
                }

                if ($this->nextMappingPriority == 1) {
                    $lastPipePosition = strrpos($previousGroupName, '|');
                    if ($lastPipePosition !== false) {
                        $previousGroupName = trim(substr($previousGroupName, 0, $lastPipePosition));
                    }

                    // dd($this->nextMappingPriority);

                    $collection = $this->prdAttrLinkCollFactory->create()
                    ->addFieldToFilter('group_name', ['like' => $previousGroupName.'%'])
                    ->addFieldToFilter('attribute_code', $attributeCode)
                    ->addFieldToFilter('attribute_option_id', $selectedOptionId)
                    ->addFieldToFilter('mapping_type', 'attribute_set');

                    if ($collection->getSize()) 
                    {
                        $mapping = $collection->getFirstItem();
                        if ($mapping) {
                            $data['attribute_set_id'] = $mapping->getTargetAttributeSetId();
                            $data['mapping_group_id'] = null;
                            $data['mapping_attribute_code'] = $mapping->getData('attribute_code');
                            $data['mapping_group_name'] = $mapping->getGroupName();
                            $data['mapping_priority'] = $mapping->getPriority();
                            $data['attribute_code'] = $mapping->getAttributeCode();

                            return $data;
                        }
                    }

                    $lastPipePosition = strrpos($previousGroupName, '|');
                    if ($lastPipePosition !== false) {
                        $previousGroupName = trim(substr($previousGroupName, 0, $lastPipePosition));
                    }

                    // dd($previousGroupName);

                    $collection = $this->prdAttrLinkCollFactory->create()
                    ->addFieldToFilter('group_name', ['like' => $previousGroupName.'%'])
                    ->addFieldToFilter('attribute_code', $attributeCode)
                    ->addFieldToFilter('attribute_option_id', $selectedOptionId)
                    ->addFieldToFilter('mapping_type', 'attribute_set');

                    if ($collection->getSize()) 
                    {
                        $mapping = $collection->getFirstItem();
                        if ($mapping) {
                            $data['attribute_set_id'] = $mapping->getTargetAttributeSetId();
                            $data['mapping_group_id'] = null;
                            $data['mapping_attribute_code'] = $mapping->getData('attribute_code');
                            $data['mapping_group_name'] = $mapping->getGroupName();
                            $data['mapping_priority'] = $mapping->getPriority();
                            $data['attribute_code'] = $mapping->getAttributeCode();

                            return $data;
                        }
                    }
                }

                // Handle mapping not exist and not selected yet
                if ($selectedAttributeCode != $attributeCode) {

                    // Get previous group name
                    $previousGroupName = $selectedGroupName;
                    $lastPipePosition = strrpos($selectedGroupName, '|');
                    if ($lastPipePosition !== false) {
                        $previousGroupName = trim(substr($selectedGroupName, 0, $lastPipePosition));
                    }

                    $collection = $this->prdAttrLinkCollFactory->create()
                    ->addFieldToFilter('group_name', ['eq' => $previousGroupName])
                    ->addFieldToFilter('priority', ['lt' => $this->nextMappingPriority])
                    ->addFieldToFilter('mapping_type', 'attribute_set');

                    // dd($collection->getSelect()->__toString());

                    if ($collection->getSize()) 
                    {
                        $mapping = $collection->getFirstItem();
                        if ($mapping) {
                            $data['attribute_set_id'] = $mapping->getTargetAttributeSetId();
                            $data['mapping_group_id'] = null;
                            $data['mapping_attribute_code'] = $mapping->getData('attribute_code');
                            $data['mapping_group_name'] = $mapping->getGroupName();
                            $data['mapping_priority'] = $mapping->getPriority();
                            $data['attribute_code'] = $mapping->getAttributeCode();

                            return $data;
                        }
                    }

                    // Get previous group name
                    $lastPipePosition = strrpos($previousGroupName, '|');
                    if ($lastPipePosition !== false) {
                        $previousGroupName = trim(substr($previousGroupName, 0, $lastPipePosition));
                    }

                    $collection = $this->prdAttrLinkCollFactory->create()
                    ->addFieldToFilter('group_name', ['eq' => $previousGroupName])
                    ->addFieldToFilter('priority', ['lt' => $this->nextMappingPriority])
                    ->addFieldToFilter('mapping_type', 'attribute_set');

                    // dd($collection->getSelect()->__toString());

                    if ($collection->getSize()) 
                    {
                        $mapping = $collection->getFirstItem();
                        if ($mapping) {
                            $data['attribute_set_id'] = $mapping->getTargetAttributeSetId();
                            $data['mapping_group_id'] = null;
                            $data['mapping_attribute_code'] = $mapping->getData('attribute_code');
                            $data['mapping_group_name'] = $mapping->getGroupName();
                            $data['mapping_priority'] = $mapping->getPriority();
                            $data['attribute_code'] = $mapping->getAttributeCode();

                            return $data;
                        }
                    }

                    // Get previous group name
                    $lastPipePosition = strrpos($previousGroupName, '|');
                    if ($lastPipePosition !== false) {
                        $previousGroupName = trim(substr($previousGroupName, 0, $lastPipePosition));
                    }

                    $collection = $this->prdAttrLinkCollFactory->create()
                    ->addFieldToFilter('group_name', ['eq' => $previousGroupName])
                    ->addFieldToFilter('priority', ['lt' => $this->nextMappingPriority])
                    ->addFieldToFilter('mapping_type', 'attribute_set');

                    // dd($collection->getSelect()->__toString());

                    if ($collection->getSize()) 
                    {
                        $mapping = $collection->getFirstItem();
                        if ($mapping) {
                            $data['attribute_set_id'] = $mapping->getTargetAttributeSetId();
                            $data['mapping_group_id'] = null;
                            $data['mapping_attribute_code'] = $mapping->getData('attribute_code');
                            $data['mapping_group_name'] = $mapping->getGroupName();
                            $data['mapping_priority'] = $mapping->getPriority();
                            $data['attribute_code'] = $mapping->getAttributeCode();

                            return $data;
                        }
                    }
                }

                // Handle mapping not exist and already selected before
                if ($selectedAttributeCode == $attributeCode) {

                    // Get previous group name
                    $previousGroupName = $selectedGroupName;
                    $lastPipePosition = strrpos($selectedGroupName, '|');
                    if ($lastPipePosition !== false) {
                        $previousGroupName = trim(substr($selectedGroupName, 0, $lastPipePosition));
                    }

                    $collection = $this->prdAttrLinkCollFactory->create()
                    ->addFieldToFilter('group_name', ['eq' => $previousGroupName])
                    ->addFieldToFilter('mapping_type', 'attribute_set');

                    if ($collection->getSize()) 
                    {
                        $mapping = $collection->getFirstItem();
                        if ($mapping) {
                            $data['attribute_set_id'] = $mapping->getTargetAttributeSetId();
                            $data['mapping_group_id'] = null;
                            $data['mapping_attribute_code'] = $mapping->getData('attribute_code');
                            $data['mapping_group_name'] = $mapping->getGroupName();
                            $data['mapping_priority'] = $mapping->getPriority();
                            $data['attribute_code'] = $mapping->getAttributeCode();

                            return $data;
                        }
                    }
                }
            }
        }
    }

    public function checkAttributeSetMappingExist($attributeCode, $groupName, $mappingPriority) 
    {
        $collection = $this->prdAttrLinkCollFactory->create()
        ->addFieldToFilter('attribute_code', $attributeCode)
        // ->addFieldToFilter('priority', ['gt' => $mappingPriority])
        ->addFieldToFilter('mapping_type', 'attribute_set');

        $groupNameArr = explode(" | ", $groupName);
        $groupNameWithMainEntity = $groupNameArr[0] . ' | ' . $groupNameArr[1];

        $collection->addFieldToFilter('group_name', [
            ['like' => $groupNameWithMainEntity . '%'],
            // ['like' => $groupName . '%'],
        ]);

        if ($collection->getSize()) {
            return $collection;
        }

        return false;
    }

    public function handleNextOrSamePriorityMapping(
        $attributeCode, 
        $mappingPriority, 
        $selectedOptionId, 
        $selectedGroupId, 
        $selectedCategoryId, 
        $currentAttributeSet, 
        $selectedGroupName, 
        $selectedAttributeCode,
        $source
    ) {
        if (!$this->nextMappingPriority || $this->nextMappingPriority && $this->nextMappingPriority >= $mappingPriority) {
            // GET MAPPING BY GROUP
            if ($selectedGroupName) {

                // dd($selectedGroupName);

                // Get mapping with same group prefix
                $collection = $this->prdAttrLinkCollFactory->create()
                ->addFieldToFilter('attribute_code', $attributeCode)
                ->addFieldToFilter('mapping_type', 'attribute_set');

                if ($source != "database" && $mappingPriority == 0) {
                    $genericAttrValLabelArr = ['Brands','Card Game','Games'];

                    $conditions = [];

                    foreach ($genericAttrValLabelArr as $label) {
                        $conditions[] = ['like' => $selectedGroupName . ' | ' . $label . '%'];
                    }

                    $collection->addFieldToFilter('group_name', $conditions);
                } else {
                    $collection->addFieldToFilter('attribute_option_id', $selectedOptionId)
                    ->addFieldToFilter('group_name', ['like' => $selectedGroupName.'%']);
                }

                // dd($collection->getSelect()->__toString());

                if ($collection->getSize()) 
                {
                    $mapping = $collection->getFirstItem();
                    // dd($mapping);
                    if ($mapping) {
                        $data['attribute_set_id'] = $mapping->getTargetAttributeSetId();
                        $data['mapping_group_id'] = null;
                        $data['mapping_group_name'] = $mapping->getGroupName();
                        $data['mapping_attribute_code'] = $mapping->getData('attribute_code');
                        $data['mapping_priority'] = $mapping->getPriority();
                        $data['attribute_code'] = $mapping->getAttributeCode();

                        return $data;
                    }
                }

                // dd($this->nextMappingPriority);

                if ($mappingPriority == $this->nextMappingPriority) {
                    if ($mappingPriority == 0) {
                        // GET GENERIC ATTRIBUTE SET MAPPING
                        if ($attributeCode) {
                            $collection = $this->prdAttrLinkCollFactory->create()
                            ->addFieldToFilter('attribute_code', $attributeCode)
                            ->addFieldToFilter('group_name', ['like' => $selectedGroupName.'%'])
                            ->addFieldToFilter('attribute_option_id', 0)
                            ->addFieldToFilter('mapping_type', 'attribute_set');

                            // dd($collection->getSelect()->__toString());

                            if ($collection->getSize()) 
                            {
                                $mapping = $collection->getFirstItem();
                                // dd($mapping);
                                if ($mapping) {
                                    $data['attribute_set_id'] = $mapping->getTargetAttributeSetId();
                                    $data['mapping_group_id'] = null;
                                    $data['mapping_group_name'] = $mapping->getGroupName();
                                    $data['mapping_attribute_code'] = $mapping->getData('attribute_code');
                                    $data['mapping_priority'] = $mapping->getPriority();
                                    $data['attribute_code'] = $mapping->getAttributeCode();

                                    return $data;
                                }
                            }
                        }
                    }

                    // Get previous group name
                    $previousGroupName = $selectedGroupName;
                    $lastPipePosition = strrpos($selectedGroupName, '|');
                    if ($lastPipePosition !== false) {
                        $previousGroupName = trim(substr($selectedGroupName, 0, $lastPipePosition));
                    }

                    // dd($previousGroupName);

                    // dd('ok');

                    // Get mapping with previous group prefix
                    $collection = $this->prdAttrLinkCollFactory->create()
                    ->addFieldToFilter('attribute_code', $attributeCode)
                    // ->addFieldToFilter('priority', ['gt' => $mappingPriority])
                    ->addFieldToFilter('group_name', ['like' => $previousGroupName.'%'])
                    ->addFieldToFilter('attribute_option_id', $selectedOptionId)
                    ->addFieldToFilter('mapping_type', 'attribute_set');

                    if ($collection->getSize()) 
                    {
                        $mapping = $collection->getFirstItem();
                        if ($mapping) {
                            $data['attribute_set_id'] = $mapping->getTargetAttributeSetId();
                            $data['mapping_group_id'] = null;
                            $data['mapping_group_name'] = $mapping->getGroupName();
                            $data['mapping_attribute_code'] = $mapping->getData('attribute_code');
                            $data['mapping_priority'] = $mapping->getPriority();
                            $data['attribute_code'] = $mapping->getAttributeCode();

                            return $data;
                        }
                    }
                }

                // Handle mapping not exist and not selected yet
                if ($selectedAttributeCode != $attributeCode) {
                    $collection = $this->prdAttrLinkCollFactory->create()
                    ->addFieldToFilter('group_name', ['eq' => $selectedGroupName])
                    ->addFieldToFilter('mapping_type', 'attribute_set');

                    if ($collection->getSize()) 
                    {
                        $mapping = $collection->getFirstItem();
                        if ($mapping) {
                            $data['attribute_set_id'] = $mapping->getTargetAttributeSetId();
                            $data['mapping_group_id'] = null;
                            $data['mapping_attribute_code'] = $mapping->getData('attribute_code');
                            $data['mapping_group_name'] = $mapping->getGroupName();
                            $data['mapping_priority'] = $mapping->getPriority();
                            $data['attribute_code'] = $mapping->getAttributeCode();

                            return $data;
                        }
                    }
                }

                // Handle mapping not exist and already selected before
                if ($selectedAttributeCode == $attributeCode) {

                    // Get previous group name
                    $previousGroupName = $selectedGroupName;
                    $lastPipePosition = strrpos($selectedGroupName, '|');
                    if ($lastPipePosition !== false) {
                        $previousGroupName = trim(substr($selectedGroupName, 0, $lastPipePosition));
                    }

                    $collection = $this->prdAttrLinkCollFactory->create()
                    ->addFieldToFilter('group_name', ['eq' => $previousGroupName])
                    ->addFieldToFilter('mapping_type', 'attribute_set');

                    if ($collection->getSize()) 
                    {
                        $mapping = $collection->getFirstItem();
                        if ($mapping) {
                            $data['attribute_set_id'] = $mapping->getTargetAttributeSetId();
                            $data['mapping_group_id'] = null;
                            $data['mapping_attribute_code'] = $mapping->getData('attribute_code');
                            $data['mapping_group_name'] = $mapping->getGroupName();
                            $data['mapping_priority'] = $mapping->getPriority();
                            $data['attribute_code'] = $mapping->getAttributeCode();

                            return $data;
                        }
                    }
                }
            }
        }
    }

    public function getAttributeSetData(
        $attributeCode, 
        $mappingPriority, 
        $selectedOptionId, 
        $selectedGroupId, 
        $selectedCategoryId = null, 
        $currentAttributeSet = null, 
        $selectedGroupName = null, 
        $selectedAttributeCode = null
    ) {
        $data = [];

        try {
            // GET MAPPING BY ATTRIBUTE OPTION AND ATTRIBUTE CODE
            if ($attributeCode) {

                $source = $this->request->getParam('source');

                // Get next mapping priority
                $this->getNextMappingPriority($attributeCode, $selectedOptionId, $mappingPriority, $source);
                
                // $collection = $this->prdAttrLinkCollFactory->create()
                // ->addFieldToFilter('attribute_code', ['eq' => $attributeCode])
                // ->addFieldToFilter('mapping_type', 'attribute_set');

                // if ($collection->getSize()) 
                // {
                //     $mapping = $collection->getFirstItem();
                //     if ($mapping) {
                //         $this->nextMappingPriority = $mapping->getPriority();
                //     }
                // }
                

                // BACK TO PREVIOUS MAPPING
                $mapping = $this->handleBackToPreviousMapping(
                    $attributeCode, 
                    $mappingPriority, 
                    $selectedOptionId, 
                    $selectedGroupId, 
                    $selectedCategoryId, 
                    $currentAttributeSet, 
                    $selectedGroupName, 
                    $selectedAttributeCode
                );

                if ($mapping) {
                    return $mapping;
                }
                
                // dd($mappingPriority);
                // if ($mappingPriority != 0) {
                    // NEXT / SAME PRIORITY MAPPING
                    $mapping = $this->handleNextOrSamePriorityMapping(
                        $attributeCode, 
                        $mappingPriority, 
                        $selectedOptionId, 
                        $selectedGroupId, 
                        $selectedCategoryId, 
                        $currentAttributeSet, 
                        $selectedGroupName, 
                        $selectedAttributeCode,
                        $source
                    );
                    
                    if ($mapping) {
                        return $mapping;
                    }
                // }
            }

            // dd($attributeCode);

            

            // GET MAPPING BY CATEGORY ID
            if ($selectedCategoryId) {
                $collection = $this->prdAttrLinkCollFactory->create()
                ->addFieldToFilter('category_id', $selectedCategoryId)
                ->addFieldToFilter('attribute_code', [
                    ['null' => true],
                    ['eq' => '']
                ])
                ->addFieldToFilter('mapping_type', 'attribute_set');
                if ($collection->getSize()) {
                    $mapping = $collection->getFirstItem();
                    if ($mapping) {
                        $data['attribute_set_id'] = $mapping->getTargetAttributeSetId();
                        $data['mapping_group_id'] = null;
                        $data['mapping_attribute_code'] = $mapping->getData('attribute_code');
                        $data['mapping_group_name'] = $mapping->getGroupName();
                        $data['mapping_priority'] = $mapping->getPriority();
                        $data['attribute_code'] = $mapping->getAttributeCode();

                        if ($attributeCode && $attributeCode == "card_game" && $selectedOptionId) {
                            $data['attribute_set_id'] = 128;
                        }

                        if ($attributeCode && $attributeCode == "card_product_type" && $selectedOptionId) {
                            $productType = $this->getOptionLabelById($attributeCode, $selectedOptionId);
                            if ($productType == "Singles") {
                                $data['attribute_set_id'] = 129;
                            } elseif ($productType == "Sealed Products") {
                                $data['attribute_set_id'] = 130;
                            } else {
                                $data['attribute_set_id'] = 128;
                            }
                        }

                        return $data;
                    }
                }
            }
        } catch (\Exception $e) {
            dd($e->getMessage());
        }

        return $data;
    }

    public function getHigherPriorityAttributeSetMapping($mappingData) 
    {
        if (!$mappingData) {
            return null;
        }
        // $data['attribute_set_id'] = $mapping->getTargetAttributeSetId();
        // $data['mapping_group_id'] = null;
        // $data['mapping_attribute_code'] = $mapping->getData('attribute_code');
        // $data['mapping_group_name'] = $mapping->getGroupName();
        // $data['mapping_priority'] = $mapping->getPriority();
        // $data['attribute_code'] = $mapping->getAttributeCode();
        // $attributeCode = $mappingData['attribute_code'] ?? null;
        $selectedGroupName = $mappingData['mapping_group_name'] ?? null;
        $priority = $mappingData['mapping_priority'] ?? null;
        // $selectedOptionId = $mappingData['selectedGroupName'] ?? null;

        $collection = $this->prdAttrLinkCollFactory->create()
        ->addFieldToFilter('group_name', ['like' => $selectedGroupName.'%'])
        ->addFieldToFilter('priority', ['gt' => $priority])
        ->addFieldToFilter('mapping_type', 'attribute_set');

        // dd($collection->getSelect()->__toString());

        if ($collection->getSize()) 
        {
           return true;
        }

        return false;
    }

    public function getCustomTableDetails($productTypeCode, $productTypeVal, $entityCode, $entityVal, $productType = null) 
    {
        $result = [
            'is_custom_table_exist' => false,
            'is_singles' => false,
            'is_sealedproducts' => false,
            'selected_attribute_set' => '',
            'sets_selection_provider' => '',
            'is_database' => true,
            'is_next_step' => false
        ];

        // if ($productType == "simple") {
            if ($productTypeCode && $productTypeVal) {
                
                // LIVING CARD GAME
                if (str_contains($productTypeCode, "living_card_product_type")) {
                    $valLabel = $this->getOptionLabelById($productTypeCode, $productTypeVal);
                    if ($valLabel) {
                        if ($valLabel == "Sealed Products" || $valLabel == "Pre Orders" || $valLabel == "Supplies") {
                            $result['is_sealedproducts'] = true;
                            $result['is_custom_table_exist'] = $this->checkCustomTableExist($entityCode, $entityVal, false, true);
                        }
                    }

                    $result['sets_selection_scope'] = 'livingcardgame_sets';
                }

                // MINIATURE GAME
                if (str_contains($productTypeCode, "miniature_product_type")) {
                    $valLabel = $this->getOptionLabelById($productTypeCode, $productTypeVal);
                    if ($valLabel) {
                        if ($valLabel == "Sealed Products" || $valLabel == "Pre Orders" || $valLabel == "Supplies") {
                            $result['is_sealedproducts'] = true;
                            $result['is_custom_table_exist'] = $this->checkCustomTableExist($entityCode, $entityVal, false, true);
                        }
                        if ($valLabel == "Singles") {
                            $result['is_custom_table_exist'] = $this->checkCustomTableExist($entityCode, $entityVal, true, false);
                            $result['is_singles'] = true;
                        }
                    }

                    $result['sets_selection_scope'] = 'miniaturegame_sets';
                }


                // MINIATURE GAME
                if (str_contains($productTypeCode, "gw_miniature_product_type")) {
                    $valLabel = $this->getOptionLabelById($productTypeCode, $productTypeVal);
                    if ($valLabel) {
                        // if ($valLabel == "Sealed Products" || $valLabel == "Pre Orders" || $valLabel == "Supplies") {
                        //     $result['is_sealedproducts'] = true;
                        //     $result['is_custom_table_exist'] = $this->checkCustomTableExist($entityCode, $entityVal, false, true);
                        // }
                        // if ($valLabel == "Singles") {
                            $result['is_custom_table_exist'] = $this->checkCustomTableExist($entityCode, $entityVal, true, false);
                        //     $result['is_singles'] = true;
                        // }
                    }

                    $result['sets_selection_scope'] = 'gamesworkshop_sets';
                }

                // NSC
                if (str_contains($productTypeCode, "nsc_product_type")) {
                    $valLabel = $this->getOptionLabelById($productTypeCode, $productTypeVal);
                    if ($valLabel) {
                        if ($valLabel == "Singles") {
                            $result['is_custom_table_exist'] = $this->checkCustomTableExist($entityCode, $entityVal, true, false);
                            $result['is_singles'] = true;
                        }
                        if ($valLabel == "Sealed Products" || $valLabel == "Pre Orders" || $valLabel == "Supplies") {
                            $result['is_sealedproducts'] = true;
                            $result['is_custom_table_exist'] = $this->checkCustomTableExist($entityCode, $entityVal, false, true);
                        }
                    }

                    $result['sets_selection_scope'] = 'nonsportcard_sets';
                }

                // SPORT CARD
                if (str_contains($productTypeCode, "sc_product_type") && !str_contains($productTypeCode, "nsc_product_type")) {
                    $valLabel = $this->getOptionLabelById($productTypeCode, $productTypeVal);
                    if ($valLabel) {
                        if ($valLabel == "Singles") {
                            $result['is_next_step'] = true;
                            $result['is_custom_table_exist'] = true;
                            $result['is_singles'] = true;
                        }
                        if ($valLabel == "Sealed Products" || $valLabel == "Pre Orders" || $valLabel == "Supplies") {
                            $result['is_sealedproducts'] = true;
                            $result['is_custom_table_exist'] = $this->checkCustomTableExist($entityCode, $entityVal, false, true);
                        }
                    }

                    $result['sets_selection_scope'] = 'sportcard_sets';
                }

                if (str_contains($productTypeCode, "sc_set_type")) {
                    $valLabel = $this->getOptionLabelById($productTypeCode, $productTypeVal);
                    if ($valLabel) {
                        // if ($valLabel == "Singles") {
                        //     $result['is_custom_table_exist'] = $this->checkCustomTableExist($entityCode, $entityVal);
                            $result['is_singles'] = true;
                        // }
                        // if ($valLabel == "Sealed Products" || $valLabel == "Pre Orders" || $valLabel == "Supplies") {
                            // $result['is_sealedproducts'] = true;
                            $result['is_custom_table_exist'] = true;
                        // }
                    }

                    $result['sets_selection_scope'] = 'sportcard_sets';
                }

                // ACTION TOYS
                if (str_contains($productTypeCode, "toys_line")) {
                    $valLabel = $this->getOptionLabelById($productTypeCode, $productTypeVal);
                    
                    // if ($valLabel) {
                        // if (str_contains($valLabel, "Case") || str_contains($valLabel, "Boxes")) {
                            $result['is_custom_table_exist'] = $this->checkCustomTableExist($entityCode, $entityVal);
                        // }
                        // if (str_contains($valLabel, "Singles")) {
                            // $result['is_singles'] = true;
                        //     $result['is_custom_table_exist'] = $this->checkCustomTableExist($entityCode, $entityVal);
                        // }
                    // }

                    $result['sets_selection_scope'] = 'actiontoy_sets';
                }

                // ANIME
                if (str_contains($productTypeCode, "product_type") && $entityCode == "anime_toys_sub_categories") {
                    $valLabel = $this->getOptionLabelById($productTypeCode, $productTypeVal);
                    
                    if ($valLabel) {
                        if (str_contains($valLabel, "Case") || str_contains($valLabel, "Boxes")) {
                            $result['is_custom_table_exist'] = $this->checkCustomTableExist($entityCode, $entityVal);
                        }
                        if (str_contains($valLabel, "Singles")) {
                            $result['is_singles'] = true;
                            $result['is_custom_table_exist'] = $this->checkCustomTableExist($entityCode, $entityVal, true, false);
                        }
                    }

                    $result['sets_selection_scope'] = 'animefigures_sets';
                }

                if ($productTypeCode == "card_product_type") {
                    $valLabel = $this->getOptionLabelById($productTypeCode, $productTypeVal);
                    if ($valLabel) {
                        if ($valLabel == "Singles") {
                            $result['is_custom_table_exist'] = $this->checkCustomTableExist($entityCode, $entityVal, true, false);
                            $result['is_singles'] = true;
                        }
                        if ($valLabel == "Sealed Products") {
                            $result['is_sealedproducts'] = true;
                            $result['is_custom_table_exist'] = $this->checkCustomTableExist($entityCode, $entityVal, false, true);
                        }
                    }
                    
                    $cardgame = $this->getOptionLabelById($entityCode, $entityVal);
                    // if (str_contains($cardgame, "Secret Lair")) {
                    //     $result['sets_selection_scope'] = 'cardgamemtg_sets';
                    // } else {
                        $result['sets_selection_scope'] = 'cardgame_sets';
                    // }
                }

                if ($productTypeCode == "card_pre_order_product_type") {
                    $valLabel = $this->getOptionLabelById($productTypeCode, $productTypeVal);
                    if ($valLabel) {
                        if ($valLabel == "Singles") {
                            $result['is_custom_table_exist'] = $this->checkCustomTableExist($entityCode, $entityVal, true, false);
                            $result['is_singles'] = true;
                        }
                        if ($valLabel == "Sealed Products") {
                            $result['is_sealedproducts'] = true;
                            $result['is_custom_table_exist'] = $this->checkCustomTableExist($entityCode, $entityVal, false, true);
                        }
                    }
                    
                    $cardgame = $this->getOptionLabelById($entityCode, $entityVal);
                    // if (str_contains($cardgame, "Secret Lair")) {
                    //     $result['sets_selection_scope'] = 'cardgamemtg_sets';
                    // } else {
                        $result['sets_selection_scope'] = 'cardgame_sets';
                    // }
                }

                // if ($productTypeCode == "secret_lair_card_product_type") {
                //     $valLabel = $this->getOptionLabelById($productTypeCode, $productTypeVal);
                //     if ($valLabel) {
                //         if ($valLabel == "Singles") {
                //             $result['is_custom_table_exist'] = $this->checkCustomTableExist($entityCode, $entityVal);
                //             $result['is_singles'] = true;
                //         }
                //         if ($valLabel == "Sealed Products" || $valLabel == "Pre Orders" || $valLabel == "Supplies") {
                //             $result['is_sealedproducts'] = true;
                //             $result['is_custom_table_exist'] = $this->checkCustomTableExist($entityCode, $entityVal);
                //         }
                //     }
                    
                //     $result['sets_selection_scope'] = 'cardgamemtg_sets';
                // }

                if ($productTypeCode == "bricks_categories") {
                    $valLabel = $this->getOptionLabelById($productTypeCode, $productTypeVal);
                    if ($valLabel) {
                        if (str_contains($valLabel, "Minifigures") || str_contains($valLabel, "Box Sets")) {
                            if (str_contains($valLabel, "Minifigures")) {
                                $result['is_singles'] = true;
                            }

                            $result['is_custom_table_exist'] = true;
                            // $result['is_custom_table_exist'] = $this->checkCustomTableExist($entityCode, $entityVal);
                        }
                    }

                    $result['sets_selection_scope'] = 'legosets_sets';
                }

                // if ($productTypeCode == "lego_minifigures_product_type") {
                //     $valLabel = $this->getOptionLabelById($productTypeCod
                // +productTypeVal);
                //     if ($valLabel) {
                //         if ($valLabel == "Singles") {
                //             $result['is_custom_table_exist'] = $this->checkCustomTableExist($entityCode, $entityVal);
                //             $result['is_singles'] = true;
                //         }
                //         if ($valLabel == "Sealed Products" || $valLabel == "Pre Orders" || $valLabel == "Supplies") {
                //             $result['is_custom_table_exist'] = $this->checkCustomTableExist($entityCode, $entityVal);
                //         }
                //     }
                    
                //     $result['sets_selection_scope'] = 'legosets_sets';
                // }
            }
        // }

        return $result;
    }

    public function checkSecretLairSets($sets) 
    {
        if ($sets) {
            $cardSet = $sets['card_set'] ?? null;
            if ($cardSet) {
                return true;
            }
        }

        return false;
    }

    public function checkCustomTableExist($entityCode, $entityVal, $isSingles = false, $isSealedProducts = false) 
    {
        // die($entityVal);
        if ($entityCode && $entityVal) {
            if ($entityCode == "nsc_categories") {
                // $collection = $this->cardGameSets->create()
                // ->getCollection()
                // ->addFieldToFilter('nsc_categories', $entityVal);
                // if ($collection->getSize()) {
                    return true;
                // }
            }

            if ($entityCode == "toys_brand") {
                return true;
            }

            if ($entityCode == "dolls_brand") {
                return true;
            }

            if ($entityCode == "diecast_model_kits_brand") {
                return true;
            }

            if ($entityCode == "railway_brand") {
                return true;
            }

            if ($entityCode == "sc_categories") {
                // $collection = $this->cardGameSets->create()
                // ->getCollection()
                // ->addFieldToFilter('nsc_categories', $entityVal);
                // if ($collection->getSize()) {
                    return true;
                // }
            }

            if ($entityCode == "living_card_game") {
                // $collection = $this->cardGameSets->create()
                // ->getCollection()
                // ->addFieldToFilter('nsc_categories', $entityVal);
                // if ($collection->getSize()) {
                    return true;
                // }
            }

            if ($entityCode == "card_game_mtg") {
                return true;
            }

            if ($entityCode == "card_game") {
                $collection = $this->cardGameSets->create()
                ->getCollection()
                ->addFieldToFilter('main_table.card_game', $entityVal);

                if ($isSingles) {
                    $collection->getSelect()->join(
                        ['singles' => 'marketplace_card_games_sets_card'],
                        'main_table.entity_id = singles.set_id',
                        []
                    )->group('main_table.card_game');
                }

                if ($isSealedProducts) {
                    $collection->getSelect()->join(
                        ['sealedproducts' => 'marketplace_card_games_sealed_products'],
                        'main_table.card_release = sealedproducts.card_release',
                        []
                    )->group('main_table.card_game');
                }

                if ($collection->getSize()) {
                    return true;
                }
            }

            if ($entityCode == "miniature_game") {
                // $collection = $this->miniatureGameSets->create()
                // ->getCollection()
                // ->addFieldToFilter('miniature_game', $entityVal);
                // if ($collection->getSize()) {
                    return true;
                // }
            }

            if ($entityCode == "gw_miniature_game") {
                // $collection = $this->miniatureGameSets->create()
                // ->getCollection()
                // ->addFieldToFilter('miniature_game', $entityVal);
                // if ($collection->getSize()) {
                    return true;
                // }
            }

            // Check custom table by categories
            if ($entityCode == "bricks_brand") {
                // $category = $this->getCategoryById($entityVal);
                // if ($category) {
                //     $categoryName = $category->getName();
                //     if ($categoryName) {
                //         if ($categoryName == "LEGO Complete Sets & Packs") {
                //             // $collection = $this->legoSets->create()
                //             // ->getCollection()
                //             // ->addFieldToFilter('lego', 'legosets');
                //             // die($collection->getSelect()->__toString());
                //             // if ($collection->getSize()) {
                //                 return true;
                //             // }
                //         }

                //         if ($categoryName == "LEGO Minifigures") {
                //             // $collection = $this->legoSets->create()
                //             // ->getCollection()
                //             // ->addFieldToFilter('lego', 'minifigures');
                //             // die($collection->getSelect()->__toString());
                //             // if ($collection->getSize()) {
                                return true;
                //             // }
                //         }
                //     }
                // }
            }
        }

        return false;
    }

    public function getAllPrefixes() 
    {
        $gamesPrefixConfigValueArr = $this->getArraySerializedConfigValue(
            'marketplace_catalog/prefix_configurations/prefix'
        );

        $brandsPrefixConfigValueArr = $this->getArraySerializedConfigValue(
            'marketplace_catalog/brands_prefix_configurations/prefix'
        );
        
        if (!$gamesPrefixConfigValueArr && !$brandsPrefixConfigValueArr) {
            return [];
        }

        return array_merge($brandsPrefixConfigValueArr, $gamesPrefixConfigValueArr);
    }

    public function getPrefixByCategoryId($categoryId, $mainAttrLabel = null, $mainAttrVal = null, $mainAttrCode = null) 
    {
        // if(!$categoryId) {
        //     return null;
        // }

        if (!$mainAttrVal && !$mainAttrLabel) {
            return null;
        }

        // Get all prefixes from config
        $allPrefixConfigValueArr = $this->getAllPrefixes();
        if (!$allPrefixConfigValueArr) {
            return null;
        }

        if (!$mainAttrLabel) {
            $mainAttrLabel = $this->getOptionLabelById($mainAttrCode, $mainAttrVal);
        }

        // dd($mainAttrLabel);

        foreach ($allPrefixConfigValueArr as $row) {
            // if ($row['category_id'] == $categoryId) {
                // dd($row);
                if ($row['main_attr_label'] == $mainAttrLabel) {
                    return $row['prefix'] . '_';
                }
            // }
        }

        return null;
    }

    public function getArraySerializedConfigValue($path) 
    {
        $prefixes = [];

        $configValue = $this->scopeConfig->getValue(
            $path, ScopeInterface::SCOPE_STORE
        );

        // dd($configValue);

        if ($configValue) {
            // dd($configValue);
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            /* Create a new product object */
            $this->serializer = $objectManager->create(\Magento\Framework\Serialize\SerializerInterface::class);
            
            return $this->serializer->unserialize($configValue);
        }

        return null;
    }

    public function processImportProductMappingData($rowData)
    {        
        // if ($rowData) {
        //     // [categories] => Games/Collectible Card Games
        //     $categoriesNamesPath = isset($rowData['categories']) ? $rowData['categories'] : null;
        //     if ($categoriesNamesPath) {
        //         $categoryNamesArr = explode("/", $categoriesNamesPath);
        //         $categoryName = $categoryNamesArr[array_key_last($categoryNamesArr)];
        //         if ($categoryName) {
        //             $attrCodes = [];
        //             foreach($rowData as $attributeCode => $attrValuesLabel) {
        //                 $attrCodes[] = $attributeCode;
        //             }
        //             if ($attrCodes) {
        //                 $category = $this->getCategoryByName($categoryName);
        //                 // die('<pre>'.print_r($category->getData(),true));

        //                 $collection = $this->prdAttrLinkCollFactory->create();
        //                 $collection->addFieldToFilter('mapping_type', 'attribute_set');
        //                 $collection->addFieldToFilter('attribute_code', ['in' => $attrCodes]);
        //                 $collection->setOrder('priority', 'DESC');
        //                 $collection->getSelect()
        //                 ->joinLeft(
        //                     ['mpalg' => 'marketplace_product_attributes_link_group'],
        //                     'main_table.group_id=mpalg.entity_id',
        //                     []
        //                 )->where('mpalg.name LIKE "'.$categoryName.' | %"')->order('priority DESC');
        //                 // die($collection->getSelect()->__toString());
        //                 if ($collection->getSize()) {
        //                     foreach($collection as $model) {
        //                         $attrCode = $model->getAttributeCode();
        //                         $attrId = $this->getAttributeOptionId($attrCode, $rowData[$attrCode]);
        //                         if ($attrId) {
        //                             // die($model->getAttributeOptionId().' dan '.$attrId);
        //                             if ($model->getAttributeOptionId() == $attrId) {
        //                                 $rowData['_attribute_set'] = $this->getAttrSetName($model->getTargetAttributeSetId());
        //                                 break;
        //                             }
        //                         }
        //                     }
        //                 }
                        
        //                 if (!isset($rowData['_attribute_set']) || isset($rowData['_attribute_set']) && !$rowData['_attribute_set']) {
        //                     if ($category) {
        //                         $categoryId = $category->getId();
        //                         if ($categoryId) {
        //                             $collection = $this->prdAttrLinkCollFactory->create();
        //                             $collection->addFieldToFilter('mapping_type', 'attribute_set');
        //                             $collection->addFieldToFilter('category_id', ['eq' => $categoryId]);
        //                             if ($collection->getSize()) {
        //                                 $model = $collection->getFirstItem();
        //                                 if ($model) {
        //                                     $rowData['_attribute_set'] = $this->getAttrSetName($model->getTargetAttributeSetId());
        //                                     // $rowData
        //                                 }
        //                             }
        //                         }
        //                     }
        //                 }
        //             }

        //             // $rowData['_attribute_set'] = 'Magic: The Gathering | Singles';
        //         }
        //     }
        // }

        // die('<pre>'.print_r($rowData,true));

        return $rowData;
    }

    public function processImportProductSinglesData($rowData) 
    {
        $collection = null;

        // CARD GAMES
        // if (isset($rowData['card_game']) && $rowData['card_game'] && isset($rowData['card_name']) && $rowData['card_name']) {
        //     $collection = $this->cardGameSetsCard->create()
        //     ->getCollection()
        //     ->addFieldToFilter('card_name', $rowData['card_name']);
        // }

        // die('<pre>'.print_r($rowData,true));

        // Sport Card
        // if (isset($rowData['card_name']) && $rowData['card_name']) {
        //     $collection = $this->sportCardsSetsCard->create()
        //     ->getCollection()
        //     ->addFieldToFilter('card_name', $rowData['card_name']);
        // }

        // Non Sport Card
        // if (isset($rowData['card_name']) && $rowData['card_name']) {
        //     $collection = $this->nonSportCardsSetsCard->create()
        //     ->getCollection()
        //     ->addFieldToFilter('card_name', $rowData['card_name']);
        // }

        // Miniature Game
        // if (isset($rowData['figurename']) && $rowData['figurename']) {
        //     $collection = $this->miniatureGameSingles->create()
        //     ->getCollection()
        //     ->addFieldToFilter('figurename', $rowData['figurename']);
        // }

        // $notAllowedAttrCodes = ['entity_id','created_at','updated_at','set_id','images'];

        // if ($collection && $collection->getSize()) {
        //     $model = $collection->getFirstItem();
        //     if ($model) {
        //         // die('<pre>'.print_r($model->getData(),true));
        //         foreach($model->getData() as $key => $data) {
        //             if (!in_array($key, $notAllowedAttrCodes)) {
        //                 if ($data && $data != null && $data != '') {
        //                     $rowData[$key] = $data;
        //                 }
        //             }
        //         }
        //     }
        // }

        // die('<pre>'.print_r($rowData,true));

        return $rowData;
    }

    public function getAttrSetName($setId)
    {
        $attributeSet = $this->_attributeSetCollection->create()
        ->addFieldToFilter('attribute_set_id',$setId);
        foreach($attributeSet as $attr) {
            return $attr->getAttributeSetName();
        }

        return null;
    }

    public function getMiniatureGameSetsById($setId, $input = false) 
    {
        if ($setId) {
            $collection = $this->miniatureGameSets->create()
            ->getCollection()
            ->addFieldToFilter('entity_id', $setId);
            if ($collection->getSize()) {
                return $collection->getFirstItem();
            }
        }

        if ($input) {
            return $this->miniatureGameSets->create();
        }

        return null;
    }

    public function getMiniatureGameSinglesById($singlesId, $input = false) 
    {
        if ($singlesId) {
            $collection = $this->miniatureGameSingles->create()
            ->getCollection()
            ->addFieldToFilter('entity_id', $singlesId);
            if ($collection->getSize()) {
                return $collection->getFirstItem();
            }
        }

        if ($input) {
            return $this->miniatureGameSingles->create();
        }

        return null;
    }
    
    public function getLivingCardGameSetsById($setId, $input = false) 
    {
        if ($setId) {
            $collection = $this->livingCardGameSets->create()
            ->getCollection()
            ->addFieldToFilter('entity_id', $setId);
            if ($collection->getSize()) {
                return $collection->getFirstItem();
            }
        }

        if ($input) {
            return $this->livingCardGameSets->create();
        }

        return null;
    }

    public function getLegoSets($bricks_brand, $input = false, $minifigures = false) 
    {
        if ($bricks_brand) {
            $collection = $this->legoSets->create()
            ->getCollection()
            ->addFieldToFilter('bricks_brand', $bricks_brand);
            if ($minifigures) {
                $collection->addFieldToFilter('number_of_minifigures', ['neq' => null]);
            }
            $collection->setOrder('bricks_set_name', 'ASC');
            // $collection->getSelect()->order(
            //     new \Zend_Db_Expr(
            //         'CASE 
            //             WHEN main_table.lego_set_name REGEXP "^[0-9]+$" THEN 1
            //             ELSE 2 
            //         END, 
            //         CAST(REGEXP_SUBSTR(main_table.lego_set_name, "[0-9]+") AS UNSIGNED) ASC'
            //     )
            // );
            if ($collection->getSize()) {
                return $collection;
            }
        }

        if ($input) {
            return $this->legoSets->create();
        }

        return null;
    }

    public function getLegoSetsById($setId, $input = false) 
    {
        if ($setId) {
            $collection = $this->legoSets->create()
            ->getCollection()
            ->addFieldToFilter('entity_id', $setId);
            if ($collection->getSize()) {
                return $collection->getFirstItem();
            }
        }

        if ($input) {
            return $this->legoSets->create();
        }

        return null;
    }

    public function getLegoSinglesById($singlesId, $input = false) 
    {
        if ($singlesId) {
            $collection = $this->legoSingles->create()
            ->getCollection()
            ->addFieldToFilter('entity_id', $singlesId);
            if ($collection->getSize()) {
                return $collection->getFirstItem();
            }
        }

        if ($input) {
            return $this->legoSingles->create();
        }

        return null;
    }

    public function getNonSportCardSetsById($setId, $input = false) {
        if ($setId) {
            $collection = $this->nonSportCardsSets->create()
            ->getCollection()
            ->addFieldToFilter('entity_id', $setId);
            if ($collection->getSize()) {
                return $collection->getFirstItem();
            }
        }

        if ($input) {
            return $this->nonSportCardsSets->create();
        }

        return null;
    }

    public function getNonSportCardById($cardId, $input = false) {
        if ($cardId) {
            $collection = $this->nonSportCardsSetsCard->create()
            ->getCollection()
            ->addFieldToFilter('entity_id', $cardId);
            if ($collection->getSize()) {
                return $collection->getFirstItem();
            }
        }

        if ($input) {
            return $this->nonSportCardsSetsCard->create();
        }

        return null;
    }

    public function getSportCardById($cardId, $input = false) {
        if ($cardId) {
            $collection = $this->sportCardsSetsCard->create()
            ->getCollection()
            ->addFieldToFilter('entity_id', $cardId);

            $collection->getSelect()->join(
                ['sets' => 'marketplace_sport_cards_sets'],
                'sets.entity_id = main_table.set_id',
                ['sets.sc_categories','sets.sc_set_name']
            )->group('main_table.entity_id');

            if ($collection->getSize()) {
                return $collection->getFirstItem();
            }
        }

        if ($input) {
            return $this->sportCardsSetsCard->create();
        }

        return null;
    }

    public function getSportCardSetsById($setId, $input = false) {
        if ($setId) {
            $collection = $this->sportCardsSets->create()
            ->getCollection()
            ->addFieldToFilter('entity_id', $setId);
            if ($collection->getSize()) {
                return $collection->getFirstItem();
            }
        }

        if ($input) {
            return $this->sportCardsSets->create();
        }

        return null;
    }

    public function getCardGameCardById($cardId, $input = false) 
    {
        if ($cardId) {
            $collection = $this->cardGameSetsCard->create()
            ->getCollection()
            ->addFieldToFilter('entity_id', $cardId);

            $collection->getSelect()->join(
                ['sets' => 'marketplace_card_games_sets'],
                'sets.entity_id = main_table.set_id',
                ['sets.card_game','sets.card_set']
            )->group('main_table.entity_id');

            if ($collection->getSize()) {
                return $collection->getFirstItem();
            }
        }

        if ($input) {
            return $this->cardGameSetsCard->create();
        }

        return null;
    }

    public function getCardGameRelease($cardRelease) {
        // dd($cardRelease);
        if ($cardRelease) {
            $collection = $this->cardGameSets->create()
            ->getCollection()
            ->addFieldToFilter('card_release', $cardRelease);
            if ($collection->getSize()) {
                return $collection->getFirstItem();
            }
        }

        return null;
    }

    public function getCardGameSealedProductsById($id, $input = false) 
    {
        if ($id) {
            $collection = $this->cardGameSealedProducts->create()
            ->getCollection()
            ->addFieldToFilter('entity_id', $id);
            if ($collection->getSize()) {
                return $collection->getFirstItem();
            }
        }

        if ($input) {
            return $this->cardGameSealedProducts->create();
        }

        return null;
    }

    public function getCardGameDropByName($name) 
    {
        if ($name) {
            $collection = $this->cardGameDropFactory->create()
            ->getCollection()
            ->addFieldToFilter('card_drop', $name);
            if ($collection->getSize()) {
                return $collection->getFirstItem();
            }
        }

        return null;
    }

    public function getCardGameReleaseByName($name) 
    {
        if ($name) {
            $collection = $this->cardGameReleaseFactory->create()
            ->getCollection()
            ->addFieldToFilter('card_release', $name);
            if ($collection->getSize()) {
                return $collection->getFirstItem();
            }
        }

        return null;
    }

    public function getCardGameDropById($id, $input = false) 
    {
        if ($id) {
            $collection = $this->cardGameDropFactory->create()
            ->getCollection()
            ->addFieldToFilter('entity_id', $id);
            if ($collection->getSize()) {
                return $collection->getFirstItem();
            }
        }

        if ($input) {
            return $this->cardGameDropFactory->create();
        }

        return null;
    }

    public function getCardGameReleaseById($id, $input = false) 
    {
        if ($id) {
            $collection = $this->cardGameReleaseFactory->create()
            ->getCollection()
            ->addFieldToFilter('entity_id', $id);
            if ($collection->getSize()) {
                return $collection->getFirstItem();
            }
        }

        if ($input) {
            return $this->cardGameReleaseFactory->create();
        }

        return null;
    }

    public function getActionToySetsById($setId, $input = false) 
    {
        if ($setId) {
            $collection = $this->actionToySetsFactory->create()
            ->getCollection()
            ->addFieldToFilter('entity_id', $setId);
            if ($collection->getSize()) {
                return $collection->getFirstItem();
            }
        }

        if ($input) {
            return $this->actionToySetsFactory->create();
        }

        return null;
    }

    public function getCardGameSetsById($setId, $input = false) 
    {
        if ($setId) {
            $collection = $this->cardGameSets->create()
            ->getCollection()
            ->addFieldToFilter('entity_id', $setId);
            if ($collection->getSize()) {
                return $collection->getFirstItem();
            }
        }

        if ($input) {
            return $this->cardGameSets->create();
        }

        return null;
    }

    public function getCardGameSetsByName($name, $input = false) 
    {
        if ($name) {
            $collection = $this->cardGameSets->create()
            ->getCollection()
            ->addFieldToFilter('card_set', $name);
            if ($collection->getSize()) {
                return $collection->getFirstItem();
            }
        }

        return null;
    }

    public function getLivingCardGameSets($living_card_game, $input = false) 
    {
        if ($living_card_game) {
            $collection = $this->livingCardGameSets->create()
            ->getCollection()
            ->addFieldToFilter('living_card_game', $living_card_game);
            if ($collection->getSize()) {
                return $collection;
            }
        }

        if ($input) {
            return $this->livingCardGameSets->create();
        }

        return null;
    }

    public function getMiniatureGameSets($miniature_game, $input = false) 
    {
        if ($miniature_game) {
            $collection = $this->miniatureGameSets->create()
            ->getCollection()
            ->addFieldToFilter('miniature_game', $miniature_game);
            if ($collection->getSize()) {
                return $collection;
            }
        }

        if ($input) {
            return $this->miniatureGameSets->create();
        }

        return null;
    }

    public function getSportCardSets($sc_categories, $input = false) 
    {
        if ($sc_categories) {
            $collection = $this->sportCardsSets->create()
            ->getCollection()
            ->addFieldToFilter('sc_categories', $sc_categories);
            if ($collection->getSize()) {
                return $collection;
            }
        }

        if ($input) {
            return $this->sportCardsSets->create();
        }

        return null;
    }

    public function getNonSportCardSets($nsc_categories, $input = false) 
    {
        if ($nsc_categories) {
            $collection = $this->nonSportCardsSets->create()
            ->getCollection()
            ->addFieldToFilter('nsc_categories', $nsc_categories);
            if ($collection->getSize()) {
                return $collection;
            }
        }

        if ($input) {
            return $this->nonSportCardsSets->create();
        }

        return null;
    }

    public function getCardGameSets($cardGame, $input = false) 
    {
        if ($cardGame) {
            $collection = $this->cardGameSets->create()
            ->getCollection()
            ->addFieldToFilter('card_game', $cardGame);
            if ($collection->getSize()) {
                return $collection;
            }
        }

        if ($input) {
            return $this->cardGameSets->create();
        }

        return null;
    }

    public function filterOptionsByAttribute($filters, $targetAttrCode) 
    {
        $result = [];

        $mappingExist = false;
        
        try {
            if ($filters) {
                foreach($filters as $attrCode => $filter) {
                    $optionId = $filter['eq'];
                    if ($optionId) {
                        $collection = $this->prdAttrLinkCollFactory->create();
                        $collection->addFieldToFilter('mapping_type', 'attribute_option');
                        $collection->addFieldToFilter('attribute_code', $attrCode);
                        $collection->addFieldToFilter('attribute_option_id', $optionId);
                        $collection->addFieldToFilter('target_attribute_code', $targetAttrCode);
                        if ($collection->getSize()) {
                            // die(print_r(explode(",", $collection->getFirstItem()->getTargetAttributeOptions()),true));
                            $targetOptions = $collection->getFirstItem()->getTargetAttributeOptions();
                            if ($targetOptions) {
                                $result = explode(",", $targetOptions);
                            }

                            $mappingExist = true;
                        }
                    }
                }

                if ($mappingExist) {
                    return $result;
                }
            }
        } catch (\Exception $e) {}

        return 'ALL_ITEMS';
    }

    public function filterShopbyData($shopby, $filter) {
        $result = [];

        $mappingExist = false;
        
        try {
            $code = null;
            $optionId = null;
            
            $filterData = $filter ? explode("|", $filter) : [];
            if ($filterData) {
                $code = $filterData[0];
                $value = $filterData[1];

                $optionId = $this->getOptionId($code, $value, true);
            }
            $collection = $this->prdAttrLinkCollFactory->create();
            $collection->addFieldToFilter('mapping_type', 'attribute_option');
            $collection->addFieldToFilter('target_attribute_code', $shopby);
            $collection->addFieldToFilter('attribute_code', $code);
            $collection->addFieldToFilter('attribute_option_id', $optionId);
            // die($collection->getSelect()->__toString());
            if ($collection->getSize()) {
                // die(print_r(explode(",", $collection->getFirstItem()->getTargetAttributeOptions()),true));
                $targetOptions = $collection->getFirstItem()->getTargetAttributeOptions();
                if ($targetOptions) {
                    $result = explode(",", $targetOptions);
                }

                $mappingExist = true;
            }
        } catch (\Exception $e) {}

        if (!$result && $mappingExist) {
            return ['EMPTY_MAPPING'];
        }

        return $result;
    }

    public function getMappingOptions($attributeCode, $group = null) 
    {
        $result = [];
        
        try {
            $collection = $this->prdAttrLinkCollFactory->create();
            $collection->addFieldToFilter('mapping_type', 'attribute_option');
            $collection->addFieldToFilter('target_attribute_code', $attributeCode);
            $collection->getSelect()
            ->joinLeft(
                ['mpalg' => 'marketplace_product_attributes_link_group'],
                'main_table.group_id=mpalg.entity_id',
                []
            )->where('mpalg.name="'.$group.'"');
            // die($collection->getSelect()->__toString());
            if ($collection->getSize()) {
                // die(print_r(explode(",", $collection->getFirstItem()->getTargetAttributeOptions()),true));
                return explode(",", $collection->getFirstItem()->getTargetAttributeOptions());
            }
        } catch (\Exception $e) {}

        return $result;
    }

    public function getCategoryById($catId) 
    {
        $collection = $this->categoryCollectionFactory->create()
        ->addAttributeToSelect('name')
        ->addAttributeToSelect('url_key')
        ->addFieldToFilter('entity_id', $catId);
        if ($collection->getSize()) {
            return $collection->getFirstItem();
        }

        return null;
    }

    public function getCachedCategoryById($catId) 
    {
        $cacheKey = self::CACHE_TAG . '_category_data_' . $catId;
        $cachedData = $this->cache->load($cacheKey);

        if ($cachedData) {
            return unserialize($cachedData); // Return cached collection
        }

        $collection = $this->categoryCollectionFactory->create()
        ->addAttributeToSelect('name')
        ->addAttributeToSelect('url_key')
        ->addFieldToFilter('entity_id', $catId);
        if ($collection->getSize()) {
            $categoryData = $collection->getFirstItem()->getData();

            $this->cache->save(
                serialize($categoryData), // Serialized data
                $cacheKey,                  // Cache key
                [self::CACHE_TAG]           // Cache tags
            );

            return $categoryData;
        }

        return null;
    }

    public function getCategoryLabelByIds($categoryPathIds) 
    {
        $categoryId = end($categoryPathIds);

        $collection = $this->categoryCollectionFactory->create()
        ->addAttributeToSelect('name')
        ->addFieldToFilter('entity_id', ['eq' => $categoryId])
        ->setOrder('level', 'ASC');
        if ($collection->getSize()) {
            return $collection->getFirstItem()->getName();
        }

        return null;
    }

    public function getCategoryPathsByName($categoryName) 
    {
        $collection = $this->categoryCollectionFactory->create()
        ->addFieldToFilter('level', ['eq' => 3])
        ->addAttributeToFilter('name', $categoryName);
        if ($collection->getSize()) {
            $category = $collection->getFirstItem();
            $path = $category->getPathIds();
            if ($path) {
                return $this->getCategoryPathLabelByIds($path);
            }
        }

        return null;
    }

    public function getCategoryPathLabelByIds($categoryPathIds) 
    {
        array_shift($categoryPathIds);
        
        $catNameArr = [];

        $collection = $this->categoryCollectionFactory->create()
        ->addAttributeToSelect('name')
        ->addFieldToFilter('entity_id', ['in' => $categoryPathIds])
        ->setOrder('level', 'ASC');
        if ($collection->getSize()) {
            foreach ($collection as $index => $cat) {
                $catNameArr[] = $cat->getName();
            }

            if ($catNameArr) {
                return implode("/", $catNameArr);
            }
        }

        return null;
    }

    public function getCategoryByName($name) 
    {
        $collection = $this->categoryCollectionFactory->create()
        ->addFieldToFilter('name', $name);
        if ($collection->getSize()) {
            return $collection->getFirstItem();
        }

        return null;
    }

    public function getCategoryNameByUrlKey($categoryUrlKey) 
    {
        $collection = $this->categoryCollectionFactory->create()
        ->addFieldToFilter('url_key', $categoryUrlKey)
        ->addAttributeToSelect(['name']);
        if ($collection->getSize()) {
            return $collection->getFirstItem()->getName();
        }

        return null;
    }

    public function getCategoryIdByUrlKey($categoryUrlKey) 
    {
        $collection = $this->categoryCollectionFactory->create()
        ->addFieldToFilter('url_key', $categoryUrlKey);
        if ($collection->getSize()) {
            return $collection->getFirstItem()->getId();
        }

        return null;
    }

    public function getCategoryIdByName($categoryName) 
    {
        $collection = $this->categoryCollectionFactory->create()
        ->addFieldToFilter('level', ['eq' => 3])
        ->addAttributeToFilter('name', $categoryName);
        if ($collection->getSize()) {
            return $collection->getFirstItem()->getId();
        }

        return null;
    }

    public function getCollectibleGameSets() 
    {
        $collection = $this->cardGamesMappingFactory->create()->getCollection();
        $collection->getSelect()->group('card_set');
        if ($collection->getSize()) {
            return $collection;
        }

        return null;
    }

    public function getAllMappingTargetAttributeCode() 
    {
        $result = [];
        
        try {
            $collection = $this->prdAttrLinkCollFactory->create();
            $collection->getSelect()->group('target_attribute_code');
            if ($collection->getSize()) {
                foreach($collection as $data) {
                    $result[] = $data->getTargetAttributeCode();
                }
            }

            // $fields = $this->cardGamesMappingFactory->create()
            // ->getResource()
            // ->getFields();
            // if ($fields) {
            //     $skipFields = [
            //         'entity_id',
            //         'created_at',
            //         'updated_at',
            //         'language'
            //     ];
            //     foreach($fields as $field) {
            //         $columnName = $field['COLUMN_NAME'];
            //         if (!in_array($columnName, $skipFields)) {
            //             $result[] = $columnName;
            //         }
            //     }
            // }
        } catch (\Exception $e) {}

        return $result;
    }

    public function createNewCard($postData) 
    {
        if ($postData) {
            $model = $this->cardGamesMappingFactory->create();
            $model->setData($postData);
            $model->save();

            return $model;
        }

        return null;
    }

    public function getGroupIdByName($groupName) 
    {
        $collection = $this->mappingGroupFactory->create()
        ->getCollection()
        ->addFieldToFilter('name', $groupName);
        if ($collection->getSize()) {
            return $collection->getFirstItem()->getId();
        } else {
            $group = $this->mappingGroupFactory->create()
            ->setName($groupName)
            ->save();

            return $group->getId();
        }

        return null;
    }

    public function getAllMappingGroup() 
    {
        $collection = $this->mappingGroupFactory->create()
        ->getCollection();

        $collection->getSelect()
        ->joinLeft(
            ['mpal' => 'marketplace_product_attributes_link'],
            'main_table.entity_id=mpal.group_id',
            []
        )->group('main_table.entity_id');
        
        if ($collection->getSize()) {
            return $collection;
        }

        return null;
    }
    
    public function getCategoryByUrlKey($urlKey) 
    {
        $collection = $this->categoryCollectionFactory->create()
        ->addFieldToFilter('url_key', $urlKey);
        // ->addFieldToFilter('level', ['neq' => 1])
        // ->addFieldToFilter('level', ['neq' => 2])
        // ->addAttributeToSelect(['name']);
        if ($collection->getSize()) {
            return $collection->getFirstItem();
        }

        return null;
    }

    public function getAllCategories() 
    {
        $collection = $this->categoryCollectionFactory->create()
        ->addAttributeToSelect(['name']);
        if ($collection->getSize()) {
            return $collection;
        }

        return null;
    }

    public function getAllAttributeSet() 
    {
        return $this->attributeSetOptions->toOptionArray();
    }

    public function getAllProductAttributes($type = null) 
    {
        $attributes = $this->attributeFactory->getCollection()
        ->addFieldToFilter('entity_type_id', 4)
        ->setOrder('frontend_label', 'ASC');
        if ($type) {
            $attributes->addFieldToFilter('frontend_input', $type);
        }

        if ($attributes) {
            return $attributes;
        }

        return null;
    }

    public function getOptionLabelById($attributeCode, $optionId) 
    {
        $attribute = $this->eavConfig->getAttribute('catalog_product', $attributeCode);
        $optionlabel = $attribute->getSource()->getOptionText($optionId);

        return $optionlabel;
    }

    public function getAttributeOptions($attributeCode) 
    {
        $options = [];

        try {
            $options = $this->attributeRepository->get($attributeCode, 'catalog_product')->getOptions();
        } catch(\Exception $e) {
            
        }

        return $options;
    }

    public function getCachedAttributeOptions($attributeCode) 
    {
        $options = [];

        if (!is_array($attributeCode)) {
            $cacheKey = self::CACHE_TAG . '_' . $attributeCode . '_attributeoptions';
            $cachedData = $this->cache->load($cacheKey);

            if ($cachedData) {
                return unserialize($cachedData); // Return cached collection
            }
            
            try {
                $rawOptions = $this->getAttributeOptions($attributeCode);
                $options = array_map(fn($option) => $option->toArray(), $rawOptions);

                $this->cache->save(
                    serialize($options), // Serialized data
                    $cacheKey,                  // Cache key
                    [self::CACHE_TAG]           // Cache tags
                );
            } catch(\Exception $e) {
                
            }
        }

        return $options;
    }

    public function getCachedAttributeData($attributeCode) 
    {
        $cacheKey = self::CACHE_TAG . '_attribute_' . $attributeCode;
        $cachedData = $this->cache->load($cacheKey);

        if ($cachedData) {
            return unserialize($cachedData); // Return cached collection
        }

        try {
            $attribute = $this->attributeRepository->get($attributeCode, 'catalog_product');
            $attributeData = $attribute->toArray();
            unset($attributeData['entity_type']);
            unset($attributeData['extension_attributes']);
            
            // dd($attributeData);

            $this->cache->save(
                serialize($attributeData), // Serialized data
                $cacheKey,                  // Cache key
                [self::CACHE_TAG]           // Cache tags
            );

            return $attributeData;
        } catch(\Exception $e) {
            // dd($e);
        }

        return null;
    }

    public function getAttribute($attributeCode) 
    {
        try {
            return $this->attributeRepository->get($attributeCode, 'catalog_product');
        } catch(\Exception $e) {
            
        }

        return null;
    }

    public function getAttributeOptionId($attributeCode, $label)
    {
        if (!$label) {
            return null;
        }
        
        $label = trim($label);

        if ($label) {
            $optionId = $this->getOptionId($attributeCode, $label, true);
            if ($optionId) {
                return $optionId;
            } else {
                /** @var \Magento\Eav\Model\Entity\Attribute\OptionLabel $optionLabel */
                $optionLabel = $this->optionLabelFactory->create();
                $optionLabel->setStoreId(0);
                $optionLabel->setLabel($label);

                $option = $this->optionFactory->create();
                $option->setLabel($optionLabel->getLabel());
                $option->setStoreLabels([$optionLabel]);
                $option->setSortOrder(0);
                $option->setIsDefault(false);

                try {
                    $this->attributeOptionManagement->add(
                        \Magento\Catalog\Model\Product::ENTITY,
                        $this->getAttribute($attributeCode)->getAttributeId(),
                        $option
                    );

                    // $this->attributeValues[$attribute->getAttributeId()][$option['label']] = $option['value'];
                } catch (\Exception $e) {
                //     g
                }

                // Get the inserted ID. Should be returned from the installer, but it isn't.
                $optionId = $this->getOptionId($attributeCode, $label, true);
                if ($optionId) {
                    return $optionId;
                }
            }
        }

        return null;
    }

    public function getOrInputAttributeOptionId($attributeCode, $label)
    {
        $label = trim($label);

        if ($label) {
            $optionId = $this->getOptionId($attributeCode, $label, true);
            if ($optionId) {
                return $optionId;
            } else {
                // Input new option
                /** @var \Magento\Eav\Model\Entity\Attribute\OptionLabel $optionLabel */
                $optionLabel = $this->optionLabelFactory->create();
                $optionLabel->setStoreId(0);
                $optionLabel->setLabel($label);

                $option = $this->optionFactory->create();
                $option->setLabel($optionLabel->getLabel());
                $option->setStoreLabels([$optionLabel]);
                $option->setSortOrder(0);
                $option->setIsDefault(false);

                $this->attributeOptionManagement->add(
                    \Magento\Catalog\Model\Product::ENTITY,
                    $this->getAttribute($attributeCode)->getAttributeId(),
                    $option
                );

                // Get the inserted ID. Should be returned from the installer, but it isn't.
                $optionId = $this->getOptionId($attributeCode, $label, true);
                if ($optionId) {
                    return $optionId;
                }
            }
        }

        return null;
    }

    /**
     * Find the ID of an option matching $label, if any.
     *
     * @param string $attributeCode Attribute code
     * @param string $label Label to find
     * @param bool $force If true, will fetch the options even if they're already cached.
     * @return int|false
     */
    public function getOptionId($attributeCode, $label, $force = false)
    {
        if ($attributeCode && $attributeCode != "") {
            /** @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute */
            $attribute = $this->getAttribute($attributeCode);
            if ($attribute) {
                // Build option array if necessary
                if ($force === true || !isset($this->attributeValues[ $attribute->getAttributeId() ])) {
                    $this->attributeValues[$attribute->getAttributeId()] = [];

                    // We have to generate a new sourceModel instance each time through to prevent it from
                    // referencing its _options cache. No other way to get it to pick up newly-added values.

                    /** @var \Magento\Eav\Model\Entity\Attribute\Source\Table $sourceModel */
                    $sourceModel = $this->tableFactory->create();
                    $sourceModel->setAttribute($attribute);

                    foreach ($sourceModel->getAllOptions() as $option) {
                        $this->attributeValues[$attribute->getAttributeId()][$option['label']] = $option['value'];
                    }
                }
                
                if (isset($this->attributeValues[$attribute->getAttributeId()][$label])) {
                    return $this->attributeValues[$attribute->getAttributeId()][$label];
                }
            }
        }
        
        return false;
    }

    public function getLegoSetsFields() 
    {
        $fields = ['bricks_set_name'];

        return json_encode($fields, true);
    }

    public function getCardGamesMappingFields() 
    {
        $fields = ['card_set','card_name'];

        return json_encode($fields, true);
    }

    public function getMappingFieldsPriority() 
    {
        $fields = [];

        try {

            $collection = $this->prdAttrLinkCollFactory->create();
            $collection->getSelect()->group('attribute_code');
            if ($collection->getSize()) {
                foreach($collection as $mapping) {
                    if (!$mapping->getAttributeCode()) {
                        continue;
                    }

                    $fields[$mapping->getAttributeCode()] = $mapping->getPriority();
                }
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }

        return json_encode($fields, true);
    }

    public function getAttributeSetMappingFields() 
    {
        $fields = [];

        try {

            $collection = $this->prdAttrLinkCollFactory->create();
            $collection->getSelect()->group('attribute_code');
            if ($collection->getSize()) {
                foreach($collection as $mapping) {
                    if (!$mapping->getAttributeCode()) {
                        continue;
                    }

                    $fields[] = $mapping->getAttributeCode();
                }
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }

        return json_encode($fields, true);
    }

    public function getMappingByAttributeSetId($attrSetId) 
    {
        if ($attrSetId) {
            try {
                $collection = $this->prdAttrLinkCollFactory->create()
                ->addFieldToFilter('target_attribute_set_id', $attrSetId)
                ->setOrder('priority', 'DESC');
                if ($collection->getSize()) {
                    return $collection->getFirstItem();
                }
            } catch (\Exception $e) {}
        }

        return null;
    }

    public function getFilteredAttributeOptions($byAttrCode, $attrOptionId, $targetAttrCode) 
    {
        $filteredOptionIds = [];

        if ($targetAttrCode) {
            try {

                $collection = $this->prdAttrLinkCollFactory->create()
                ->addFieldToFilter('mapping_type', 'attribute_option')
                ->addFieldToFilter('attribute_code', $byAttrCode)
                ->addFieldToFilter('attribute_option_id', $attrOptionId)
                ->addFieldToFilter('target_attribute_code', $targetAttrCode);
                // die($collection->getSelect()->__toString());
                if ($collection->getSize()) {
                    foreach($collection as $mapping) {
                        $targetAttrOptions = $mapping->getTargetAttributeOptions();
                        if (!$targetAttrOptions) {
                            continue;
                        }

                        // die(print_r($targetAttrOptions,true));

                        $filteredOptionIds = array_merge($filteredOptionIds, explode(",", $targetAttrOptions));
                    }
                }
            } catch (\Exception $e) {}
        }

        // die(print_r($filteredOptionIds,true));

        return $filteredOptionIds;
    }

    public function getFilteredAttributeOptionsByCategoryId($categoryId, $targetAttrCode) 
    {
        $filteredOptionIds = [];

        if ($categoryId) {
            try {

                $collection = $this->prdAttrLinkCollFactory->create()
                ->addFieldToFilter('mapping_type', 'attribute_option')
                ->addFieldToFilter('category_id', $categoryId)
                ->addFieldToFilter('target_attribute_code', $targetAttrCode);
                // die($collection->getSelect()->__toString());
                if ($collection->getSize()) {
                    foreach($collection as $mapping) {
                        $targetAttrOptions = $mapping->getTargetAttributeOptions();
                        if (!$targetAttrOptions) {
                            continue;
                        }

                        // die(print_r($targetAttrOptions,true));

                        $filteredOptionIds = array_merge($filteredOptionIds, explode(",", $targetAttrOptions));
                    }
                }
            } catch (\Exception $e) {}
        }

        // die(print_r($filteredOptionIds,true));

        return $filteredOptionIds;
    }

    public function getFilteredAttributeOptionsByCategory($categoryUrlKey, $targetAttrCode) 
    {
        $filteredOptionIds = [];

        // die('oke'.$categoryUrlKey);

        $category = $this->getCategoryByUrlKey($categoryUrlKey);
        if ($category) {
            $categoryId = $category->getId();
            // die('oke'.$categoryId);
            if ($categoryId) {
                try {
                    $collection = $this->prdAttrLinkCollFactory->create()
                    ->addFieldToFilter('mapping_type', 'attribute_option')
                    ->addFieldToFilter('category_id', $categoryId)
                    ->addFieldToFilter('target_attribute_code', $targetAttrCode);
                    // die($collection->getSelect()->__toString());
                    if ($collection->getSize()) {
                        foreach($collection as $mapping) {
                            $targetAttrOptions = $mapping->getTargetAttributeOptions();
                            if (!$targetAttrOptions) {
                                continue;
                            }

                            // die(print_r($targetAttrOptions,true));

                            $filteredOptionIds = array_merge($filteredOptionIds, explode(",", $targetAttrOptions));
                        }

                        return $filteredOptionIds;
                    }
                } catch (\Exception $e) {}
            }
        }

        // die(print_r($filteredOptionIds,true));

        return 'ALL_ITEMS';
    }
}