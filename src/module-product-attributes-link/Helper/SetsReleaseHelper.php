<?php

namespace TCGCollective\ProductAttributesLink\Helper;

use Magento\Framework\Url;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\UrlInterface;
use Magento\Catalog\Model\ProductFactory;
use Lof\MarketPlace\Helper\Data as MarketHelper;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use TCGCollective\MarketPlace\Model\PlatformDatabaseCache;
use Magento\Framework\Filesystem;
use TCGCollective\ProductAttributesLink\Helper\Data as MappingHelper;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;

class SetsReleaseHelper extends \Magento\Framework\App\Helper\AbstractHelper
{
    const CACHE_TAG = 'seller_setsrelease_selection_cache';

    protected $cardGameSetsCard;
    protected $directory;


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
    protected $actionToySetsFactory;
    protected $actionToySinglesFactory;
    protected $cardGameSealedProducts;
    protected $cardGameDropFactory;
    protected $dollsSetsFactory;
    protected $dollsSinglesFactory;
    protected $railwaySetsFactory;
    protected $railwaySinglesFactory;
    protected $diecastModelKitsSetsFactory;
    protected $diecastModelKitsSinglesFactory;
    protected $cardGameReleaseFactory;
    protected $sportCardsSealedProducts;
    protected $legoSealedProducts;
    protected $miniatureGameSealedProducts;
    protected $actionToySealedProducts;
    protected $diecastModelKitsSealedProductsFactory;
    protected $railwaySealedProductsFactory;
    protected $dollsSealedProductsFactory;
    protected $livingCardGameSealedProducts;
    protected $frontendUrl;
    protected $request;
    protected $redirect;
    protected $storeManager;
    protected $urlBuilder;
    protected $productFactory;
    protected $helper;
    protected $productCollection;
    protected $platformDatabaseCache;
    protected $filesystem;
    protected $mappingHelper;
    protected $cache;
    protected $attributeOptionsCollectionFactory;
    protected $state;
    protected $mainCategoriesHelper;
    protected $resource;
    protected $platformDbHelper;
    protected $mainCategoriesConfigProvider;
    
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
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
        Url $frontendUrl,
        \Magento\Framework\App\RequestInterface $request,
        RedirectInterface $redirect,
        StoreManagerInterface $storeManager,
        UrlInterface $urlBuilder,
        ProductFactory $productFactory,
        MarketHelper $helper,
        ProductCollectionFactory $productCollection,
        PlatformDatabaseCache $platformDatabaseCache,
        Filesystem $filesystem,
        MappingHelper $mappingHelper,
        CacheInterface $cache,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory $attributeOptionsCollectionFactory,
        State $state,
        \TCGCollective\MarketPlace\Helper\MainCategoriesHelper $mainCategoriesHelper,
        \Magento\Framework\App\ResourceConnection $resource,
        \TCGCollective\PlatformDatabase\Helper\Data $platformDbHelper,
        \TCGCollective\PlatformDatabase\Model\MainCategoriesConfigProvider $mainCategoriesConfigProvider
    ) {
        parent::__construct(
            $context
        );
        
        $this->mainCategoriesConfigProvider = $mainCategoriesConfigProvider;
        $this->resource = $resource;
        $this->mainCategoriesHelper = $mainCategoriesHelper;
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
        $this->actionToySetsFactory = $actionToySetsFactory;
        $this->actionToySinglesFactory = $actionToySinglesFactory;
        $this->dollsSetsFactory = $dollsSetsFactory;
        $this->dollsSinglesFactory = $dollsSinglesFactory;
        $this->railwaySetsFactory = $railwaySetsFactory;
        $this->railwaySinglesFactory = $railwaySinglesFactory;
        $this->diecastModelKitsSetsFactory = $diecastModelKitsSetsFactory;
        $this->diecastModelKitsSinglesFactory = $diecastModelKitsSinglesFactory;
        $this->legoSealedProducts = $legoSealedProducts;
        $this->miniatureGameSealedProducts = $miniatureGameSealedProducts;
        $this->actionToySealedProducts = $actionToySealedProducts;
        $this->diecastModelKitsSealedProductsFactory = $diecastModelKitsSealedProductsFactory;
        $this->railwaySealedProductsFactory = $railwaySealedProductsFactory;
        $this->dollsSealedProductsFactory = $dollsSealedProductsFactory;
        $this->livingCardGameSealedProducts = $livingCardGameSealedProducts;
        $this->frontendUrl = $context->getUrlBuilder();
        $this->frontendUrl = $frontendUrl;
        $this->request = $request;
        $this->redirect = $redirect;
        $this->storeManager = $storeManager;
        $this->urlBuilder = $urlBuilder;
        $this->productFactory = $productFactory;
        $this->helper = $helper;
        $this->productCollection = $productCollection;
        $this->platformDatabaseCache = $platformDatabaseCache;
        $this->filesystem = $filesystem;
        $this->mappingHelper = $mappingHelper;
        $this->cache = $cache;
        $this->attributeOptionsCollectionFactory = $attributeOptionsCollectionFactory;
        $this->state = $state;
        $this->platformDbHelper = $platformDbHelper;
    }

    // product creation | listing dataprovider
    public function getProductCreationSetsReleaseDataProvider() 
    {
        return $this->processGetProductCreationSetsReleaseDataProvider();
    }

    public function getInputTypesAttributeCodes() {
        return [
            'sc_set_name',
            'sc_card_name',
            'sc_sealed_products',
            'sealed_products',
            'name',
            'supplier_sku',
            'card_name'
        ];
    }
    
    public function getMainEntityVal($mainAttrCode) {
        if ($mainAttrCode) {
            $mainAttrValFromParam = $this->request->getParam($mainAttrCode);
            if ($mainAttrValFromParam) {
                return $mainAttrValFromParam;
            }

            $mainAttrValFromUrl = $this->getPathParam($mainAttrCode);
            if ($mainAttrValFromUrl) {
                return $mainAttrValFromUrl;
            }
        }

        return null;
    }

    public function filterCollectionByMainEntity($collection, $column, $value = null) {
        $collection->getSelect()->where($column . ' = ' . $value);
        return $collection;
    }

    public function getProductCreationGridFilters($filters) 
    {
        $result = [];

        if ($filters) {
            foreach($filters as $fieldCode) {
                $result[$fieldCode] = $this->getFilterOptions($fieldCode);
            }
        }

        return $result;
    }

    public function getMainCategoriesConfig() 
    {
        $mainAttrCode = $this->request->getParam('main_attr_code');
        if ($mainAttrCode) {
            return $this->mainCategoriesConfigProvider->getByMainAttrCode($mainAttrCode);
        }

        return null;
    }

    

    // process | product creation | listing dataprovider
    public function processGetProductCreationSetsReleaseDataProvider() 
    {
        $isProductCreation = true;

        $customTableProductType = '';

        $categoryName = $this->getProductCreationSetsReleaseCategoryName();
        // dd($categoryName);
        if ($categoryName) {
            $namespace = $this->request->getParam('namespace');

            $mainCategoriesMetadata = $this->getMainCategoriesConfig();
            // if (!$mainCategoriesMetadata) {
            //     return [
            //         "totalRecords" => 0,
            //         "items" => []
            //     ];
            // }

            $mainAttrVal = $this->request->getParam('main_attr_val');
            // dd($mainAttrVal);
            if (!$mainAttrVal) {
                return [
                    "totalRecords" => 0,
                    "items" => []
                ];
            }

            $mainAttrCode = $this->request->getParam('main_attr_code');

            $customTableMetadata = $this->mappingHelper->getCustomTableMetadata(null, $categoryName);

            $collection = $this->getPlatformDatabaseCollection($customTableMetadata, $isProductCreation);
            if ($collection) {
                $collection = $this->filterCollection(
                    $collection, 
                    $customTableMetadata,
                    $isProductCreation
                );
                
                $setId = $this->request->getParam('set_id');
                $isSingles = $this->request->getParam('is_singles');
                $isSealedProducts = $this->request->getParam('is_sealed_products');
                $release = $this->request->getParam('release');
                $namespace = $this->request->getParam('namespace');

                // Filter Sealed Products Sets
                if ($isSealedProducts && str_contains($namespace, "sets_listing")) {
                    $sealedProductsTableName = $customTableMetadata['sealedProductsTableName'] ?? '';
                    if ($sealedProductsTableName) {
                        $collection->getSelect()->join(
                            ['sealedproducts' => $sealedProductsTableName],
                            'main_table.entity_id = sealedproducts.set_id',
                            []
                        )->group('main_table.entity_id');
                    }
                }

                // Filter Singles Sets
                if ($isSingles && str_contains($namespace, "sets_listing")) {
                    $singlesTableName = $customTableMetadata['singlesTableName'] ?? '';

                    $collection->getSelect()->join(
                        ['singles' => $singlesTableName],
                        'main_table.entity_id = singles.set_id',
                        []
                    )->group('main_table.entity_id');
                }

                if ($release) {
                    $releaseAttrCode = $customTableMetadata['mainReleaseAttrCode'];
                    $collection->addFieldToFilter('main_table.' . $releaseAttrCode, $release);
                }

                if ($setId) {
                    $collection->addFieldToFilter('main_table.set_id', $setId);
                }

                $collection = $this->setPagination(
                    $collection
                );

                $collection = $this->setOrderBy(
                    $collection,
                    $customTableMetadata
                );

                $result = $collection->toArray();
            }

            // dd($collection->getSelect()->__toString());

            // $this->cache->save(
            //     serialize($result),
            //     $cacheKey,
            //     [self::CACHE_TAG]
            // );

            return $result;

            // // dd($mainCategoriesMetadata);

            // $customTableMetadata = $this->mappingHelper->getCustomTableMetadata(null, $categoryName);
            // // dd($customTableMetadata);
            // if ($customTableMetadata) {

            //     // Check main entity value on params/url
            //     $mainAttrCode = $customTableMetadata['mainAttrCode'];
            //     $mainAttrVal = $this->getMainEntityVal($mainAttrCode);
            //     // dd($mainAttrVal);
            //     $setId = $this->request->getParam('set_id');
                

            //     $collection = $this->getPlatformDatabaseCollection($customTableMetadata, $isProductCreation);
            //     // dd($collection);
            //     if ($collection) {
            //         $collection = $this->filterCollection(
            //             $collection, 
            //             $customTableMetadata,
            //             $isProductCreation
            //         );

            //         $isSingles = $this->request->getParam('is_singles');
            //         $release = $this->request->getParam('release');
            //         $namespace = $this->request->getParam('namespace');
            //         if (!$isSingles && str_contains($namespace, "sets_listing")) {
            //             $sealedProductsTableName = $customTableMetadata['sealedProductsTableName'] ?? '';
            //             if ($sealedProductsTableName) {
            //                 $collection->getSelect()->join(
            //                     ['sealedproducts' => $sealedProductsTableName],
            //                     'main_table.entity_id = sealedproducts.set_id',
            //                     []
            //                 )->group('main_table.entity_id');
            //             }
            //         }

            //         if ($isSingles && str_contains($namespace, "sets_listing")) {
            //             $singlesTableName = $customTableMetadata['singlesTableName'] ?? '';

            //             $collection->getSelect()->join(
            //                 ['singles' => $singlesTableName],
            //                 'main_table.entity_id = singles.set_id',
            //                 []
            //             )->group('main_table.entity_id');
            //         }

            //         if ($release) {
            //             $releaseAttrCode = $customTableMetadata['mainReleaseAttrCode'];
            //             $collection->addFieldToFilter('main_table.' . $releaseAttrCode, $release);
            //         }

            //         if ($setId) {
            //             $collection->addFieldToFilter('main_table.set_id', $setId);
            //         }

            //         $collection = $this->setPagination(
            //             $collection
            //         );

            //         $collection = $this->setOrderBy(
            //             $collection,
            //             $customTableMetadata
            //         );
                    
            //         $result = $collection->toArray();

            //         // dd($collection->getSelect()->__toString());

            //         // $this->cache->save(
            //         //     serialize($result),
            //         //     $cacheKey,
            //         //     [self::CACHE_TAG]
            //         // );

            //         return $result;
            //     }

                // $namespace = $this->request->getParam('namespace');
                // $isSingles = $this->request->getParam('is_singles');
                // if ($isSingles && str_contains($namespace, '_sets_')) {
                //     $collection = $customTableMetadata['setsCollection'];
                //     $singlesTableName = $customTableMetadata['singlesTableName'];
                //     // dd($singlesTableName);
                //     if ($collection) {
                //         $collection->getSelect()->where(
                //             'EXISTS (SELECT 1 FROM ' . $singlesTableName .' mcgsc WHERE mcgsc.set_id = main_table.entity_id)'
                //         );

                //         return $collection->toArray();
                //     }
                // }
            // }
        }

        return [
            "totalRecords" => 0,
            "items" => []
        ];
    }

    public function setPagination($collection) 
    {
        $paging = $this->request->getParam('paging');
        if ($paging) {
            $pageSize = $paging['pageSize'] ?? 20;
            $current = $paging['current'] ?? 1;

            $collection->setPageSize($pageSize);
            $collection->setCurPage($current);
        }

        return $collection;
    }

    public function setOrderBy($collection, $customTableMetadata) 
    {
        if ($customTableMetadata) {

            $orderBy = null;

            $setsAttrCode = $customTableMetadata['mainSetsAttrCode'] ?? '';
            $singlesAttrCode = $customTableMetadata['mainSinglesAttrCode'] ?? '';

            $namespace = $this->request->getParam('namespace');
            if ($namespace) {
                if (str_contains($namespace, 'sets_listing')) 
                {
                    $collection->setOrder($setsAttrCode, 'ASC');
                    return $collection;
                }

                if (str_contains($namespace, 'singles_listing')) 
                {
                    // $collection->setOrder($singlesAttrCode, 'ASC');
                    return $collection;
                }
            }

            // $refererUrl = $this->getRefererUrl();
            // // dd($refererUrl);
            // if ($refererUrl) {
            // }
        }

        return $collection;
    }

    // PLATFORM DATABASE
    public function getPlatformDatabaseDataProvider($filterAttrCode = false) 
    {
        // dd('ok');
        $isAjax = $this->request->getParam('isAjax');
        if (!$isAjax && !$filterAttrCode) {
            return [
                "totalRecords" => 0,
                "items" => []
            ];
        }
        
         $refererUrl = $this->getRefererUrl();
        // dd($refererUrl);
        if (str_contains($refererUrl, 'attributes/')) {
            return $this->platformDbHelper->getCustomAttributesListingDataProvider();
        }

        $customTableProductType = '';

        $categoryName = $this->getPlatformDatabaseCategoryName();
        if (!$categoryName) {
            // dd('SetsReleaseHelper | getPlatformDatabaseDataProvider | categoryName not found');
        }

        // dd($categoryName);
        if ($categoryName) {
            $customTableMetadata = $this->mappingHelper->getCustomTableMetadata(null, $categoryName);
            if ($customTableMetadata) {

                if (!$customTableMetadata['mainAttrCode']) {
                    // dd('getPlatformDatabaseDataProvider | customTableMetadata not found');
                }

                // dd($customTableMetadata);

                // // Check main entity value on params/url
                // $mainAttrCode = $customTableMetadata['mainAttrCode'];
                // $mainAttrVal = $this->getMainEntityVal($mainAttrCode);
                // if (!$mainAttrVal) {
                //     return [
                //         "totalRecords" => 0,
                //         "items" => []
                //     ];
                // }
                $productType = $this->getProductTypeCode();
                // dd($productType);
                if (!$productType) {
                    $productType = 'index';
                }

                $cacheKey = self::CACHE_TAG . '_' . $this->convertToSlug($categoryName) . '_' . $customTableMetadata['mainAttrCode'] . '_' . $productType;
                if ($filterAttrCode) {
                    $cacheKey .= '_' . $filterAttrCode;
                }
                $cachedData = $this->cache->load($cacheKey);
                if ($cachedData) {
                    // dd('pk');
                    // return unserialize($cachedData);
                }

                // dd($mainAttrCode);

                // SETS/SEALED PRODUCTS/SINGLES/MAIN ENTITY
                $collection = $this->getPlatformDatabaseCollection($customTableMetadata);
                // dd($collection);
                if ($collection) {
                    // dd('ok');
                    $collection = $this->filterCollection(
                        $collection, 
                        $customTableMetadata
                    );

                    // $result = $collection->toArray();

                    // dd($result);

                    // die($collection->getSelect()->__toString());

                    if ($collection) {

                        $collection = $this->setPagination(
                            $collection
                        );

                        $collection = $this->setOrderBy(
                            $collection,
                            $customTableMetadata
                        );

                        // dd($filterAttrCode);

                        if ($filterAttrCode) {
                            $collection->getSelect()->group('main_table.' . $filterAttrCode);
                        }

                        // dd($collection);
                        
                        $result = $collection->toArray();

                        // dd($result);

                        // dd($collection->getSelect()->__toString());

                        $this->cache->save(
                            serialize($result),
                            $cacheKey,
                            [self::CACHE_TAG]
                        );

                        return $result;
                    }
                }
            }
        }

        return [
            "totalRecords" => 0,
            "items" => []
        ];
    }

    // PLATFORM DATABASE | FILTERS
    public function getFilterOptions($attrCode, $tableAlias = 'main_table') 
    {
        // dd($attrCode);
        $options = [];
        $added   = [];

        if ($attrCode) {
            $isProductCreation = $this->request->getParam('isAjax') ? true : false;
            // dd($isProductCreation);
            if ($isProductCreation) {
                $platformDatabaseDataArr = $this->getProductCreationSetsReleaseDataProvider();
            } else {
                $platformDatabaseDataArr = $this->getPlatformDatabaseDataProvider($attrCode);
            }
            // dd($platformDatabaseDataArr);
            if ($platformDatabaseDataArr) {
                foreach ($platformDatabaseDataArr['items'] as $data) {
                    // dd($data);
                    if (isset($data[$attrCode])) {
                        $val = $data[$attrCode];
                        if (!isset($added[$val])) {
                            $options[] = [
                                'label' => $val,
                                'value' => $val
                            ];
                            $added[$val] = true;
                        }
                    }
                }
            }
            
            if ($attrCode == "release_year") {
                usort($options, fn($a, $b) => $b['label'] <=> $a['label']);
            } else {
                usort($options, fn($a, $b) => $a['label'] <=> $b['label']);
            }
        }

        // dd($options);

        return $options;
    }

    public function getProductCreationSetsReleaseCategoryName() 
    {
        $categoryName = null;

        $namespace = $this->request->getParam('namespace');
        if ($namespace) {
            // CARD GAME CATEGORY
            
            
            if (str_contains($namespace, 'actiontoys')) 
            {
                $categoryName = 'Action Toys';
            }

            if (str_contains($namespace, 'anime')) 
            {
                $categoryName = 'Anime Toys';
            }

            if (str_contains($namespace, 'dolls')) 
            {
                $categoryName = 'Fashion Dolls';
            }

            if (str_contains($namespace, 'cardgame')) 
            {
                $categoryName = 'Collectible Card Games';
            }

            if (str_contains($namespace, 'livingcardgame')) 
            {
                $categoryName = 'Living Card Games';
            }

            if (str_contains($namespace, 'miniaturegame')) 
            {
                $categoryName = 'Miniature Games';
            }

            if (str_contains($namespace, 'gamesworkshop')) 
            {
                $categoryName = 'Games Workshop';
            }

            if (str_contains($namespace, 'bricks')) 
            {
                $categoryName = 'Bricks';
            }

            if (str_contains($namespace, 'sportcard')) 
            {
                $categoryName = 'Sport Cards';
            }

            if (str_contains($namespace, 'nonsportcard')) 
            {
                $categoryName = 'Non Sport Cards';
            }

            if (str_contains($namespace, 'diecast')) 
            {
                $categoryName = 'Diecast';
            }

            if (str_contains($namespace, 'modelkits')) 
            {
                $categoryName = 'Model Kits';
            }

            if (str_contains($namespace, 'railway') || str_contains($namespace, 'trains')) 
            {
                $categoryName = 'Trains';
            }

            return $categoryName;
        }

        $refererUrl = $this->getRefererUrl();
        // dd($refererUrl);
        if ($refererUrl) {
            // CARD GAME CATEGORY
            if (str_contains($refererUrl, 'cardgame')) 
            {
                $categoryName = 'Collectible Card Games';
            }

            if (str_contains($refererUrl, 'sportcard')) 
            {
                $categoryName = 'Sport Cards';
            }

            if (str_contains($refererUrl, 'actiontoys')) 
            {
                $categoryName = 'Action Toys';
            }
        }

        return $categoryName;
    }

    public function getSinglesDatabaseSetsGridDataProvider() 
    {
        $categoryName = $this->getPlatformDatabaseCategoryName();
        if ($categoryName) {
            $customTableMetadata = $this->mappingHelper->getCustomTableMetadata(null, $categoryName);
            if ($customTableMetadata) {
                $collection = $this->getPlatformDatabaseCollection($customTableMetadata);
                // dd($collection);
                if ($collection) {
                    $collection = $this->filterCollection(
                        $collection, 
                        $customTableMetadata
                    );
                    return $collection;
                }
            }
        }

        return null;
    }

    // SELLER | PLATFORM DATABASE | APPLY GRID FILTER
    public function applyFilters($collection, $customTableMetadata, $productTypeCode) 
    {
        $filters = $this->request->getParam('filters') ?? [];
        $grid_filters = $this->request->getParam('grid_filters') ?? [];

        $filters = array_merge($filters, $grid_filters);
        if ($filters) {
            $mainAttrCode = $customTableMetadata['mainAttrCode'] ?? null;
            $allowedFields = null;

            if ($productTypeCode == "sealedproducts") {
                $allowedFields = $this->mappingHelper->getSealedProductsAllowedFields($mainAttrCode);
            }

            if ($productTypeCode == "singles") {
                $allowedFields = $this->mappingHelper->getSinglesAllowedFields($mainAttrCode);
            }

            // dd($allowedFields);

            // if ($productTypeCode == "sealedproducts") {
            //     $allowedFields = $this->mappingHelper->getSealedProductsAllowedFields($mainAttrCode);
            // }
            
            $inputTypesAttributeCodes = $this->getInputTypesAttributeCodes();

            // dd($allowedFields);
            foreach($filters as $column => $value) {
                if ($column == "placeholder") {
                    continue;
                }

                $dbColumn = 'main_table.' . $column;

                // dd($allowedFields);

                if ($allowedFields) {
                    if (!in_array($column, $allowedFields)) {
                        $dbColumn = 'sets.' . $column;
                    }
                }
                
                if (!$productTypeCode) {
                    $dbColumn = 'eaov.value';
                }

                // dd($dbColumn);

                if (in_array($column, $inputTypesAttributeCodes) || $column == $mainAttrCode) {
                    $collection->addFieldToFilter(
                        $dbColumn,
                        [
                            ['like' => strtolower($value) . '%'],
                            ['like' => '%' . strtolower($value)],
                            ['like' => '%' . strtolower($value) . '%']
                        ]
                    );
                } else {
                    $collection->addFieldToFilter($dbColumn, ['eq' => $value]);
                }
            }
        }

        return $collection;
        // if ($filter->getField() === 'is_published') {
        //     $collection->addFilterToMap('is_published', 'cpei.value');
            
        //     $collection->getSelect()->where(
        //         "cpei.value = ?",
        //         $filter->getValue()
        //     );
            
        //     if ($filter->getValue() != 1) {
        //         $collection->getSelect()->orWhere(
        //             "cpei.value IS NULL"
        //         );
        //     }

        //     return $collection;
        // }

        // $fieldCode = $filter->getField();
        // $value = $filter->getValue();

        // if ($fieldCode == "website_ids") {
        //     $collection->addWebsiteFilter($value);

        //     return $collection;
        // }

        // if (!is_array($value)) {
        //     $attrCode = $this->getAttributeCodesByOptionIds($value);

        //     $attrCode = $attrCode[$value] ?? null;

        //     if ($attrCode) {
        //         $collection->addAttributeToFilter($attrCode, ['eq' => $value]);

        //         return $collection;
        //     }
        // }

        // return null;
    }

    public function filterCollection($collection, $customTableMetadata, $isProductCreation = false) 
    {
        $sealedProductsTableName = $customTableMetadata['sealedProductsTableName'] ?? '';
        $singlesTableName = $customTableMetadata['singlesTableName'] ?? '';
        $setsTableName = $customTableMetadata['setsTableName'] ?? '';
        $setsAttrCode = $customTableMetadata['mainSetsAttrCode'] ?? '';
        $mainReleaseAttrCode = $customTableMetadata['mainReleaseAttrCode'] ?? '';
        $mainAttrCode = $customTableMetadata['mainAttrCode'] ?? '';
        $setsFieldToSelectFromSingles = $customTableMetadata['setsFieldToSelectFromSingles'] ?? [];
        $setsFieldToSelectFromSealedProducts = $customTableMetadata['setsFieldToSelectFromSealedProducts'] ?? [];

        $mainAttrVal = $this->getMainEntityVal($mainAttrCode);

        $productTypeCode = $this->getProductTypeCode($isProductCreation);
        // dd($productTypeCode);
        $setId = $this->request->getParam('set_id');
        if ($productTypeCode == "singles" && !$setId) {
            $productTypeCode = null;
        }

        if ($productTypeCode == "sealedproducts" && !$mainAttrVal) {
            $productTypeCode = null;
        }
        // dd($productTypeCode);
        if ($productTypeCode) {
            
            // dd($mainAttrVal);
            // if (!$mainAttrVal && $productTypeCode != "singles") {
            //     return null;
            // }
            if ($productTypeCode == "singles_sets" || $productTypeCode == "sets") {
                if ($productTypeCode == "singles_sets") {
                    if ($singlesTableName) {
                        $collection->getSelect()->join(
                            ['singles' => $singlesTableName],
                            'main_table.entity_id=singles.set_id',
                            []
                        )->group('main_table.entity_id');

                        // Map columns
                        $collection->addFilterToMap('release_year', 'main_table.release_year');
                        $collection->addFilterToMap('language_version', 'main_table.language_version');
                        $collection->addFilterToMap('card_series', 'main_table.card_series');
                        $collection->addFilterToMap('card_release', 'main_table.card_release');
                        $collection->addFilterToMap('release_type', 'main_table.release_type');
                        $collection->addFilterToMap('card_set', 'main_table.card_set');
                    }

                    if ($mainAttrCode) {
                        $mainAttrVal = $this->request->getParam($mainAttrCode);
                        if (!$mainAttrVal) {
                            $mainAttrVal = $this->getPathParam($mainAttrCode);
                        }

                        if ($mainAttrVal) {
                            $collection->getSelect()->where('main_table.' . $mainAttrCode .' = ' . $mainAttrVal);
                        }
                    }

                    // Ordering collection
                    $collection = $this->orderPlatformDatabaseCollection($collection, $productTypeCode, $customTableMetadata);
                }

                if ($productTypeCode == "sets") {
                    if ($mainAttrCode) {
                        $mainAttrVal = $this->request->getParam($mainAttrCode);
                        if (!$mainAttrVal) {
                            $mainAttrVal = $this->getPathParam($mainAttrCode);
                        }

                        if ($mainAttrVal) {
                            $collection->getSelect()->where('main_table.' . $mainAttrCode .' = ' . $mainAttrVal);
                        }
                    }
                }
            }

            // dd($productTypeCode);
            
            // PLATFORM DATABASE | SEALED PRODUCTS COLLECTION FILTERS
            if ($productTypeCode == "sealedproducts" || $productTypeCode == "release") {
                // dd($setsTableName);
                if ($setsTableName) {
                    if ($mainAttrCode) {
                        $setsColumnsToSelect = $this->getSetsTableColumnToSelect($collection, $customTableMetadata, $productTypeCode);

                        // dd($setsColumnsToSelect);

                        $collection->getSelect()->join(
                            ['sets' => $setsTableName],
                            'main_table.' . $mainReleaseAttrCode . '=sets.' . $mainReleaseAttrCode,
                            $setsColumnsToSelect
                        );

                        $column = 'sets.' . $mainAttrCode;
                        $this->filterCollectionByMainEntity($collection, $column, $mainAttrVal);
                    }
                }

                // Ordering collection
                $collection = $this->orderPlatformDatabaseCollection($collection, $productTypeCode, $customTableMetadata);

                if ($productTypeCode == "release") {
                    $collection->getSelect()->group('main_table.' . $mainReleaseAttrCode)->order('main_table.' . $mainReleaseAttrCode . ' DESC');
                } else {
                    $collection->getSelect()->group('main_table.entity_id');
                }
            }

            // PLATFORM DATABASE | SINGLES COLLECTION FILTERS
            if ($productTypeCode == "singles") {
                // dd($setsTableName);
                if ($setsTableName) {
                    // dd($mainAttrCode);
                    if ($mainAttrCode) {
                        $mainAttrVal = $this->request->getParam($mainAttrCode);
                        if (!$mainAttrVal) {
                            $mainAttrVal = $this->getPathParam($mainAttrCode);
                        }

                        // dd($mainAttrVal);

                        if ($mainAttrVal) {
                            $collection->getSelect()->where('sets.' . $mainAttrCode .' = ' . $mainAttrVal);
                        }
                    }

                    // if ($mainAttrCode) {
                        $setId = $this->request->getParam('set_id');
                        if (!$setId) {
                            $setId = $this->getPathParam('set_id');
                        }

                        // dd($setId);
                    // }
                    
                    if ($setId) {
                        $collection->getSelect()->where('main_table.set_id = ' . $setId);
                    }
// dd($setsFieldToSelectFromSingles);
                    $collection->getSelect()->join(
                        ['sets' => $setsTableName],
                        'main_table.set_id=sets.entity_id',
                        $setsFieldToSelectFromSingles
                    )->group('main_table.entity_id');
                }

                
                // dd($collection->toArray());

                // Ordering collection
                $collection = $this->orderPlatformDatabaseCollection($collection, $productTypeCode, $customTableMetadata);
            }
        } else {
            // dd('ok');
            // dd($mainAttrCode);
            // dd($setsTableName);
            if (!$setsTableName) {
                dd('setsTableName is missing');
            }
            $collection->getSelect()->join(
                ['eav_attribute' => 'eav_attribute'],
                'eav_attribute.attribute_id = main_table.attribute_id',
                []
            )->where('eav_attribute.attribute_code = "'.$mainAttrCode.'"');

            $collection->getSelect()->join(
                ['eaov' => 'eav_attribute_option_value'],
                'main_table.option_id = eaov.option_id',
                [$mainAttrCode => 'eaov.value']
            );
            
            $collection->getSelect()->join(
                ['sets' => $setsTableName],
                'main_table.option_id = sets.' . $mainAttrCode,
                []
            );

            $refererUrl = $this->getRefererUrl();
            if ($refererUrl) {

                if (str_contains($refererUrl, "singles")) {
                    $collection->getSelect()->join(
                        ['singles' => $singlesTableName],
                        'sets.entity_id = singles.set_id',
                        []
                    );
                }

                // if (!$sealedProductsTableName) {
                //     dd('sealedProductsTableName is missing');
                // }

                if (!$mainReleaseAttrCode) {
                    // dd('mainReleaseAttrCode is missing');
                }
                // dd($mainReleaseAttrCode);

                if (str_contains($refererUrl, "sealedproducts")) {
                    $collection->getSelect()->join(
                        ['sealedproducts' => $sealedProductsTableName],
                        'sets.' . $mainReleaseAttrCode . ' = sealedproducts.' . $mainReleaseAttrCode,
                        []
                    );
                }
            }
            
            $collection->getSelect()->group('main_table.option_id')->order('eaov.value ASC');

            $collection->addFilterToMap($mainAttrCode, 'eaov.value');
        }

        // dd($collection->getSelect()->__toString());

        $collection = $this->applyFilters($collection, $customTableMetadata, $productTypeCode);

        return $collection;
    }

    public function getSetsGenericColumns() {
        return ['entity_id', 'images','name','supplier_sku','sku'];
    }

    public function getSetsTableColumnToSelect($collection, $customTableMetadata, $customTableType) 
    {
        $sealedProductsTableName = $customTableMetadata['sealedProductsTableName'] ?? '';
        $singlesTableName = $customTableMetadata['singlesTableName'] ?? '';
        $setsTableName = $customTableMetadata['setsTableName'] ?? '';
        // $setsAttrCode = $customTableMetadata['mainSetsAttrCode'] ?? '';
        // $mainReleaseAttrCode = $customTableMetadata['mainReleaseAttrCode'] ?? '';
        // $mainAttrCode = $customTableMetadata['mainAttrCode'] ?? '';
        // $setsFieldToSelectFromSingles = $customTableMetadata['setsFieldToSelectFromSingles'] ?? [];
        // $setsFieldToSelectFromSealedProducts = $customTableMetadata['setsFieldToSelectFromSealedProducts'] ?? [];

        if (!$setsTableName) {
            return [];
        }

        $connection = $this->resource->getConnection();

        // Get sets table
        $setsTable = $collection->getTable($setsTableName);

        // Get list of columns under sets table
        $setsTableColumns = array_keys($connection->describeTable($setsTable));

        // dd($setsTableColumns);
        
        $columnsToSelectFromSets = array_filter(
            $setsTableColumns, fn($col) => !in_array($col, $this->getSetsGenericColumns())
        );

        // dd($columnsToSelectFromSets);

        if ($customTableType == "singles") {
            // Get singles table
            $singlesTable = $collection->getTable($singlesTableName);

            // Get list of columns under singles table
            $singlesTableColumns = array_keys($connection->describeTable($singlesTable));
        }

        if ($customTableType == "sealedproducts") {
            // Get singles table
            $sealedProductsTable = $collection->getTable($sealedProductsTableName);

            // Get list of columns under singles table
            $sealedProductsTableColumns = array_keys($connection->describeTable($sealedProductsTable));

            // dd($sealedProductsTableColumns);

            $columnsToSelectFromSets = array_filter(
                $columnsToSelectFromSets, fn($col) => !in_array($col, $sealedProductsTableColumns)
            );
        }

        // dd($columnsToSelectFromSets);
        
        return $columnsToSelectFromSets;
    }

    public function getPathParam($key) {
        $url = $this->getRefererUrl();

        $path = parse_url($url, PHP_URL_PATH);
        $segments = explode('/', trim($path, '/'));

        $index = array_search($key, $segments);
        if ($index !== false && isset($segments[$index + 1])) {
            return $segments[$index + 1];
        }
        return null;
    }
    
    public function orderPlatformDatabaseCollection($collection, $productTypeCode, $customTableMetadata) 
    {
        if ($productTypeCode == "singles_sets") {
            $ordering = $customTableMetadata['setsOrdering'] ??  [];

            $collection->getSelect()->order($ordering);
        }

        if ($productTypeCode == "sealedproducts") {
            $ordering = $customTableMetadata['sealedProductsOrdering'] ??  [];

            $collection->getSelect()->order($ordering);
        }

        return $collection;
    }

    public function getPlatformDatabaseCollection($customTableMetadata, $isProductCreation = false) 
    {
        // dd('ok');
        $data = [];

        $collection = null;
        
        $productType = $this->getProductTypeCode($isProductCreation);
        // dd($productType);
        // dd($customTableMetadata);
        $setId = $this->request->getParam('set_id');
        $mainAttrCode = $customTableMetadata['mainAttrCode'] ?? null;
        $mainAttrVal = $this->getMainEntityVal($mainAttrCode);
        // dd($productType);
        // dd($mainAttrVal);
        if ($productType == 'singles' && !$setId) {
            return null;
        }
        // dd($setId);
        if ($productType) {
            if ($productType == 'singles' && $setId) {
                $collection = $customTableMetadata['singlesCollection'] ?? null;
                return $collection;
            }

            if ($productType == 'sets' || $productType == 'singles_sets') {
                $collection = $customTableMetadata['setsCollection'] ?? null;
                return $collection;
            }

            if ($productType == 'release' || $productType == 'sealedproducts' && $mainAttrVal) {
                $collection = $customTableMetadata['sealedProductsCollection'] ?? null;
                return $collection;
            }
        }
        
        $collection = $this->attributeOptionsCollectionFactory->create();
        return $collection;
    }
    
    public function downloadMyProductsAsCsv() {
        $startTime = microtime(true);

        $platformDatabaseToExport = [];

        $attributeSetId = null;
        $attributeSetName = null;

        $productType = $this->getProductType(true);

        $categoryName = $this->getPlatformDatabaseCategoryName();
        if ($categoryName) {
            $customTableMetadata = $this->mappingHelper->getCustomTableMetadata(null, $categoryName);
            if ($customTableMetadata) {

                $mainAttrCode = $customTableMetadata['mainAttrCode'] ?? null;

                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $this->productCollectionFactory = $objectManager->create(\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory::class);
                $this->helper = $objectManager->create(\TCGCollective\MarketPlace\Helper\Catalog::class);

                $productGroupByMainAttr = [];

                $collection = $this->productCollectionFactory->create();
                if ($collection) {
                    $collection = $this->helper->filterSellerCatalogProductDataProvider($collection, $this->getProductType(false));

                    $collection->addFieldToSelect('name');
                    // $collection->addAttributeToSelect('supplier_sku');
                    // $collection->addAttributeToSelect('base_image');
                    // $collection->addAttributeToSelect('price');
                    // $collection->addAttributeToSelect('price');
                    $collection->addAttributeToSelect(['*']);
                    // $collection->addAttributeToSelect('stock_data');
                }

                // if ($collection->getSize()) {
                //     foreach($collection as $model) {
                //         if ($model->getIsPublished() == 1) {
                //         dd($model->getData());
                //         }
                //     }
                // }

                $data = $collection->toArray();

                // dd(end($data));

                $grouped = array_reduce(
                    array_map(function ($item) {
                        $item['publish_status'] = match ($item['is_published']) {
                            "1" => 1,
                            "2" => 0,
                            default => 0,
                        };
                        return $item;
                    }, $data),
                    function ($carry, $item) {
                        $carry[$item['attribute_set_id']][] = $item;
                        return $carry;
                    },
                    []
                );
                
                foreach ($grouped as $attributeSetId => $items) {
                    // Get attribute codes on attribute set
                    if ($attributeSetId) {
                        $attributeSetExportData = $this->platformDatabaseCache->getAttributeSetData($attributeSetId);
                        if ($attributeSetExportData) {

                            // dd($attributeSetExportData);

                            $endTime = microtime(true);
                            $executionTime = $endTime - $startTime;

                            $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/CATALOGexecutionTimeexecutionTime.log');
                            $logger = new \Zend_Log();
                            $logger->addWriter($writer);
                            $logger->info('CATALOG CSV DOWNLOAD executionTime: '.$executionTime);

                            return $this->setCatalogExportedFieldsData($categoryName, $productType, $attributeSetExportData, $customTableMetadata, $items);
                        }
                    }
                }

                // dd($grouped);
                // dd($collection->getData());
            }
        }

        return [
            'success' => false,
            'name' => '',
            'file' => null,
            'refererUrl' => $this->getRefererUrl()
        ];
    }

    public function setCatalogExportedFieldsData($categoryName, $productType, $attributeSetExportData, $customTableMetadata, $catalogDataToExport) 
    {
        $startTime = microtime(true);

        $url = $this->getRefererUrl();

        // dd($attributeSetExportData);

        if ($customTableMetadata && $attributeSetExportData) {
            $exportAttributeCodes = $attributeSetExportData['exportAttributeCodes'] ?? [];
            $attributeSetName = $attributeSetExportData['attributeSetName'] ?? [];
            $dataAttributes = $attributeSetExportData['dataAttributes'] ?? [];

            $mainAttrCode = $customTableMetadata['mainAttrCode'];
            $mainSetsAttrCode = $customTableMetadata['mainSetsAttrCode'];
            $mainSinglesAttrCode = $customTableMetadata['mainSinglesAttrCode'];
            $singlesCollection = $customTableMetadata['singlesCollection'];
            $setsCollection = $customTableMetadata['setsCollection'];
            $setsTableName = $customTableMetadata['setsTableName'];
            $setsFieldToSelet = $customTableMetadata['setsFieldToSelet'];
            $mainSinglesNumberAttrCode = $customTableMetadata['mainSinglesNumberAttrCode'];
            $mainSetsNumberAttrCode = $customTableMetadata['mainSetsNumberAttrCode'];

            $setId = null;
            if ($url) {
                if (preg_match('/set_id\/(\d+)/', $url, $matches)) {
                    $setId = $matches[1]; // The first capturing group contains the set_id
                }
            }

            if ($catalogDataToExport) {
                $productsToExport = [];
                
                $defaultWebsite = $this->storeManager->getStore()->getWebsite()->getName();
                $mainAttrVal = null;
                $prefix = null;

                foreach($catalogDataToExport as $index => $rawProductData) {
                    $productData = [];

                    // PUBLISH DATA
                    $productData['publish_status'] = 0;
                    $productData['listing_country'] = $defaultWebsite;
                    $productData['price'] = 0;
                    $productData['qty'] = 0;

                    // PRODUCT DETAILS

                    // dd($rawProductData);
                    

                    $cardName = $rawProductData[$mainSinglesAttrCode] ?? null;
                    $setsName = $rawProductData[$mainSetsAttrCode] ?? null;

                    // Set main attr val
                    if (!$mainAttrVal) {
                        $mainAttrVal = $this->mappingHelper->getOptionLabelById(
                            $mainAttrCode, 
                            $rawProductData[$mainAttrCode]
                        );
                    }

                    $productData[$mainAttrCode] = $mainAttrVal ? trim($mainAttrVal) : '';

                    $mainAttrVal = $rawProductData[$mainAttrCode] ?? null;

                    // dd($productData);

                    // Set product name
                    $name = $rawProductData['name'] ?? '';
                    $productData['name'] = trim($name);

                    // Set product supplier_sku
                    $supplier_sku = $rawProductData['supplier_sku'] ?? '';
                    $productData['supplier_sku'] = trim($supplier_sku);

                    // $skuArr = [];

                    // if ($name) {
                    //     $skuArr[] = trim(strtoupper($this->convertToSlug($name)));
                    // }

                    // $skuArr[] = strtoupper($this->makeid(10));

                    // Set seller SKU
                    // $productData['supplier_sku'] = implode("-", $skuArr);

                    $imagesData = '';
                    $thumbData = '';
                    
                    // $imagesDataRaw = $rawProductData['images'] ?? null;
                    // if ($imagesDataRaw) {
                    //     $imagesDataArr = explode(",", $imagesDataRaw);
                    //     if ($imagesDataArr) {
                    //         $newImagesDataArr = [];
                    //         foreach($imagesDataArr as $key => $imageWithPath) {
                    //             if ($imageWithPath) {
                    //                 if ($key == 0) {
                    //                     $thumbData = basename($imageWithPath);
                    //                 } else {
                    //                     $newImagesDataArr[] = basename($imageWithPath);
                    //                 }
                    //             }
                    //         }

                    //         if ($newImagesDataArr) { 
                    //             $imagesData = implode(",", $newImagesDataArr);
                    //         }
                    //     }
                    // }
                    
                    // IMAGES
                    $productData['base_image'] = $rawProductData['image'] ?? '';
                    $productData['additional_images'] = '';
                    $productData['price'] = $rawProductData['price'] ? rtrim(rtrim($rawProductData['price'], '0'), '.') : 0;

                    // Get prefix
                    if (!$prefix) {
                        $categoryId = $productData['category_ids'] ?? null;
                        $prefix = $this->mappingHelper->getPrefixByCategoryId($categoryId, null, $mainAttrVal, $mainAttrCode);
                    }

                    $mainSetsAttrVal = $rawProductData[$mainSetsAttrCode] ?? '';
                    $mainSealedProductsVal = $rawProductData['card_release'] ?? '';

                    // SETS / SINGLES DATA
                    foreach($exportAttributeCodes as $attrCode) {

                        if ($attrCode == $mainAttrCode) {
                            continue;
                        }
                        
                        $attrCodeWithoutPrefix = null;

                        if ($prefix && str_contains($attrCode, $prefix)) {
                            $attrCodeWithoutPrefix = str_replace($prefix, "", $attrCode);
                        }
                        
                        if ($attrCodeWithoutPrefix) {
                            $value = $rawProductData[$attrCodeWithoutPrefix] ?? '';
                    //         if ($value) {
                    //             try {
                    //                 $this->mappingHelper->getOrInputAttributeOptionId($attrCode, $value);
                    //             } catch(\Exception $e) {}
                    //         }
                            
                            $rawProductData[$attrCode] = $value ? trim($value) : '';
                        } else {
                            $value = $rawProductData[$attrCode] ?? '';
                            if ($value) {
                    //             try {
                    //                 $this->mappingHelper->getOrInputAttributeOptionId($attrCode, $value);
                    //             } catch(\Exception $e) {}
                                $productData[$attrCode] = $value ? trim($value) : '';
                            } else {
                                if (!isset($rawProductData[$attrCode])) {
                                    if ($attrCode == "price" || $attrCode == "publish_status") {
                                        $productData[$attrCode] = 0;
                                    }
                                    $productData[$attrCode] = '';
                                }
                            }
                        }

                        if (isset($productData[$attrCode]) && $productData[$attrCode]) {
                            $attributeData = $dataAttributes[array_search($attrCode, array_column($dataAttributes, 'attribute_code'))] ?? null;
                            if ($attributeData) { 
                                $optionsData = $attributeData['optionsData'] ?? null;
                                if ($optionsData) {
                                    $option = $optionsData[array_search($productData[$attrCode], array_column($optionsData, 'value'))] ?? null;
                                    if ($option) {
                                        $productData[$attrCode] = $option['label'];
                                    }
                                }
                            }
                        }
                    }

                    $productData = $this->removeUnusedAttributes($productData);
                    
                    // Set product type
                    $productData['categories'] = $categoryName;
                    $productData['card_product_type'] = $productType;

                    
                    // dd($productData);

                    $productsToExport[] = $productData;
                }

                $header = array_keys($productsToExport[0]);

                array_unshift($productsToExport, $header);

                if ($productsToExport) {

                    
                    $endTime = microtime(true);
                    $executionTime = $endTime - $startTime;

                    $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/executionTimeexecutionTime.log');
                    $logger = new \Zend_Log();
                    $logger->addWriter($writer);
                    $logger->info('setExportedFieldsData executionTime: '.$executionTime);

                    return $this->exportProductAsCsv($header, $productsToExport, $mainAttrCode, $mainAttrVal, $mainSetsAttrVal, $mainSealedProductsVal);
                }
            }
        }

        return null;
    }
    
    /** Export Platform Database Singles/Sealed Products as CSV for Bulk Upload */
    public function downloadPlatformDatabaseAsCsvForBulkUpload() {
        $startTime = microtime(true);

        $platformDatabaseToExport = [];

        $attributeSetId = null;
        $attributeSetName = null;

        $productType = $this->getProductType(true);

        $categoryName = $this->getPlatformDatabaseCategoryName();
        // dd($categoryName);
        if ($categoryName) {
            $customTableMetadata = $this->mappingHelper->getCustomTableMetadata(null, $categoryName);
            if ($customTableMetadata) {
                $platformDatabaseToExport = $this->getCustomTableDataProvider($customTableMetadata);
                if ($platformDatabaseToExport) {
                    $dataForMapping = $platformDatabaseToExport[0] ?? [];
                    if ($dataForMapping) {
                        // Temp Set product type for mapping query purpose
                        $dataForMapping['card_product_type'] = $productType;

                        // dd($dataForMapping);

                        $attributeSetMappingData = $this->platformDatabaseCache->getAttributeSetMappingDataByCategoryName(
                            $dataForMapping,
                            $categoryName
                        );
                        if ($attributeSetMappingData) {
                            $attributeSetId = $attributeSetMappingData['attribute_set_id'] ?? null;
                            $attributeSetName = $attributeSetMappingData['attribute_set_name'] ?? null;
                        }
                    }
                }
            }
        }

        // Get attribute codes on attribute set
        if ($attributeSetId) {
            // dd($attributeSetId);
            $attributeSetExportData = $this->platformDatabaseCache->getAttributeSetData($attributeSetId);
            if ($attributeSetExportData) {

                // dd($attributeSetExportData);

                $endTime = microtime(true);
                $executionTime = $endTime - $startTime;

                $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/executionTimeexecutionTime.log');
                $logger = new \Zend_Log();
                $logger->addWriter($writer);
                $logger->info('downloadPlatformDatabaseAsCsvForBulkUpload executionTime: '.$executionTime);

                return $this->setExportedFieldsData($categoryName, $productType, $attributeSetExportData, $customTableMetadata, $platformDatabaseToExport);
            }
        }

        return [
            'success' => false,
            'name' => '',
            'file' => null,
            'refererUrl' => $this->redirect->getRefererUrl()
        ];
    }

    public function getCustomTableDataProvider($customTableMetadata) 
    {
        $data = [];

        $collection = null;

        $refererUrl = $this->getRefererUrl();

        // Card Game | Custom Table
        if (str_contains($refererUrl, 'cardgame')) {
            if (str_contains($refererUrl, 'singles')) {
                $collection = $customTableMetadata['singlesCollection'] ?? null;
            }
        }

        if ($collection) {
            $setsTableName = $customTableMetadata['setsTableName'] ?? null;
            $setsFieldToSelet = $customTableMetadata['setsFieldToSelet'] ?? [];

            $setsFieldToSelet = array_filter($setsFieldToSelet, function($value) {
                return $value != "supplier_sku" && $value != "name";
            });

            // dd($setsFieldToSelet);

            // Get main attribute code value
            $setId = null;
            if ($refererUrl) {
                if (preg_match('/set_id\/(\d+)/', $refererUrl, $matches)) {
                    $setId = $matches[1]; // The first capturing group contains the set_id
                }
            }
            
            if ($setId) {
                if ($setsTableName) {
                    // dd($setsFieldToSelet);
                    $collection->getSelect()->joinLeft(
                        ['sets' => $setsTableName],
                        'main_table.set_id=sets.entity_id',
                        $setsFieldToSelet
                    );

                    $collection->addFieldToFilter('sets.entity_id', $setId);
                }
            }

            if ($collection->getSize()) {
                return $collection->getData();
            }
        }

        return $data;
    }

    public function contains($str, array $arr)
    {
        foreach($arr as $a) {
            if (stripos($str,$a) !== false) return true;
        }

        return false;
    }

    public function makeId($length) 
    {
        $result = '';
        $characters = 'abcdefghijklmnopqrstuvwxyz0123456789';
        $charactersLength = strlen($characters);
        for ($i = 0; $i < $length; $i++) {
            $result .= $characters[rand(0, $charactersLength - 1)];
        }

        return $result;
    }    

    public function convertToSlug($text) 
    {
        return strtolower(
            preg_replace(
                ['/[^a-zA-Z0-9 -]/', '/[ -]+/', '/^-|-$/'],
                ['', '-', ''],
                trim($text)
            )
        );
    }

    public function setExportedFieldsData($categoryName, $productType, $attributeSetExportData, $customTableMetadata, $platformDatabaseToExport) 
    {
        $startTime = microtime(true);

        $url = $this->getRefererUrl();

        // dd($attributeSetExportData);

        if ($customTableMetadata && $attributeSetExportData) {
            $exportAttributeCodes = $attributeSetExportData['exportAttributeCodes'] ?? [];
            $attributeSetName = $attributeSetExportData['attributeSetName'] ?? [];

            $mainAttrCode = $customTableMetadata['mainAttrCode'];
            $mainSetsAttrCode = $customTableMetadata['mainSetsAttrCode'];
            $mainSinglesAttrCode = $customTableMetadata['mainSinglesAttrCode'];
            $singlesCollection = $customTableMetadata['singlesCollection'];
            $setsCollection = $customTableMetadata['setsCollection'];
            $setsTableName = $customTableMetadata['setsTableName'];
            $setsFieldToSelet = $customTableMetadata['setsFieldToSelet'];
            $mainSinglesNumberAttrCode = $customTableMetadata['mainSinglesNumberAttrCode'];
            $mainSetsNumberAttrCode = $customTableMetadata['mainSetsNumberAttrCode'];

            $setId = null;
            if ($url) {
                if (preg_match('/set_id\/(\d+)/', $url, $matches)) {
                    $setId = $matches[1]; // The first capturing group contains the set_id
                }
            }

            if ($platformDatabaseToExport) {
                $productsToExport = [];
                
                $defaultWebsite = $this->storeManager->getStore()->getWebsite()->getName();
                $mainAttrVal = null;
                $prefix = null;

                foreach($platformDatabaseToExport as $index => $platformDatabaseData) {
                    $productData = [];

                    // PUBLISH DATA
                    $productData['publish_status'] = 0;
                    $productData['listing_country'] = $defaultWebsite;
                    $productData['price'] = 0;
                    $productData['qty'] = 0;

                    // PRODUCT DETAILS

                    // dd($platformDatabaseData);
                    

                    $cardName = $platformDatabaseData[$mainSinglesAttrCode] ?? null;
                    $setsName = $platformDatabaseData[$mainSetsAttrCode] ?? null;

                    // Set main attr val
                    if (!$mainAttrVal) {
                        $mainAttrVal = $this->mappingHelper->getOptionLabelById(
                            $mainAttrCode, 
                            $platformDatabaseData[$mainAttrCode]
                        );
                    }

                    $productData[$mainAttrCode] = $mainAttrVal ? trim($mainAttrVal) : '';

                    $mainAttrVal = $productData[$mainAttrCode] ?? null;

                    // dd($platformDatabaseData);

                    // Set product name
                    $name = $platformDatabaseData['name'] ?? '';
                    $productData['name'] = trim($name);

                    // Set product supplier_sku
                    $supplier_sku = $platformDatabaseData['supplier_sku'] ?? '';
                    $productData['supplier_sku'] = trim($supplier_sku);

                    // $skuArr = [];

                    // if ($name) {
                    //     $skuArr[] = trim(strtoupper($this->convertToSlug($name)));
                    // }

                    // $skuArr[] = strtoupper($this->makeid(10));

                    // Set seller SKU
                    // $productData['supplier_sku'] = implode("-", $skuArr);

                    $imagesData = '';
                    $thumbData = '';
                    
                    $imagesDataRaw = $platformDatabaseData['images'] ?? null;
                    if ($imagesDataRaw) {
                        $imagesDataArr = explode(",", $imagesDataRaw);
                        if ($imagesDataArr) {
                            $newImagesDataArr = [];
                            foreach($imagesDataArr as $key => $imageWithPath) {
                                if ($imageWithPath) {
                                    if ($key == 0) {
                                        $thumbData = basename($imageWithPath);
                                    } else {
                                        $newImagesDataArr[] = basename($imageWithPath);
                                    }
                                }
                            }

                            if ($newImagesDataArr) { 
                                $imagesData = implode(",", $newImagesDataArr);
                            }
                        }
                    }
                    
                    // IMAGES
                    $productData['base_image'] = (string)trim($thumbData);
                    $productData['additional_images'] = (string)trim($imagesData);

                    // Get prefix
                    if (!$prefix) {
                        $categoryId = $productData['category_ids'] ?? null;
                        $prefix = $this->mappingHelper->getPrefixByCategoryId($categoryId, null, $mainAttrVal, $mainAttrCode);
                    }

                    $mainSetsAttrVal = $platformDatabaseData[$mainSetsAttrCode] ?? '';
                    $mainSealedProductsVal = $platformDatabaseData['card_release'] ?? '';

                    // SETS / SINGLES DATA
                    foreach($exportAttributeCodes as $attrCode) {

                        if ($attrCode == $mainAttrCode) {
                            continue;
                        }
                        
                        $attrCodeWithoutPrefix = null;

                        if ($prefix && str_contains($attrCode, $prefix)) {
                            $attrCodeWithoutPrefix = str_replace($prefix, "", $attrCode);
                        }
                        
                        if ($attrCodeWithoutPrefix) {
                            $value = $platformDatabaseData[$attrCodeWithoutPrefix] ?? '';
                    //         if ($value) {
                    //             try {
                    //                 $this->mappingHelper->getOrInputAttributeOptionId($attrCode, $value);
                    //             } catch(\Exception $e) {}
                    //         }
                            
                            $productData[$attrCode] = $value ? trim($value) : '';
                        } else {
                            $value = $platformDatabaseData[$attrCode] ?? '';
                            if ($value) {
                    //             try {
                    //                 $this->mappingHelper->getOrInputAttributeOptionId($attrCode, $value);
                    //             } catch(\Exception $e) {}
                                $productData[$attrCode] = $value ? trim($value) : '';
                            } else {
                                if (!isset($productData[$attrCode])) {
                                    if ($attrCode == "price" || $attrCode == "publish_status") {
                                        $productData[$attrCode] = 0;
                                    }
                                    $productData[$attrCode] = '';
                                }
                            }
                        }
                    }

                    $productData = $this->removeUnusedAttributes($productData);
                    
                    // Set product type
                    $productData['categories'] = $categoryName;
                    $productData['card_product_type'] = $productType;

                    $productsToExport[] = $productData;
                }

                $header = array_keys($productsToExport[0]);

                array_unshift($productsToExport, $header);

                if ($productsToExport) {

                    
                    $endTime = microtime(true);
                    $executionTime = $endTime - $startTime;

                    $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/executionTimeexecutionTime.log');
                    $logger = new \Zend_Log();
                    $logger->addWriter($writer);
                    $logger->info('setExportedFieldsData executionTime: '.$executionTime);

                    return $this->exportProductAsCsv($header, $productsToExport, $mainAttrCode, $mainAttrVal, $mainSetsAttrVal, $mainSealedProductsVal);
                }
            }
        }

        return null;
    }

    public function exportProductAsCsv($headers, $productData, $mainAttrCode, $mainAttrVal, $mainSetsAttrVal, $mainSealedProductsVal = null) 
    {
        $startTime = microtime(true);

        $this->directory = $this->filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);

        $result = [
            'success' => false,
            'name' => null,
            'file' => null,
            'refererUrl' => null
        ];

        if ($productData) {

            // Set filename
            $fileName = '';
    
            if ($mainAttrVal) {
                $fileName .= $this->mappingHelper->convertToFileName($mainAttrVal);
            }

            if ($mainSetsAttrVal) {
                $fileName .= '-' . $this->mappingHelper->convertToFileName($mainSetsAttrVal);
            }

            $fileName .= '__' . $this->mappingHelper->getDateTime('Y-m-d') . '_' . $this->mappingHelper->getDateTime('H-i-s') . '.csv';
            
            // File path
            $file = 'export/singles/'. $fileName;

            // Generate csv file
            $this->directory->create('export');
            $stream = $this->directory->openFile($file, 'w+');
            $stream->lock();

            // Process data before write to CSV
            foreach ($productData as $row) {
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

        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/executionTimeexecutionTime.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info('exportProductAsCsv executionTime: '.$executionTime);

        return $result;
    }

    public function getRefererUrl() 
    {
        // $currentUrl = $this->urlBuilder->getUrl($route, $params);
        // dd($currentUrl);
        // if (str_contains($currentUrl, 'mui/index/render')) {
        //     $refererUrl = $this->getRefererUrl();
        //     dd($refererUrl);
        //     return $refererUrl;
        // }

        // return $currentUrl;
        // // return $this->redirect->getRefererUrl();
        $url = $this->urlBuilder->getCurrentUrl();
        if (str_contains($url, 'mui/index/render') || str_contains($url, 'export/csv')) {
            $url = $this->redirect->getRefererUrl();
        }

        return $url;
    }

    public function getProductType($label = false) {
        $refererUrl = $this->getRefererUrl();
        if ($refererUrl) {

            $result = [
                'code' => '',
                'label' => ''
            ];

            if ($this->getSource() == "database") {
                if (str_contains($refererUrl, 'singles/index')) {
                    $result['code'] = 'singles';
                    $result['label'] = 'Singles';
                }

                if (str_contains($refererUrl, 'sealedproducts/index')) {
                    $result['code'] = 'sealedproducts';
                    $result['label'] = 'Sealed Products';
                }
            }

            if ($this->getSource() == "catalog") {
                if (str_contains($refererUrl, '/singles')) {
                    $result['code'] = 'singles';
                    $result['label'] = 'Singles';
                }

                if (str_contains($refererUrl, '/sealedproducts')) {
                    $result['code'] = 'sealedproducts';
                    $result['label'] = 'Sealed Products';
                }
            }

            if ($label) {
                return $result['label'];
            }

            return $result['code'];
        }

        return null;
    }

    public function getProductTypeCode($isProductCreation = false) 
    {
        $refererUrl = $this->getRefererUrl();
        // if ($isProductCreation) {
            $namespace = $this->request->getParam('namespace');
            if ($namespace) {
                $code = null;

                if (str_contains($namespace, '_release_')) {
                    $code = 'release';
                } elseif (str_contains($namespace, '_sets_')) {
                    $code = 'sets';

                    if (str_contains($refererUrl, '/singles') || str_contains($namespace, '_singles_')) {
                        $code = 'singles_sets';
                    }
                } elseif (str_contains($namespace, '_singles_listing') || str_contains($namespace, 'card_listing') && !str_contains($namespace, 'sport')) {
                    $code = 'singles';
                } elseif (str_contains($namespace, '_sealedproducts_')) {
                    $code = 'sealedproducts';
                }

                // dd($code);

                return $code;
            }
        // }

        // dd($code);

        $url = $this->frontendUrl->getCurrentUrl();
        
        if ($refererUrl) {
            $result = [
                'code' => ''
            ];

            // if ($this->getSource() == "database") {
                // dd($url);
                if (str_contains($url, 'sets/singles/') || str_contains($refererUrl, 'sets/singles/')) {
                    $result['code'] = 'singles_sets';
                } elseif (str_contains($refererUrl, 'singles/index') || str_contains($url, 'cardgamescard/index')) {
                    $result['code'] = 'singles';
                } elseif (str_contains($refererUrl, 'sets')) {
                    $result['code'] = 'sets';
                } elseif (str_contains($url, 'sealedproducts/index') || str_contains($refererUrl, 'sealedproducts/index')) {
                    $result['code'] = 'sealedproducts';
                }
            // }

            // dd($result);

            return $result['code'];
        }

        return null;
    }

    public function getSource() {
        $refererUrl = $this->getRefererUrl();
        if ($refererUrl) {
            if (str_contains($refererUrl, 'database')) {
                return 'database';
            } else {
                return 'catalog';
            }
        }

        return 'catalog';
    }

    public function getSegmentFromUrl($url, $position) {
        $path = parse_url($url, PHP_URL_PATH);
        $segments = explode('/', trim($path, '/'));
        return $segments[$position] ?? null;
    }

    public function getPlatformDatabaseCategoryName() 
    {
        $urlKey = '';

        $currentUrl = $this->urlBuilder->getCurrentUrl();
        if (str_contains($currentUrl, 'mui/index/render')) {
            $refererUrl = $this->getRefererUrl();
            $urlKey = $this->getSegmentFromUrl($refererUrl, 2);
            if ($this->getCurrentArea() == "marketplace") {
                $urlKey = $this->getSegmentFromUrl($refererUrl, 3);
            }
        } else {
            $urlKey = $this->request->getControllerName();
        }

        $urlKey = $urlKey ? str_replace(['sets', 'singles','sealedproducts'], '', $urlKey) : null;

        // dd($urlKey);

        if ($urlKey == "diecast") {
            return "Diecast";
        }

        if ($urlKey == "trains") {
            return "Trains";
        }

        if ($urlKey == "cardgame" || $urlKey == "cardgames" || $urlKey == "cardgamescard") {
            return "Collectible Card Games";
        }
        
        if ($urlKey == 'anime')
        {
            return "Anime Toys";
        }

        if ($urlKey == 'animemodelkits')
        {
            return "Anime Model Kits";
        }

        if ($urlKey == 'gundam')
        {
            return "Gundam";
        }

        if ($urlKey == 'fungoods')
        {
            return "Fun Goods";
        }

        if ($urlKey == 'actiontoysmodelkits')
        {
            return "Action Toys Model Kits";
        }

        if ($urlKey == 'dolls')
        {
            return "Dolls";
        }

        if ($urlKey == 'actiontoy' || $urlKey == 'toys' || $urlKey == 'actiontoys')
        {
            return "Action Toys";
        }

        if ($urlKey == 'nonsportcard')
        {
            return "Non Sports Cards";
        }

        // dd($urlKey);

        return $this->mainCategoriesHelper->getCategoriesByUrlKey($urlKey);
        // $categoryName = null;

        // $refererUrl = $this->getRefererUrl();
        // // dd($refererUrl);
        // if ($refererUrl) {
        //     // CARD GAME CATEGORY
        //     if (str_contains($refererUrl, 'dolls')) 
        //     {
        //         $categoryName = 'Dolls';
        //     }

        //     if (str_contains($refererUrl, 'cardgame')) 
        //     {
        //         $categoryName = 'Collectible Card Games';
        //     }

        //     if (str_contains($refererUrl, 'gamesworkshop')) 
        //     {
        //         $categoryName = 'Games Workshop';
        //     }

        //     if (str_contains($refererUrl, 'sportcard')) 
        //     {
        //         $categoryName = 'Sport Cards';
        //     }
            

        //     if (str_contains($refererUrl, 'diecast')) 
        //     {
        //         $categoryName = 'Diecast';
        //     }

        //     if (str_contains($refererUrl, 'modelkits')) 
        //     {
        //         $categoryName = 'Model Kits';
        //     }

        //     if (str_contains($refererUrl, 'trains')) 
        //     {
        //         $categoryName = 'Trains';
        //     }
        // }

        // return $categoryName;
    }

    public function getSetsReleaseDatabase() 
    {
        $setsCollection = null;
        $singlesCollection = null;
        $sealedProductsCollection = null;

        $mainAttrCode = null;
        $setsTableName = null;
        $singlesTableName = null;
        $manageSetsPath = null;
        $sealedproductsTableName = null;
        $manageSealedProductsPath = null;
        $releaseAttrCode = null;
        $manageSinglesPath = null;

        $url = $this->frontendUrl->getCurrentUrl();
        if ($url) {
            
            $productId = null;
            $categories = null;
            $mainAttrVal = null;
            $categoryName = null;

            // HANDLE VARIATIONS WIZARD
            if (str_contains($url, "wizard")) {
                $productData = $this->request->getParam('product');

                $categoryId = $productData['category_ids'] ?? null;
                if ($categoryId) {
                    $category = $this->mappingHelper->getCategoryById($categoryId);
                    if ($category) {
                        $categoryName = $category->getName();
                    }
                }
            }

            if (
                $categoryName == "Collectible Card Games" || 
                str_contains($url, "cardgame") && !str_contains($url, "livingcardgame")
            ) {
                $setsCollection = $this->cardGameSets->create()->getCollection();
                $singlesCollection = $this->cardGameSetsCard->create()->getCollection();
                $sealedProductsCollection = $this->cardGameSealedProducts->create()->getCollection();

                $mainAttrCode = 'card_game';
                $releaseAttrCode = 'card_release';

                $setsTableName = 'marketplace_card_games_sets';
                $singlesTableName = 'marketplace_card_games_sets_card';
                $sealedproductsTableName = 'marketplace_card_games_sealed_products';

                $manageSetsPath = 'cardgamesets';
                $manageSealedProductsPath = 'cardgamesealedproducts';
                $manageSinglesPath = 'cardgamesingles';
            }

            if (str_contains($url, "livingcardgame")) {
                $sealedProductsCollection = $this->livingCardGameSealedProducts->create()->getCollection();

                $mainAttrCode = 'living_card_game';
                // $releaseAttrCode = 'card_release';

                $setsTableName = 'marketplace_living_card_games_sets';
                $singlesTableName = 'marketplace_living_card_games_singles';
                $sealedproductsTableName = 'marketplace_living_card_games_sealedproducts';

                $manageSetsPath = 'livingcardgamessets';
                $manageSealedProductsPath = 'livingcardgamessealedproducts';
            }

            if (str_contains($url, "miniaturegame")) {
                $sealedProductsCollection = $this->miniatureGameSealedProducts->create()->getCollection();

                $mainAttrCode = 'miniature_game';
                // $releaseAttrCode = 'card_release';

                $setsTableName = 'marketplace_miniature_games_sets';
                $singlesTableName = 'marketplace_miniature_games_sets_singles';
                $sealedproductsTableName = 'marketplace_miniature_games_sealedproducts';

                $manageSetsPath = 'miniaturegamessets';
                $manageSealedProductsPath = 'miniaturegamessealedproducts';
            }

            if (str_contains($url, "toys")) {
                $sealedProductsCollection = $this->actionToySealedProducts->create()->getCollection();

                $mainAttrCode = 'toys_brand';
                // $releaseAttrCode = 'card_release';

                $setsTableName = 'marketplace_action_toy_sets';
                $singlesTableName = 'marketplace_action_toy_singles';
                $sealedproductsTableName = 'marketplace_action_toy_sealedproducts';

                $manageSetsPath = 'toyssets';
                $manageSealedProductsPath = 'toyssealedproducts';
            }

            if (str_contains($url, "dolls")) {
                $sealedProductsCollection = $this->dollsSealedProductsFactory->create()->getCollection();

                $mainAttrCode = 'dolls_brand';
                // $releaseAttrCode = 'card_release';

                $setsTableName = 'marketplace_dolls_sets';
                $singlesTableName = 'marketplace_dolls_singles';
                $sealedproductsTableName = 'marketplace_dolls_sealedproducts';

                $manageSetsPath = 'dollssets';
                $manageSealedProductsPath = 'dollssealedproducts';
            }

            if (str_contains($url, "sportcard") && !str_contains($url, "nonsportcard")) {
                $sealedProductsCollection = $this->sportCardsSealedProducts->create()->getCollection();

                $mainAttrCode = 'sc_categories';
                // $releaseAttrCode = 'sc_release';

                $setsTableName = 'marketplace_sport_cards_sets';
                $singlesTableName = 'marketplace_sport_cards_sets_card';
                $sealedproductsTableName = 'marketplace_sport_cards_sealedproducts';

                $manageSetsPath = 'sportcardssets';
                $manageSealedProductsPath = 'sportcardssealedproducts';
            }

            if (str_contains($url, "nonsportcard")) {
                $sealedProductsCollection = $this->nonSportCardsSealedProducts->create()->getCollection();

                $mainAttrCode = 'nsc_categories';
                // $releaseAttrCode = 'nsc_release';

                $setsTableName = 'marketplace_non_sport_cards_sets';
                $singlesTableName = 'marketplace_non_sport_cards_sets_card';
                $sealedproductsTableName = 'marketplace_non_sport_cards_sealedproducts';

                $manageSetsPath = 'nonsportcardssets';
                $manageSealedProductsPath = 'nonsportcardssealedproducts';
            }

            if (str_contains($url, "bricks")) {
                $sealedProductsCollection = $this->legoSealedProducts->create()->getCollection();

                $mainAttrCode = 'bricks_brand';
                // $releaseAttrCode = 'card_release';

                $setsTableName = 'marketplace_lego_sets';
                $singlesTableName = 'marketplace_lego_sets_singles';
                $sealedproductsTableName = 'marketplace_lego_sealedproducts';

                $manageSetsPath = 'brickssets';
                $manageSealedProductsPath = 'brickssealedproducts';
            }

            if (str_contains($url, "diecastmodelkits")) {
                $sealedProductsCollection = $this->diecastModelKitsSealedProductsFactory->create()->getCollection();

                $mainAttrCode = 'diecast_model_kits_brand';
                // $releaseAttrCode = 'card_release';

                $setsTableName = 'marketplace_diecast_model_kits_sets';
                $singlesTableName = 'marketplace_diecast_model_kits_singles';
                $sealedproductsTableName = 'marketplace_diecast_model_kits_sealedproducts';

                $manageSetsPath = 'diecastmodelkitssets';
                $manageSealedProductsPath = 'diecastmodelkitssealedproducts';
            }

            if (str_contains($url, "trains")) {
                $sealedProductsCollection = $this->railwaySealedProductsFactory->create()->getCollection();

                $mainAttrCode = 'railway_brand';
                // $releaseAttrCode = 'card_release';

                $setsTableName = 'marketplace_railway_sets';
                $singlesTableName = 'marketplace_railway_singles';
                $sealedproductsTableName = 'marketplace_railway_sealedproducts';

                $manageSetsPath = 'trainssets';
                $manageSealedProductsPath = 'trainssealedproducts';
            }
        }

        if ($mainAttrCode) {
            return [
                'mainAttrCode' => $mainAttrCode,
                'setsTableName' => $setsTableName,
                'singlesTableName' => $singlesTableName,
                'manageSetsPath' => $manageSetsPath,
                'manageSealedProductsPath' => $manageSealedProductsPath,
                'manageSinglesPath' => $manageSinglesPath,
                'sealedproductsTableName' => $sealedproductsTableName,
                'releaseAttrCode' => $releaseAttrCode,
                'setsCollection' => $setsCollection,
                'singlesCollection' => $singlesCollection,
                'sealedProductsCollection' => $sealedProductsCollection
            ];
        }

        return null;
    }

    public function getUrl($route, $params) 
    {
        return $this->urlBuilder->getUrl($route, $params);
    }

    public function getActionsLink($item = null) {
        $html = '';
        $mainEntityUrlPath = '';
        $params = [];

        $linkLabel = null;

        $currentUrl = $this->urlBuilder->getCurrentUrl();
        // dd($currentUrl);
        if (str_contains($currentUrl, 'render/')) {
            $currentUrl = $this->redirect->getRefererUrl();
        }

        if (str_contains($currentUrl, 'customattributes')) {
            // dd($currentUrl);
            
            $path = parse_url($currentUrl, PHP_URL_PATH);

            // Pecah URL menjadi array
            $segments = explode('/', trim($path, '/'));

            // Ambil segmen ke-3 (0-based: 0=backoffice, 1=productattributeslink, 2=cardgameattributes)
            $entity = str_replace("attributes", "", $segments[2]);

            // dd($entity);

            $entityCode = $segments[count($segments)-3];

            $mainAttrCode = $segments[count($segments)-2];

            $mainAttrVal = $segments[count($segments)-1];

            $params = [
                'entity' => $entityCode,
                'attribute_code' => $item['attribute_code'],
                $mainAttrCode => $mainAttrVal
            ];

            

            
            // if ($manageSinglesUrl) {
            //     $html .= '<a href="'. $manageSinglesUrl .'">'.$manageSinglesLabel.'</a><br />';
            // }

            // if ($url) {
                if (str_contains($currentUrl, 'customattributes/linkedvalues/')) {
                    $entityCode = $segments[count($segments)-5];
                    
                    $editUrl = $this->getUrl(
                        'productattributeslink/customattributes/linkededit',
                        [
                            'entity' => $entityCode,
                            'entity_id' => $item['entity_id'],
                            $mainAttrCode => $mainAttrVal
                        ]
                    );
                    
                    $html .= '<a href="'. $editUrl .'">Edit Value</a>';
                } elseif (str_contains($currentUrl, 'customattributes/linked/')) {
                    $urlPath = 'productattributeslink/customattributes/linkedvalues';

                    $entityCode = $segments[count($segments)-7];

                    $params = [
                        'entity' => $entityCode,
                        'attribute_code' => $item['attribute_code'],
                        'attribute_value' => $item['attribute_value'],
                        'linked_attribute_code' => $item['linked_attribute_code'],
                        $mainAttrCode => $mainAttrVal
                    ];

                    $url = $this->getUrl(
                        $urlPath,
                        $params
                    );

                    $html .= '<a href="'. $url .'">Manage Attribute Values</a>';
                } elseif (str_contains($currentUrl, 'customattributes/values')) {
                    $editUrl = $this->getUrl(
                        'productattributeslink/customattributes/edit',
                        [
                            'entity' => $entityCode,
                            'entity_id' => $item['entity_id'],
                            $mainAttrCode => $mainAttrVal
                        ]
                    );

                    $entityCode = $segments[count($segments)-5];

                    $manageLinkedUrl = $this->getUrl(
                        'productattributeslink/customattributes/linked',
                        [
                            'entity' => $entityCode,
                            'attribute_value' => $item['attribute_value'],
                            'attribute_code' => $item['attribute_code'],
                            $mainAttrCode => $mainAttrVal
                        ]
                    );

                    $html .= '<a href="'. $manageLinkedUrl .'">Manage Linked Attributes</a><br/>';
                    $html .= '<a href="'. $editUrl .'">Edit Value</a>';
                } else {
                    $urlPath = 'productattributeslink/customattributes/values';

                    $url = $this->getUrl(
                        $urlPath,
                        $params
                    );

                    $html .= '<a href="'. $url .'">Manage Attribute Values</a>';
                }
            // }

            // dd($html);

            return $html;
        } else if (str_contains($currentUrl, 'attributes/')) {
            // dd($currentUrl);
            $urlPath = 'productattributeslink/customattributes/index';
            // $path = parse_url($currentUrl, PHP_URL_PATH);

            // // Pecah URL menjadi array
            // $segments = explode('/', trim($path, '/'));

            // // Ambil segmen ke-3 (0-based: 0=backoffice, 1=productattributeslink, 2=cardgameattributes)
            // $entity = str_replace("attributes", "", $segments[2]);

            // d
            $path = parse_url($currentUrl, PHP_URL_PATH);

            // Pecah URL menjadi array
            $segments = explode('/', trim($path, '/'));

            // dd($segments);

            // Ambil segmen ke-3 (0-based: 0=backoffice, 1=productattributeslink, 2=cardgameattributes)
            $entity = str_replace("attributes", "", $segments[2]);
            // dd($entity);

            // dd($entity);

            $mainCategoriesConfigData = $this->mainCategoriesConfigProvider->getByEntityCode($entity);

            // dd($mainCategoriesConfigData);

            // dd($entity);
            $mainAttrCode = $mainCategoriesConfigData['mainAttrCode'] ?? '';
            $entityCode = $mainCategoriesConfigData['entityCode'] ?? '';

            // dd($mainAttrCode);

            // dd($item[$mainAttrCode]);
            
            $params = [];

            if ($mainAttrCode) {
                $params = [
                    'entity' => $entityCode,
                    $mainAttrCode => $item['option_id'] ?? ''
                    // 'mainAttrVal' => $this->request->getParam('categories'),
                    // 'attribute_code' => $item['attribute_code'],
                    // 'id' => $this->request->getParam('id')
                ];
            }

            $url = $this->getUrl(
                $urlPath,
                $params
            );

            
            // if ($manageSinglesUrl) {
            //     $html .= '<a href="'. $manageSinglesUrl .'">'.$manageSinglesLabel.'</a><br />';
            // }

            if ($url) {
                $html .= '<a href="'. $url .'">Manage Attributes</a>';
            }

            // dd($html);

            return $html;
            // // dd($currentUrl);
            // $urlPath = 'productattributeslink/customattributes/index';
            // $path = parse_url($currentUrl, PHP_URL_PATH);

            // // Pecah URL menjadi array
            // $segments = explode('/', trim($path, '/'));

            // // Ambil segmen ke-3 (0-based: 0=backoffice, 1=productattributeslink, 2=cardgameattributes)
            // $entity = str_replace("attributes", "", $segments[2]);

            // // dd($entity);

            // $params = [
            //     'attribute_code' => $item['attribute_code'],
            //     'linked_attribute_code' => $item['linked_attribute_code'],
            //     'linked_attribute_value' => $item['linked_attribute_value']
            // ];

            // $url = $this->getUrl(
            //     $urlPath,
            //     $params
            // );

            
            // // if ($manageSinglesUrl) {
            // //     $html .= '<a href="'. $manageSinglesUrl .'">'.$manageSinglesLabel.'</a><br />';
            // // }

            // if ($url) {
            //     $html .= '<a href="'. $url .'">Manage Attributes</a>';
            // }

            // // dd($html);

            // return $html;
        }

        // if ()
        
        
        // get category name
        $categoryName = $this->getPlatformDatabaseCategoryName();

        if (!$categoryName) {
            dd('categoryName not found');
        }

        $manageSinglesUrl = null;
        $manageSinglesLabel = null;

        if ($categoryName) {

            // get custom table metadata
            $customTableMetadata = $this->mappingHelper->getCustomTableMetadata(null, $categoryName);

            if (!$customTableMetadata) {
                dd('customTableMetadata not founs');
            }

            // dd($customTableMetadata);

            if ($customTableMetadata) {
                $mainAttrCode = $customTableMetadata['mainAttrCode'];
                $viewSinglesUrlPath = $customTableMetadata['viewSinglesUrlPath'];
                $viewSetsUrlPath = $customTableMetadata['viewSetsUrlPath'];
                $viewSealedProductsUrlPath = $customTableMetadata['viewSealedProductsUrlPath'];

                // Get current url
                $currentUrl = $this->urlBuilder->getCurrentUrl();
                // dd($currentUrl);
                if (!$currentUrl) {
                    // dd($currentUrl);
                    $currentUrl = $this->request->getParam('namespace');
                }
                // dd($currentUrl = $this->request->getParam('namespace'));

                if (!$viewSinglesUrlPath && !$viewSetsUrlPath) {
                    dd('viewSinglesUrlPath not found');
                }
                

                $routes = 'productattributeslink';

                if ($this->getCurrentArea() == "marketplace") {
                    $routes = 'database';
                }

                $urlPath = null;

                // dd($currentUrl);

                // View Sets/Release Link
                if (isset($item['option_id'])) {
                    if (str_contains($currentUrl, '/index') && !str_contains($currentUrl, 'singles')) {
                        $urlPath = $routes . '/' . $viewSetsUrlPath . '/index';
                        $params = [
                            $mainAttrCode => $item['option_id']
                        ];

                        $linkLabel = 'View Sets/Release';
                    }

                    if (str_contains($currentUrl, 'singles')) {
                        $urlPath = $routes . '/' . $viewSetsUrlPath . '/singles';
                        $params = [
                            $mainAttrCode => $item['option_id']
                        ];

                        $linkLabel = 'View Sets/Release';
                    }
                    // dd($currentUrl = $this->request->getParam('namespace'));
// dd($currentUrl);
                    if (str_contains($currentUrl, 'sealedproducts')) {
                        $urlPath = $routes . '/' . $viewSealedProductsUrlPath . '/index';
                        $params = [
                            $mainAttrCode => $item['option_id']
                        ];

                        $linkLabel = 'View Sealed Products';
                    }
                }
                // dd($linkLabel);
                
                $action = null;
                $actionName      = $this->request->getActionName();
                $namespace      = $this->request->getParam('namespace');

                // dd($mainAttrCode);

                $mainAttrVal = $this->request->getParam($mainAttrCode);
                // dd($mainAttrVal);
                if (!isset($item['option_id'])) {
                    // dd($actionName);
                    // dd($namespace);
                    $refererUrl = $this->getRefererUrl();
                    if (($actionName == "index" || $namespace && str_contains($namespace,'sets_listing')) && $mainAttrVal) {
                        // dd('ok');
                        // dd($refererUrl);
                        if (str_contains($refererUrl,'/singles/')) {
                            $manageSinglesLabel = 'Manage Singles';
                            if ($this->getCurrentArea() == "marketplace") {
                                $manageSinglesLabel = 'View Singles';
                            }

                            $urlPath = $routes . '/' . $viewSinglesUrlPath . '/index';
                            $params = [
                                'set_id' => $item['entity_id']
                            ];

                            $manageSinglesUrl = $this->getUrl(
                                $urlPath,
                                $params
                            );
                        }

                        if ($this->getCurrentArea() != "marketplace") {
                            $urlPath = $routes . '/' . $viewSetsUrlPath . '/edit';
                            $params = [
                                'entity_id' => $item['entity_id']
                            ];

                            $linkLabel = 'Edit Set';
                        }
                    } elseif ($namespace && str_contains($namespace,'singles')) {
                        $urlPath = $routes . '/' . $viewSinglesUrlPath . '/edit';
                        $params = [
                            'entity_id' => $item['entity_id']
                        ];

                        // dd($item);

                        $linkLabel = 'Edit Singles';

                        if ($this->getCurrentArea() == "marketplace") {
                            $urlPath = $routes . '/' . $viewSinglesUrlPath . '/index';
                            $params = [
                                'set_id' => $item['entity_id']
                            ];

                            $linkLabel = 'View Singles';
                        }
                    } elseif ($namespace && str_contains($namespace,'sealedproducts')) {
                        $urlPath = $routes . '/' . $viewSealedProductsUrlPath . '/edit';
                        $params = [
                            'entity_id' => $item['entity_id']
                        ];

                        $linkLabel = 'Edit Sealed Products';
                    }
                }
            }
        }

        // dd($linkLabel);

        $url = $this->getUrl(
            $urlPath,
            $params
        );

        
        if ($manageSinglesUrl) {
            $html .= '<a href="'. $manageSinglesUrl .'">'.$manageSinglesLabel.'</a><br />';
        }

        if ($linkLabel) {
            $html .= '<a href="'. $url .'">'.$linkLabel.'</a>';
        }

        // dd($html);

        return $html;
    }

    public function getCurrentArea()
    {
        try {
            return $this->state->getAreaCode();
        } catch (LocalizedException $e) {
            // Jika area belum di-set, bisa handle di sini
            return 'unknown';
        }
    }

    public function getSetsActionLinkHtml($area, $customTableType, $item) 
    {
        $html = '';

        $data = $this->getSetsReleaseDatabase();
        if ($data && $item) {
            
            if ($customTableType == "singles") {
                $manageSetsUrl = $this->getUrl(
                    'database/' . $data['manageSinglesPath'] . '/index',
                    [
                        'set_id' => $item['entity_id']
                    ]
                );

                $html .= '<a href="'. $manageSetsUrl .'">View Singles</a>';
            }
        }

        return $html;
    }

    public function removeUnusedAttributes($productData) {
        $keysToUnset = ['category_ids', 'status', 'required_options', 'has_options', 'image_label','quantity_and_stock_status','visibility','seller_id','approval','seller_group','membership_order',
        'seller_featured_package',
        'auction',
        'sale',
        'links_purchased_separately',
        'samples_title',
        'links_title',
        'links_exist',
        'source',
        'thumbnail_label',
        'featured_package',
        'weight',
        'weight_type',
        'news_from_date',
        'news_to_date',
        'lof_preorder',
        'preorder_availability',
        'preorder_msg',
        'cod_available',
        'link_to_other_stores',
        'product_rma',
        'seller_duration',
        'seller_membership_order',
        'small_image_label','created_at','updated_at','sku_type','price_type','tax_class_id','old_id', 'url_path','card_game_mtg'];
        $productData = array_diff_key($productData, array_flip($keysToUnset));
        
        unset($productData['sku']);

        return $productData;
    }

    public function getSetsReleaseActionLinkHtml($area, $customTableType, $item) 
    {
        $html = '';

        $data = $this->getSetsReleaseDatabase();
        if ($data && $item) {

            if ($customTableType == "sealedproducts") {
                $manageSealedProductsUrl = $this->getUrl(
                    'database/' . $data['manageSealedProductsPath'] . '/index',
                    [
                        $data['mainAttrCode'] => $item['option_id']
                    ]
                );

                $html .= '<a href="'. $manageSealedProductsUrl .'">View Sealed Products</a>';
            }

            // dd($item);
            
            if ($customTableType == "singles") {
                $manageSetsUrl = $this->getUrl(
                    'database/' . $data['manageSetsPath'] . '/singles',
                    [
                        $data['mainAttrCode'] => $item['option_id']
                    ]
                );

                $html .= '<a href="'. $manageSetsUrl .'">View Sets / Release</a>';
            }

            if ($customTableType == "sets") {
                $manageSetsUrl = $this->getUrl(
                    'database/' . $data['manageSetsPath'] . '/index',
                    [
                        $data['mainAttrCode'] => $item['option_id']
                    ]
                );

                $html .= '<a href="'. $manageSetsUrl .'">View Sets / Release</a>';
            }
        }

        return $html;
    }

    public function getAndFilterSealedProductsCollection() 
    {
        $data = $this->getSetsReleaseDatabase();
        if ($data) {
            $collection = $data['sealedProductsCollection'];
        }

        return $collection;
    }

    
}