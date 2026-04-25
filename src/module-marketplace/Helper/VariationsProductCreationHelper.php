<?php

namespace TCGCollective\MarketPlace\Helper;

use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\CacheInterface;
use Magento\Catalog\Model\Product\Type;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;

class VariationsProductCreationHelper extends \Magento\Framework\App\Helper\AbstractHelper
{
    const CACHE_TAG = 'seller_variations_product_creation_cache';

    protected $currentCustomTableMetadata;

    protected $redirect;
    protected $mappingHelper;
    protected $catalogHelper;
    protected $helper;
    protected $productCollectionFactory;
    protected $request;
    protected $urlBuilder;
    protected $serializer;
    protected $resource;
    protected $cache;
    protected $variationsAttributesMappingFactory;
    protected $attributesCollection;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        \TCGCollective\ProductAttributesLink\Helper\Data $mappingHelper,
        \TCGCollective\MarketPlace\Helper\Catalog $catalogHelper,
        \Lof\MarketPlace\Helper\Data $helper,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Serialize\SerializerInterface $serializer,
        \Magento\Framework\App\ResourceConnection $resource,
        CacheInterface $cache,
        \TCGCollective\MarketplaceConfigurableProduct\Model\VariationsAttributesMappingFactory $variationsAttributesMappingFactory,
        \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $attributesCollection
    ) {
        $this->redirect = $redirect;
        $this->mappingHelper = $mappingHelper;
        $this->catalogHelper = $catalogHelper;
        $this->helper = $helper;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->request = $request;
        $this->urlBuilder = $context->getUrlBuilder();
        $this->serializer = $serializer;
        $this->resource = $resource;
        $this->cache = $cache;
        $this->variationsAttributesMappingFactory = $variationsAttributesMappingFactory;
        $this->attributesCollection = $attributesCollection;
        parent::__construct(
            $context
        );
    }

    /**
     * Clear cache manually if needed.
     */
    public function clearMyProductsCache()
    {
        $this->cache->clean(self::CACHE_TAG); // Remove cache entry
    }

    // SELLER | MY PRODUCTS | EDIT | BACK URL
    public function getMyProductsEditBackOrSaveUrl() 
    {
        // TO DO
        return null;
    }

    // SELLER | MY PRODUCTS | APPLY GRID FILTER
    public function applyMyProductsDataProviderFilter($collection, $filter) 
    {
        if ($filter->getField() === 'is_published') {
            $collection->addFilterToMap('is_published', 'cpei.value');
            
            $collection->getSelect()->where(
                "cpei.value = ?",
                $filter->getValue()
            );
            
            if ($filter->getValue() != 1) {
                $collection->getSelect()->orWhere(
                    "cpei.value IS NULL"
                );
            }

            return $collection;
        }

        $fieldCode = $filter->getField();
        $value = $filter->getValue();

        if ($fieldCode == "website_ids") {
            $collection->addWebsiteFilter($value);

            return $collection;
        }

        if (!is_array($value)) {
            $attrCode = $this->getAttributeCodesByOptionIds($value);

            $attrCode = $attrCode[$value] ?? null;

            if ($attrCode) {
                $collection->addAttributeToFilter($attrCode, ['eq' => $value]);

                return $collection;
            }
        }

        return null;
    }
    
    public function getVariationsProductCreationMainEntityMetadata() 
    {
        $mainEntityMetadata = null;

        $url = $this->getCurrentUrl();

        
        // $url = $this->urlBuilder->getCurrentUrl();
        // if (str_contains($url, 'mui/index/render')) {
        //     $url = $this->redirect->getRefererUrl();
        // }

        // dd($url);

        // Ui component render ajax
        // if (!str_contains($url, 'product/wizard')) {
        //     // Card Game
        //     if (str_contains($url, '/cardgame')) {
        //         $mainEntityMetadata = $this->catalogHelper->getMainEntityMetadata('cardgame');
        //     }

        //     // Sport Cards
        //     if (str_contains($url, '/sportcards')) {
        //         $mainEntityMetadata = $this->catalogHelper->getMainEntityMetadata('sportcards');
        //     }
        // }

        // Wizard Request Ajax
        // if (str_contains($url, 'product/wizard')) {
            $productParams = $this->request->getParam('product');
            $cardGame = $this->request->getParam('card_game');
            $scCategories = $this->request->getParam('sc_categories');

            // Card Game
            if (isset($productParams['card_game']) || $cardGame) {
                $mainEntityMetadata = $this->catalogHelper->getMainEntityMetadata('cardgame');
            }

            // Sport Cards
            if (isset($productParams['sport_cards']) || $scCategories) {
                $mainEntityMetadata = $this->catalogHelper->getMainEntityMetadata('sportcards');
            }
        // }

        return $mainEntityMetadata;
    }

    public function getVariationsMappingAttributeCodes() 
    {
        $productParam = $this->request->getParam('product');
        if (!$productParam) {
            return null;
        }

        $attributeSetId = $productParam['attribute_set_id'] ?? null;

        $variantAttributeCodes = null;

        // Get variations attributes mapping
        $collection = $this->variationsAttributesMappingFactory->create()
        ->getCollection();
        $collection->addFieldToFilter('attribute_set_id', $attributeSetId);
        if ($collection->getSize()) {
            $mapping = $collection->getFirstItem()->getVariationsAttributeCodes();
            if ($mapping) {
                $variantAttributeCodes = explode(',', $mapping);
            }
        }

        return $variantAttributeCodes;
    }

    public function getCurrentCustomTableMetadata() 
    {
        if ($this->currentCustomTableMetadata) {
            return $this->currentCustomTableMetadata;
        }

        $productParam = $this->request->getParam('product');
        if ($productParam) {
            // Select Variations Step
            $categoryId = isset($productParam['category_ids']) ? $productParam['category_ids'][0] : null;
            if ($categoryId) {
                $customTableMetadata = $this->mappingHelper->getCustomTableMetadata($categoryId);
                if ($customTableMetadata) {
                    $this->currentCustomTableMetadata = $customTableMetadata;
                    return $this->currentCustomTableMetadata;
                }
            }
        }

        return null;
    }

    public function getPlatformDatabaseSetsArr() 
    {
        $customTableMetadata = $this->getCurrentCustomTableMetadata();
        if ($customTableMetadata) {
            // Check exist singles
            // $singlesTableName = $customTableMetadata['singlesTableName'];
            // $collection->getSelect()->where(
            //     'EXISTS (SELECT 1 FROM ' . $singlesTableName .' mcgsc WHERE mcgsc.set_id = main_table.entity_id)'
            // );

            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $this->helper = $objectManager->create(\Lof\MarketPlace\Helper\Data::class);

            $sellerId = $this->helper->getSellerId();

            $namespace = $this->request->getParam('namespace');

            $cardSet = [];
            $cardRelease = [];

            // $card_product_type = $this->mappingHelper->getOptionId('card_product_type', 'Singles');

            $sellerProductCollection = $this->productCollectionFactory->create()
            ->addAttributeToSelect('mtg_card_set')
            ->addAttributeToFilter('seller_id', $sellerId)
            // ->addAttributeToFilter('card_product_type', $card_product_type)
            ->addAttributeToFilter('mtg_card_set', ['notnull' => true])
            ->addFieldToFilter('type_id', 'simple');

            $sellerProductCollection->getSelect()->group('at_mtg_card_set.value');
            if ($sellerProductCollection->getSize()) {
                foreach($sellerProductCollection as $product) {
                    // if ($namespace && str_contains($namespace, 'sets') && !str_contains($namespace, 'release')) {
                        $cardSet[] = $this->mappingHelper->getOptionLabelById('mtg_card_set', $product->getData('mtg_card_set'));
                    // }

                    // if ($namespace && !str_contains($namespace, 'sets') && str_contains($namespace, 'release')) {
                    //     $cardRelease[] = $this->mappingHelper->getOptionLabelById('mtg_card_release', $product->getData('mtg_card_release'));
                    // }
                }
            }

            return $cardSet;
        }

        return null;
    }

    // SELLER | VARIATIONS | PRODUCT CREATION | SETS | DATAPROVIDER
    public function getVariationsProductCreationDataProvider($step) 
    {
        // dd('getVariationsProductCreationSetsReleaseDataProvider');
        
        $filters = $this->request->getParam('filters');
        
        $mainEntityMetadata = $this->getVariationsProductCreationMainEntityMetadata(); 
        // dd($mainEntityMetadata);
        if (!$mainEntityMetadata) {
            return [
                "totalRecords" => 0,
                "items" => []
            ];
        }

        // Load from cache
        $cacheKey = self::CACHE_TAG . '_' . $mainEntityMetadata['main_attribute_code'] . '_' . $step . '_' . json_encode($filters);
        $cachedData = $this->cache->load($cacheKey);
        if ($cachedData) {
            // return unserialize($cachedData);
        }

        // dd($mainEntityMetadata);

        // SELECT ATTRIBUTES STEP
        if ($step == "select_attributes") {
            $collection = $this->attributesCollection->create();

            $variantAttributeCodes = $this->getVariationsMappingAttributeCodes();
            if ($variantAttributeCodes) {
                $collection->getSelect()->where('main_table.attribute_code IN (\'' . implode('\',\'', $variantAttributeCodes) . '\')');
            } else {
                $collection->getSelect()->where('main_table.attribute_code = NULL');
            }
            
            $collection->getSelect()->where(
                '(`apply_to` IS NULL) OR
                (
                    FIND_IN_SET(' .
                sprintf("'%s'", Type::TYPE_SIMPLE) . ',
                        `apply_to`
                    ) AND
                    FIND_IN_SET(' .
                sprintf("'%s'", Type::TYPE_VIRTUAL) . ',
                        `apply_to`
                    ) AND
                    FIND_IN_SET(' .
                sprintf("'%s'", Configurable::TYPE_CODE) . ',
                        `apply_to`
                    )
                )'
            );
            
            $collection->getSelect()->group('main_table.attribute_code');
            
            if ($collection->getSize()) {
                $result = $collection->toArray();

                $this->cache->save(
                    serialize($result),
                    $cacheKey,
                    [self::CACHE_TAG]
                );

                return $result;
            }

            return [
                "totalRecords" => 0,
                "items" => []
            ];
        }

        // SELECT SETS STEP
        if ($step == "select_sets" || $step == "select_singles") {
            $productType = 'singles';

            $collection = $this->productCollectionFactory->create();
            
            // Filter by category
            $collection = $this->catalogHelper->filterMyProductsDataProviderByCategory(
                $collection, 
                $mainEntityMetadata
            );

            // Filter by seller
            $collection = $this->catalogHelper->filterMyProductsDataProviderBySeller($collection);

            $collection->addAttributeToFilter('type_id', 'simple');

            // Select all attributes
            $collection->addAttributeToSelect(['*']);

            // Filter by product type
            $collection = $this->catalogHelper->filterMyProductsDataProviderByProductType(
                $collection,
                $mainEntityMetadata,
                $productType
            );

            $mainSetsAttrCode = $mainEntityMetadata['set_attribute_code'];
            $mainAttrCode = $mainEntityMetadata['main_attribute_code'];

            $productParam = $this->request->getParam('product');

            
            $mainAttrVal = $productParam[$mainAttrCode] ?? '';
            $categoryId = isset($productParam['category_ids']) ? $productParam['category_ids'][0] : null;

            $mainAttrValLabel = $this->catalogHelper->getOptionLabelById($mainAttrVal);
            
            $prefix = $this->mappingHelper->getPrefixByCategoryId(
                $categoryId,
                $mainAttrValLabel
            );

            if ($mainAttrVal) {
                $collection->addFieldToFilter($mainAttrCode, $mainAttrVal);
            }

            // FILTER MODIFIER
            $filterModifiers = $this->request->getParam('filters_modifier');
            if ($filterModifiers) {
                foreach($filterModifiers as $attrCode => $filter) {
                    if ($attrCode == $mainSetsAttrCode) {
                        $attrCode = $prefix . $mainSetsAttrCode;
                    }

                    if ($attrCode == "entity_id") {
                        continue;
                    }

                    $conditionType = $filter['condition_type'];
                    $value = $filter['value'] ?? true;
                    $collection->addFieldToFilter($attrCode, [
                        $conditionType => $value
                    ]);
                }
            }

            $filters = $this->request->getParam('filters') ?? [];
            if ($filters) {

                $generalAttributeCodes = ['release_year','language_version','name','sku','supplier_sku'];
                $inputTypesAttributeCodes = ['name','sku','supplier_sku'];

                foreach($filters as $attrCode => $value) {
                    if ($attrCode == "placeholder") {
                        continue;
                    }

                    if (!in_array($attrCode, $generalAttributeCodes)) {
                        $attrCode = $prefix . $attrCode;
                    }

                    if (in_array($attrCode, $inputTypesAttributeCodes)) {
                        $collection->addFieldToFilter(
                            $attrCode,
                            [
                                ['like' => strtolower($value) . '%'],
                                ['like' => '%' . strtolower($value)],
                                ['like' => '%' . strtolower($value) . '%']
                            ]
                        );
                    } else {
                        $collection->addFieldToFilter($attrCode, ['eq' => $value]);
                    }
                }
            }

            // GROUP & SORT
            $collection->getSelect()->group('e.entity_id');
            $collection->getSelect()->order('entity_id desc');

            $result = $collection->toArray();

            $result = $this->removeMyProductsDataProviderPrefix($result);
            // ambil semua nilai card_set
            // $keys = array_column($result, 'card_set');

            // // cari yang unik
            // $uniqueKeys = array_unique($keys);

            // // cocokkan dengan original array
            // $result = array_intersect_key($result, $uniqueKeys);

            // print_r($result);
            $uniqueKeys = array_unique(
                array_map(function ($row) {
                    return $row['card_set'] . '-' . $row['language_version'];
                }, $result)
            );

            $result = array_values(array_intersect_key($result, $uniqueKeys));

            // dd($result);

            $this->cache->save(
                serialize($result),
                $cacheKey,
                [self::CACHE_TAG]
            );

            // dd($result);

            // die('<pre>'.print_r($collection->getFirstItem()->getData(),true));
            // die($collection->getSelect()->__toString());

            return $result;
        }
        
        
        // // Filter by category
        // $collection = $this->filterMyProductsDataProviderByCategory(
        //     $collection, 
        //     $mainEntityMetadata
        // );

        // // Filter by seller
        // $collection = $this->filterMyProductsDataProviderBySeller($collection);

        // // Select all attributes
        // $collection->addAttributeToSelect(['*']);

        // // Filter by product type
        // $collection = $this->filterMyProductsDataProviderByProductType(
        //     $collection,
        //     $mainEntityMetadata,
        //     $productType
        // );

        // $customTable = $collection->getTable('catalog_product_entity_int');
        // $collection->getSelect()->joinLeft(
        //     ['cpei' => $customTable],
        //     'e.entity_id = cpei.entity_id AND cpei.value = 1 AND cpei.attribute_id = 1304', // Ensure attribute_id matches
        //     [
        //         'value AS custom_value',
        //         'is_published' => new \Zend_Db_Expr("IF(cpei.value = 1, 1, IF(cpei.value IS NULL OR cpei.value = 2, 2, 1))")
        //     ]
        // );

        // // GROUP & SORT
        // $collection->getSelect()->group('e.entity_id');
        // $collection->getSelect()->order('entity_id desc');
        
        // // ADD FILTER TO MAP
        // $collection->addFilterToMap('is_published', 'cpei.value');

        

        // die('<pre>'.print_r($collection->getFirstItem()->getData(),true));
        // die($collection->getSelect()->__toString());

        return $result;
    }

    public function getMyProductsDataProviderProductType() {
        $currentUrl = $this->getCurrentUrl();
        if ($currentUrl) {
            if (str_contains($currentUrl,'sealedproducts')) {
                return 'sealedproducts';
            }

            if (str_contains($currentUrl,'singles')) {
                return 'singles';
            }

            if (str_contains($currentUrl,'lots')) {
                return 'lots';
            }

            if (str_contains($currentUrl,'supplies')) {
                return 'supplies';
            }
        }

        return 'sealedproducts';
    }

    public function filterMyProductsDataProviderByCategory(
        $collection, 
        $mainEntityMetadata
    ) {
        $filters = $this->request->getParam('filters') ?? [];
        if (!isset($filters['category_id'])) {
            $categoryIds = [];

            $categoryUrlKey = $mainEntityMetadata['category_url_key'];
            if (!$categoryUrlKey) {
                throw new \Exception(__('Main Entity Metadata | Category url key not found.'));
            }
            
            $category = $this->mappingHelper->getCategoryByUrlKey($categoryUrlKey);
            if ($category) {
                $categoryIds[] = (int)$category->getId();
            }

            $collection->addCategoriesFilter(['in' => $categoryIds]);
        }

        return $collection;
    }

    public function filterMyProductsDataProviderBySeller(
        $collection
    ) {
        $sellerId = $this->helper->getSellerId();
        $collection->addAttributeToFilter('seller_id', ['eq' => $sellerId]);

        return $collection;
    }

    public function getCurrentUrl() 
    {
        $url = $this->urlBuilder->getCurrentUrl();
        if (str_contains($url, 'mui/index/render')) {
            $url = $this->redirect->getRefererUrl();
        }

        return $url;
    }

    public function filterMyProductsDataProviderByProductType(
        $collection, 
        $mainEntityMetadata,
        $productType
    ) {
        $productTypeAttrCode = $mainEntityMetadata['product_type_attr_code'];
        if (!$productTypeAttrCode) {
            return $collection;
        }

        $url = $this->getCurrentUrl();

        if (str_contains($url, "configurable")) {
            $collection->addFieldToFilter('type_id', 'configurable');
        } else {
            $collection->addFieldToFilter('type_id', 'simple');
        }

        if ($productType == "lots") {
            $optionId = $this->mappingHelper->getOptionId($productTypeAttrCode, 'Lots/Sets');
            $collection->addAttributeToFilter($productTypeAttrCode, $optionId);
        }

        if ($productType == "supplies") {
            $optionId = $this->mappingHelper->getOptionId($productTypeAttrCode, 'Supplies');
            $collection->addAttributeToFilter($productTypeAttrCode, $optionId);
        }

        if ($productType == "preorders") {
            $optionId = $this->mappingHelper->getOptionId($productTypeAttrCode, 'Pre Orders');
            $collection->addAttributeToFilter($productTypeAttrCode, $optionId);

            if (str_contains($url, "sealedproducts")) {
                $optionId = $this->mappingHelper->getOptionId('card_pre_order_product_type', 'Sealed Products');
                $collection->addAttributeToFilter('card_pre_order_product_type', $optionId);
            } elseif (str_contains($url, "singles")) {
                $optionId = $this->mappingHelper->getOptionId('card_pre_order_product_type', 'Singles');
                $collection->addAttributeToFilter('card_pre_order_product_type', $optionId);
            }
        } elseif ($productType == "sealedproducts") {
            $optionId = $this->mappingHelper->getOptionId($productTypeAttrCode, 'Sealed Products');
            $collection->addAttributeToFilter($productTypeAttrCode, $optionId);
        } elseif ($productType == "singles") {
            $optionId = $this->mappingHelper->getOptionId($productTypeAttrCode, 'Singles');
            $collection->addAttributeToFilter($productTypeAttrCode, $optionId);
        }

        return $collection;
    }
    
    public function removeMyProductsDataProviderPrefix(
        $productData
    ) {
        $prefixes = $this->catalogHelper->getPrefixes();
        if (!$prefixes) {
            return [];
        }

        $cleanedRows = [];

        foreach ($productData as $row) {
            $cleanedRow = [];

            foreach ($row as $key => $value) {
                // simpan yang asli
                $cleanedRow[$key] = $value;

                // cek apakah ada prefix
                foreach ($prefixes as $prefix) {
                    if (strpos($key, $prefix) === 0) {
                        // buat duplicate key tanpa prefix
                        $newKey = substr($key, strlen($prefix));
                        $cleanedRow[$newKey] = $value;
                        break;
                    }
                }
            }

            $cleanedRows[] = $cleanedRow;
        }

        return $cleanedRows;
    }

    public function getOptionLabels(array $optionIds)
    {
        $connection = $this->resource->getConnection();
        $tableOption = $this->resource->getTableName('eav_attribute_option');
        $tableOptionValue = $this->resource->getTableName('eav_attribute_option_value');

        $select = $connection->select()
            ->from(['o' => $tableOption], ['option_id'])
            ->join(
                ['v' => $tableOptionValue],
                'o.option_id = v.option_id',
                ['label' => 'v.value']
            )
            ->where('o.option_id IN (?)', $optionIds)
            ->where('v.store_id = ?', 0); // global label

        $results = $connection->fetchAll($select);

        $options = [];
        foreach ($results as $row) {
            $options[] = [
                'label' => $row['label'],
                'value' => $row['option_id']
            ];
        }

        return $options;
    }

    public function getFilterOptions($attrCode, $isUsingPrefix = false) 
    {
        $step = 'select_sets';

        $productData = $this->getVariationsProductCreationDataProvider(
            $step
        );
        
        // $productData = $collection->toArray();
        // $productData = $this->removeMyProductsDataProviderPrefix($productData);

        $optionIds = [];

        foreach($productData as $item) {
            if (isset($item[$attrCode])) {
                $optionIds[] = $item[$attrCode];
            }
        }

        if ($optionIds) {
            $optionIds = array_unique($optionIds);
        }

        if ($attrCode == "sc_card_name") {
            // dd($optionIds);
        }

        if ($optionIds) {
            // dd($this->getOptionLabels($optionIds));
            return $this->getOptionLabels($optionIds);
        }

        $options = [
            [
                'label' => ' ',
                'value' => ''
            ]
        ];

        return $options;

        // if ($attrCode) {
        //     try {
        //         $filterItems = $this->getFilterItems($attrCode, $isUsingPrefix);
        //         if ($filterItems) {
        //             $options = [];

        //             if ($isUsingPrefix) {
        //                 $prefixArr = [];

        //                 foreach ($filterItems->getData() as $entityId => $productData) {
        //                     foreach ($productData as $key => $value) {
        //                         // Cek key yang diakhiri dengan _card_set DAN nilainya tidak null
        //                         if ($value !== null && str_ends_with($key, '_' . $attrCode)) {
        //                             // Ambil prefix-nya saja, misal 'pk' dari 'pk_card_set'
        //                             $prefix = substr($key, 0, -strlen('_' . $attrCode));
                                    
        //                             // Simpan dan keluar dari loop untuk entity ini (ambil satu saja)
        //                             $prefixArr[$entityId] = $prefix;
        //                             break;
        //                         }
        //                     }
        //                 }

        //                 $prefixArr = array_unique($prefixArr);
        //             }

        //             $options[] = [
        //                 'label' => '-',
        //                 'value' => null
        //             ];

        //             foreach ($filterItems as $item) {
        //                 $label = null;
        //                 if ($isUsingPrefix) {
                            
        //                     foreach($prefixArr as $prefix) {
        //                         $val = $item->getData($prefix.'_'.$attrCode);
        //                         if ($val) {
        //                             $getLabel = $this->mappingHelper->getOptionLabelById($prefix.'_'.$attrCode, $val);
        //                             if ($getLabel) {
        //                                 $label = $getLabel;
        //                                 $options[] = [
        //                                     'label' => $label,
        //                                     'value' => $val
        //                                 ];
        //                                 break;
        //                             }
        //                         }
        //                     }
        //                 } else {
        //                     $val = $item->getData($attrCode);
        //                     $label = $this->mappingHelper->getOptionLabelById($attrCode, $val);
        //                     if ($label) {
        //                         $options[] = [
        //                             'label' => $label,
        //                             'value' => $val
        //                         ];
        //                     }
        //                 }
        //             }

        //             if ($options) {
        //                 $options = array_map("unserialize", array_unique(array_map("serialize", $options)));
        //                 if ($attrCode == "release_year") {
        //                     usort($options, fn($a, $b) => $a['label'] <=> $b['label']);
        //                 } else {
        //                     usort($options, fn($a, $b) => $b['label'] <=> $a['label']);
        //                 }
        //             }
        //         }
        //     } catch (\Exception $e) {
        //         dd($e->getMessage());
        //     }
        // }
        
        return $options;
    }

    public function filterProductCollectionByCardProductType($collection) 
    {
        $url = $this->urlBuilder->getCurrentUrl();
        
        // if (str_contains($url, "configurable")) {
        //     $collection->addFieldToFilter('type_id', 'configurable');
        // } else {
        //     $collection->addFieldToFilter('type_id', 'simple');
        // }

        if (str_contains($url, "lots")) {
            $optionId = $this->mappingHelper->getOptionId('card_product_type', 'Lots/Sets');
            $collection->addAttributeToFilter('card_product_type', $optionId);
        }

        if (str_contains($url, "supplies")) {
            $optionId = $this->mappingHelper->getOptionId('card_product_type', 'Supplies');
            $collection->addAttributeToFilter('card_product_type', $optionId);
        }

        if (str_contains($url, "preorders")) {
            $optionId = $this->mappingHelper->getOptionId('card_product_type', 'Pre Orders');
            $collection->addAttributeToFilter('card_product_type', $optionId);

            if (str_contains($url, "sealedproducts")) {
                $optionId = $this->mappingHelper->getOptionId('card_pre_order_product_type', 'Sealed Products');
                $collection->addAttributeToFilter('card_pre_order_product_type', $optionId);
            } elseif (str_contains($url, "singles")) {
                $optionId = $this->mappingHelper->getOptionId('card_pre_order_product_type', 'Singles');
                $collection->addAttributeToFilter('card_pre_order_product_type', $optionId);
            }
        } elseif (str_contains($url, "sealedproducts")) {
            $optionId = $this->mappingHelper->getOptionId('card_product_type', 'Sealed Products');
            $collection->addAttributeToFilter('card_product_type', $optionId);
        } elseif (str_contains($url, "singles")) {
            $optionId = $this->mappingHelper->getOptionId('card_product_type', 'Singles');
            $collection->addAttributeToFilter('card_product_type', $optionId);
        }

        return $collection;
    }

    public function filterProductCollectionByProductType($collection) 
    {
        $url = $this->urlBuilder->getCurrentUrl();

        // dd($url);
        
        // if (str_contains($url, "configurable")) {
        //     $collection->addFieldToFilter('type_id', 'configurable');
        // } else {
        //     $collection->addFieldToFilter('type_id', 'simple');
        // }

        if (str_contains($url, "configurable") && !str_contains($url, "associated")) {
            $collection->addAttributeToFilter('type_id', 'configurable');
        } else {
            // dd($url);
            $collection->addAttributeToFilter('type_id', 'simple');
        }

        return $collection;
    }

    public function getFilterItems($attrCode, $isUsingPrefix) 
    {
        $items = [];

        $sellerId = $this->helper->getSellerId();

        $sellerProductCollection = $this->productCollectionFactory->create()
        ->addAttributeToFilter('seller_id', ['eq' => $sellerId]);

        // Filter by product type
        $this->filterProductCollectionByCardProductType($sellerProductCollection);

        $this->filterProductCollectionByProductType($sellerProductCollection);

        // Handle attributes with prefix
        if ($isUsingPrefix) {
            $connection = $sellerProductCollection->getConnection();
            $attributes = $connection->fetchPairs("
                SELECT attribute_id, attribute_code
                FROM eav_attribute
                WHERE attribute_code LIKE '%_$attrCode'
                AND attribute_code NOT LIKE '%{$attrCode}_%'
            ");

            if (!empty($attributes)) {
                $attributeIds = array_keys($attributes);
                $attributeIdList = implode(',', array_map('intval', $attributeIds));

                $customTable = $sellerProductCollection->getTable('catalog_product_entity_int');

                // Join ke table attr_int dgn semua attribute_id _card_set
                $sellerProductCollection->getSelect()->joinLeft(
                    ['attr_int' => $customTable],
                    "e.entity_id = attr_int.entity_id AND attr_int.attribute_id IN ($attributeIdList)",
                    []
                );

                // Bangun kolom-kolom dinamis berdasarkan attribute_code
                $columns = [];
                foreach ($attributes as $attributeId => $attributeCode) {
                    $columns[$attributeCode] = new \Zend_Db_Expr(
                        "MAX(IF(attr_int.attribute_id = $attributeId, attr_int.value, NULL))"
                    );
                }

                $sellerProductCollection->getSelect()->columns($columns);
            }
        } else {
            $sellerProductCollection->addAttributeToSelect($attrCode)
            ->addAttributeToFilter($attrCode, ['notnull' => true]);

            $sellerProductCollection->getSelect()->group('at_' . $attrCode . '.value');
        }
        
        $sellerProductCollection->getSelect()->group('e.entity_id');

        if ($sellerProductCollection->getSize()) {
            return $sellerProductCollection;
        }

        return null;
    }

    public function filterSellerCatalogProductDataProvider($collection, $productType = 'sealedproducts') 
    {
        $url = $this->urlBuilder->getCurrentUrl();

        $filters = $this->request->getParam('filters') ?? [];
        if (!isset($filters['category_id'])) {
            $categoryIds = [];
            
            $category = $this->mappingHelper->getCategoryByUrlKey('collectible-game');
            if ($category) {
                $categoryIds[] = (int)$category->getId();
            }

            $collection->addCategoriesFilter(['in' => $categoryIds]);
        }

        $collection->addAttributeToSelect('card_product_type');
        $collection->addAttributeToSelect('mtg_card_foil');
        $collection->addAttributeToSelect('mtg_sealed_products');
        $collection->addAttributeToSelect('release_year');
        $collection->addAttributeToSelect('card_game');
        $collection->addAttributeToSelect('website_ids');

        $sellerId = $this->helper->getSellerId();
        $collection->addAttributeToFilter('seller_id', ['eq' => $sellerId]);

        // Filter by product type
        $this->filterProductCollectionByCardProductType($collection);

        // Filter by product type
        $this->filterProductCollectionByProductType($collection);

        // Join with catalog_category_product to get product category names
        $categoryTable = $collection->getTable('catalog_category_product');
        $collection->getSelect()->joinLeft(
            ['ccp' => $categoryTable],
            'e.entity_id = ccp.product_id',
            ['category_id' => 'ccp.category_id']
        );

        // Join with catalog_category_entity to get category names
        $categoryEntityTable = $collection->getTable('catalog_category_entity_varchar');
        $collection->getSelect()->joinLeft(
            ['cce' => $categoryEntityTable],
            'ccp.category_id = cce.entity_id',
            ['categories' => 'cce.value'] // Retrieve the category name
        );

        $customTable = $collection->getTable('catalog_product_entity_int');
        $collection->getSelect()->joinLeft(
            ['cpei' => $customTable],
            'e.entity_id = cpei.entity_id AND cpei.value = 1 AND cpei.attribute_id = 1304', // Ensure attribute_id matches
            [
                'value AS custom_value',
                'is_published' => new \Zend_Db_Expr("IF(cpei.value = 1, 1, IF(cpei.value IS NULL OR cpei.value = 2, 2, 1))")
            ]
        );
        
        $customTable = $collection->getTable('catalog_product_entity_int');

        $attrCodes = $this->getAttrWithPrefixForListing();
        if ($attrCodes) {
            foreach($attrCodes as $index => $code) 
            {
                $this->selectAttrWithPrefix($collection, $code);
            }
        }

        // Select Variations Step
        // $productParam = $this->request->getParam('product');
        // $categoryId = isset($productParam['category_ids']) ? $productParam['category_ids'][0] : null;
        // if ($categoryId) {
        //     $customTableMetadata = $this->mappingHelper->getCustomTableMetadata($categoryId);
        //     if ($customTableMetadata) {
        //         $setsTableName = $customTableMetadata['setsTableName'] ?? null;
        //         $singlesTableName = $customTableMetadata['singlesTableName'] ?? null;
        //         $mainSinglesAttrCode = $customTableMetadata['mainSinglesAttrCode'] ?? null;
        //         $mainSetsAttrCode = $customTableMetadata['mainSetsAttrCode'] ?? null;
        //         if ($singlesTableName) {
        //             $singlesTable = $collection->getTable($singlesTableName);
        //             $collection->getSelect()->joinLeft(
        //                 ['singles' => $singlesTable],
        //                 $mainSinglesAttrCode . '_attr_int.value = singles.' . $mainSinglesAttrCode,
        //                 []
        //             );

        //             $setsTable = $collection->getTable($setsTableName);
        //             $collection->getSelect()->joinLeft(
        //                 ['sets' => $setsTable],
        //                 'singles.set_id = sets.entity_id',
        //                 ['card_set']
        //             );

        //             dd($collection->getSelect()->__toString());
        //             dd($collection->getFirstItem()->getData());
        //         }
        //     }
        // }

        // GROUP & SORT
        $collection->getSelect()->group('e.entity_id');
        $collection->getSelect()->order('entity_id desc');
        
        // ADD FILTER TO MAP
        $collection->addFilterToMap('is_published', 'cpei.value');

        // die('<pre>'.print_r($collection->getData(),true));
        // die($collection->getSelect()->__toString());

        return $collection;
    }

    public function getAttrWithPrefixForListing() 
    {
        $attributeCodes = [];

        $url = $this->urlBuilder->getCurrentUrl();

        $productTypeParamVal = null;

        $productParam = $this->request->getParam('product') ?? null;
        if ($productParam) {
            $productTypeParam = $productParam['card_product_type'] ?? null;
            if ($productTypeParam) {
                $productTypeParamVal = $this->mappingHelper->getOptionLabelById('card_product_type', $productTypeParam);
            }
        }

        if ($productTypeParamVal == "Sealed Products" || str_contains($url, "preorders") || str_contains($url, "sealedproducts")) {
            $attributeCodes = ['card_series','card_release','sealed_products'];
        }

        if ($productTypeParamVal == "Singles" || str_contains($url, "singles")) {
            $attributeCodes = ['card_set','card_name'];
        }

        return $attributeCodes;
    }

    public function selectAttrWithPrefix($collection, $attrCode) 
    {
        $connection = $collection->getConnection();

        $attributeIds = $connection->fetchCol("
            SELECT attribute_id FROM eav_attribute
            WHERE attribute_code LIKE '%_$attrCode'
            AND attribute_code NOT LIKE '%{$attrCode}_%'
        ");

        if (!empty($attributeIds)) {
            $attributeIdList = implode(',', array_map('intval', $attributeIds));

            $collection->getSelect()->joinLeft(
                [$attrCode . '_attr_int' => $collection->getTable('catalog_product_entity_int')],
                "e.entity_id = " . $attrCode . "_attr_int.entity_id AND " . $attrCode . "_attr_int.attribute_id IN ($attributeIdList)",
                []
            );

            // Generate dynamic CASE WHEN for each attribute_id
            $cases = array_map(function ($attrId) use ($attrCode) {
                return "WHEN " . $attrCode . "_attr_int.attribute_id = $attrId THEN " . $attrCode . "_attr_int.value";
            }, $attributeIds);

            $caseStatement = implode(" ", $cases);

            $collection->getSelect()->columns([
                $attrCode => new \Zend_Db_Expr("
                    MAX(
                        CASE $caseStatement ELSE NULL END
                    )
                "),
            ]);
        }

        return $collection;
    }

    public function getAttributeCodesByOptionIds(
        $optionIds
    ) {
        $resource = $this->resource;

        $connection  = $resource->getConnection();
        $tableOption = $resource->getTableName('eav_attribute_option');
        $tableAttr   = $resource->getTableName('eav_attribute');

        if (!is_array($optionIds)) {
            $optionIds = [$optionIds];
        }

        $select = $connection->select()
            ->from(['eao' => $tableOption], ['option_id'])
            ->join(
                ['ea' => $tableAttr],
                'ea.attribute_id = eao.attribute_id',
                ['attribute_code']
            )
            ->where('eao.option_id IN (?)', $optionIds);

        return $connection->fetchPairs($select);
    }
}