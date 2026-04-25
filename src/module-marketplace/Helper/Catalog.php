<?php

namespace TCGCollective\MarketPlace\Helper;

use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\CacheInterface;
use Lof\MarketPlace\Model\Seller;

class Catalog extends \Magento\Framework\App\Helper\AbstractHelper
{
    const CACHE_TAG = 'seller_my_products_cache';

    protected $redirect;
    protected $mappingHelper;
    protected $helper;
    protected $productCollectionFactory;
    protected $request;
    protected $urlBuilder;
    protected $serializer;
    protected $resource;
    protected $cache;
    protected $coreRegistry;
    protected $mainCategoriesHelper;
    protected $eavAttributeCollectionFactory;
    protected $sellerCollectionFactory;

    protected $mainCategoriesData = null;
    protected $myProductsTabsAttrCode = null;
    protected $myProductsTabs = null;
    protected $myProductsTabsLabel = null;
    protected $currentMyProductsTab = null;

    protected $_seller = null;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        \TCGCollective\ProductAttributesLink\Helper\Data $mappingHelper,
        \Lof\MarketPlace\Helper\Data $helper,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Serialize\SerializerInterface $serializer,
        \Magento\Framework\App\ResourceConnection $resource,
        CacheInterface $cache,
        \Magento\Framework\Registry $registry,
        \TCGCollective\MarketPlace\Helper\MainCategoriesHelper $mainCategoriesHelper,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory $eavAttributeCollectionFactory,
        \Lof\MarketPlace\Model\ResourceModel\Seller\CollectionFactory $sellerCollectionFactory
    ) {
        $this->redirect = $redirect;
        $this->mappingHelper = $mappingHelper;
        $this->helper = $helper;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->request = $request;
        $this->urlBuilder = $context->getUrlBuilder();
        $this->serializer = $serializer;
        $this->resource = $resource;
        $this->cache = $cache;
        $this->coreRegistry = $registry;
        $this->mainCategoriesHelper = $mainCategoriesHelper;
        $this->eavAttributeCollectionFactory = $eavAttributeCollectionFactory;
        $this->sellerCollectionFactory = $sellerCollectionFactory;
        parent::__construct(
            $context
        );
    }

    protected function getSellerByUrl($sellerUrl)
    {
        if (!isset($this->_seller[$sellerUrl])) {
            $seller = $this->sellerCollectionFactory->create()
            ->addFieldToFilter("url_key", $sellerUrl)
            ->addFieldToFilter("status", Seller::STATUS_ENABLED)
            ->getFirstItem();
            $this->_seller[$sellerUrl] = $seller && $seller->getId() ? $seller : null;
        }
        return $this->_seller[$sellerUrl];
    }

    // Seller profile mega menu graphql
    public function getSellerMainCategoryIds($sellerUrl) 
    {
        $seller = $this->getSellerByUrl($sellerUrl);
        if (!$seller) {
            throw new NoSuchEntityException(__('seller with url "%1" does not exist.', $sellerUrl));
        }

        $connection = $this->resource->getConnection();

        $marketplaceProductTable = $this->resource->getTableName('lof_marketplace_product');
        $categoryProductTable    = $this->resource->getTableName('catalog_category_product');
        $categoryEntityTable     = $this->resource->getTableName('catalog_category_entity');

        $sellerId = $seller->getId();
        // dd($sellerId);

        $select = $connection->select()
            ->from(['lmp' => $marketplaceProductTable], [])
            ->joinInner(
                ['cp' => $categoryProductTable],
                'cp.product_id = lmp.product_id',
                ['category_id']
            )
            ->joinInner(
                ['ce' => $categoryEntityTable],
                'ce.entity_id = cp.category_id',
                ['parent_id']
            )
            ->where('lmp.seller_id = ?', $sellerId);

        // dd($connection->fetchAll($select));

        $parentCategoryIds = array_unique(
            array_column($connection->fetchAll($select), 'parent_id')
        );

        // dd($parentIds);

        // dd($parentCategoryIds);
        
        return $parentCategoryIds ?? [];
    }

    public function getSellerCategoryIds($sellerUrl) 
    {
        $seller = $this->getSellerByUrl($sellerUrl);
        if (!$seller) {
            throw new NoSuchEntityException(__('seller with url "%1" does not exist.', $sellerUrl));
        }

        $connection = $this->resource->getConnection();

        $marketplaceProductTable = $this->resource->getTableName('lof_marketplace_product');
        $categoryProductTable    = $this->resource->getTableName('catalog_category_product');
        $categoryEntityTable     = $this->resource->getTableName('catalog_category_entity');

        $sellerId = $seller->getId();
        // dd($sellerId);

        $select = $connection->select()
            ->from(['lmp' => $marketplaceProductTable], [])
            ->joinInner(
                ['cp' => $categoryProductTable],
                'cp.product_id = lmp.product_id',
                ['category_id']
            )
            ->joinInner(
                ['ce' => $categoryEntityTable],
                'ce.entity_id = cp.category_id',
                ['entity_id']
            )
            ->where('lmp.seller_id = ?', $sellerId);

        // dd($connection->fetchAll($select));

        $categoryIds = array_unique(
            array_column($connection->fetchAll($select), 'entity_id')
        );

        // dd($parentIds);

        // dd($categoryIds);
        
        return $categoryIds ?? [];
    }

    // PWA | Product Details | Show/Hide & Sorting Product Information
    public function pwaFilteringAndSortingProductInformation($results, $categoryUid) 
    {
        if (!$categoryUid) {
            return $results;
        }

        $sequence = $this->getProductInformationAttributeCodes($categoryUid);
        if (!$sequence) {
            return $results;
        }

        $prefixes = $this->getPrefixesByCategoryId(base64_decode($categoryUid));

        // Filter: hanya biarkan code yang ada dalam sequence
        $results = array_filter($results, function($item) use ($sequence, $prefixes) {
            $code = $item['attribute_metadata']['code'];
            $code = $this->removePrefix($code, $prefixes);

            return in_array($code, $sequence, true);
        });

        // Re-index array agar tidak memiliki gap keys
        $results = array_values($results);
        
        usort($results, function($a, $b) use ($sequence, $prefixes) {
            $codeA = $a['attribute_metadata']['code'];
            $codeB = $b['attribute_metadata']['code'];

            $codeA = $this->removePrefix($codeA, $prefixes);
            $codeB = $this->removePrefix($codeB, $prefixes);

            $posA = array_search($codeA, $sequence);
            $posB = array_search($codeB, $sequence);

            // Jika code tidak ditemukan dalam $order, letakkan di akhir
            $posA = $posA === false ? PHP_INT_MAX : $posA;
            $posB = $posB === false ? PHP_INT_MAX : $posB;

            return $posA <=> $posB;
        });

        return $results;
    }

    // PWA | Product Details | Show/Hide & Sorting Product Details
    public function pwaFilteringAndSortingProductDetails($results, $categoryUid) 
    {
        if (!$categoryUid) {
            return $results;
        }

        $sequence = $this->getProductDetailsAttributeCodes($categoryUid);
        if (!$sequence) {
            return $results;
        }

        $prefixes = $this->getPrefixesByCategoryId(base64_decode($categoryUid));

        // dd($prefixes);

        // Filter: hanya biarkan code yang ada dalam sequence
        $results = array_filter($results, function($item) use ($sequence, $prefixes) {
            $code = $item['attribute_metadata']['code'];
            $code = $this->removePrefix($code, $prefixes);

            return in_array($code, $sequence, true);
        });

        // Re-index array agar tidak memiliki gap keys
        $results = array_values($results);
        
        usort($results, function($a, $b) use ($sequence, $prefixes) {
            $codeA = $a['attribute_metadata']['code'];
            $codeB = $b['attribute_metadata']['code'];

            $codeA = $this->removePrefix($codeA, $prefixes);
            $codeB = $this->removePrefix($codeB, $prefixes);

            $posA = array_search($codeA, $sequence);
            $posB = array_search($codeB, $sequence);

            // Jika code tidak ditemukan dalam $order, letakkan di akhir
            $posA = $posA === false ? PHP_INT_MAX : $posA;
            $posB = $posB === false ? PHP_INT_MAX : $posB;

            return $posA <=> $posB;
        });

        return $results;
    }

    // Categories | Sub Categories Configurations
    public function getSubCategoriesConfig($categoryId) 
    {
        $configValue = $this->scopeConfig->getValue(
            'marketplace_categories/subcategories_configuration/subcategories', ScopeInterface::SCOPE_STORE
        );

        // dd($configValue);

        $category = $this->mappingHelper->getCategoryById($categoryId);
        if ($category) {
            if ($category->getLevel() != 3) {
                $children = $category->getChildrenCategories();
                if ($children) {
                    $firstChild = $children->getFirstItem();
                    if ($firstChild) {
                        $categoryId = $firstChild->getId();
                    }
                }
            }
        }

        if ($configValue) {
            $prefixMappingArr = $this->serializer->unserialize($configValue);
            if ($prefixMappingArr) {
                foreach($prefixMappingArr as $data) {
                    if ($data['category_id'] == $categoryId) {
                        return $data;
                    }
                }
            }
        }

        return null;
    }

    public function getProductDetailsAttributeCodes($categoryUid) 
    {
        $configValueArr = $this->getArraySerializedConfigValue('marketplace_catalog/product_details/sequence');
        // die('<pre>'.print_r($configValueArr,true));
        if (!$configValueArr) {
            return null;
        }

        $categoryId = (int) base64_decode($categoryUid);

        // dd($categoryId);

        $keys = array_keys($configValueArr);
        $index = array_search($categoryId, array_column($configValueArr, 'category_id'));

        // dd($index);
        if ($index === false) {
            return null;
        }

        $attributes = $index !== false ? $configValueArr[$keys[$index]]['attributes'] : null;
        // dd($attributes);
        if (!$attributes) {
            return null;
        }

        $attributesArray = preg_split('/\r\n|\r|\n/', $attributes);
        $attributesArray = array_filter(array_map('trim', $attributesArray));
        // dd($attributesArray);

        return $attributesArray;
    }

    public function removePrefix($string, array $prefixes)
    {
        foreach ($prefixes as $prefix) {
            $pattern = $prefix . '_';
            if (strpos($string, $pattern) === 0) {
                return substr($string, strlen($pattern));
            }
        }
        return $string;
    }

    public function getProductInformationAttributeCodes($categoryUid) 
    {
        $configValueArr = $this->getArraySerializedConfigValue('marketplace_catalog/product_information/sequence');
        // die('<pre>'.print_r($configValueArr,true));
        if (!$configValueArr) {
            return null;
        }

        $categoryId = (int) base64_decode($categoryUid);

        // dd($categoryId);

        $keys = array_keys($configValueArr);
        $index = array_search($categoryId, array_column($configValueArr, 'category_id'));

        // dd($index);
        if ($index === false) {
            return null;
        }

        $attributes = $index !== false ? $configValueArr[$keys[$index]]['attributes'] : null;
        // dd($attributes);
        if (!$attributes) {
            return null;
        }

        $attributesArray = preg_split('/\r\n|\r|\n/', $attributes);
        $attributesArray = array_filter(array_map('trim', $attributesArray));
        // dd($attributesArray);

        return $attributesArray;
    }

    // PWA | Side Filters | Show/Hide & Sorting
    public function pwaFilteringAndSortingSideFilters($results, $categoryUid) 
    {
        if (!$categoryUid) {
            return $results;
        }

        $sequence = $this->getPWASideFiltersCodes($categoryUid);
        // dd($sequence);
        if (!$sequence) {
            return $results;
        }

        $systemAttributeCodes = [
            'sale',
            'category_uid',
            'price'
        ];

        // $sequence = array_merge($sequence, $systemAttributeCodes);

        // dd($results);
        
        $filteredResults = [];
        
        foreach ($results as $key => $item) {

            $order = $this->getOrderIndex($key, $sequence);

            if ($order === null) {
                // tidak match → SKIP
                continue;
            }

            // match → beri posisi
            $item['position'] = $order;

            if ($item['attribute_code'] == "ship_from_bucket") {
                $attrCode = 'ship_from';
                $attribute =  $this->mappingHelper->getAttribute($attrCode);
                if ($attribute) {
                    // dd($attribute->getOptions());
                    $item['label'] = $attribute->getFrontendLabel();
                    $item['attribute_code'] = $attrCode;
                }

                if (count($item['options'])) {
                    $map = [];

                    foreach ($attribute->getOptions() as $option) {
                        $map[$option->getValue()] = $option->getLabel();
                    }

                    // dd($map);

                    $newOptions = [];

                    foreach ($item['options'] as &$option) {
                        if (isset($map[$option['value']])) {
                            $option['label'] = $map[$option['value']];
                        }

                        $newOptions[] = $option;
                    }

                    // unset($item);

                    // dd($newOptions);

                    // $options = array_column($item['options'], 'value');

                    // $newOptions = [];

                    // foreach($attribute->getOptions() as $option) {
                    //     if (in_array($option->getValue(), $options)) {
                    //         $newOptions[] = $option;
                    //     }
                    // }

                    $item['options'] = $newOptions;
                }
            }

            // tambahkan tanpa mempertahankan key lama
            $filteredResults[] = $item;
        }

        // dd($filteredResults);

        return $filteredResults;
    }

    public function getMasterAttributes() 
    {
        $data = [];

        $collection = $this->eavAttributeCollectionFactory->create();
        $collection->getSelect()->join(
            ['cea' => 'catalog_eav_attribute'],
            'cea.attribute_id = main_table.attribute_id',
            []
        )
        ->where('main_table.entity_type_id = 4 AND cea.is_visible = 1 AND cea.is_filterable = 1 AND cea.is_master_attribute = 1')
        ->order('main_table.attribute_code ASC');
        if ($collection->getSize()) {
            foreach($collection as $model) {
                $data[] = [
                    'attribute_code' => $model->getAttributeCode()
                ];
            }
        }

        return $data;
    }


    public function getOrderIndex($key, $customOrder)
    {
        $key = (string) $key;
        $key = str_replace('_bucket', '', $key);

        foreach ($customOrder as $i => $item) {
            if (str_contains($key, $item)) {
                return $i + 1; // posisi mulai dari 1
            }
        }

        return null; // ga match
    }

    public function getPWASideFiltersCodes($categoryUid) 
    {
        $configValueArr = $this->getArraySerializedConfigValue('marketplace_catalog/side_filters/sequence');
        // die('<pre>'.print_r($configValueArr,true));
        if (!$configValueArr) {
            return null;
        }

        $categoryId = (int) base64_decode($categoryUid);

        // dd($categoryId);
        $category = $this->mappingHelper->getCategoryById($categoryId);
        if ($category) {
            if ($category->getLevel() != 3) {
                $children = $category->getChildrenCategories();
                if ($children) {
                    $firstChild = $children->getFirstItem();
                    if ($firstChild) {
                        $categoryId = $firstChild->getId();
                    }
                }
            }
        }

        // dd($categoryId);

        // dd($configValueArr);

        $keys = array_keys($configValueArr);
        $index = array_search($categoryId, array_column($configValueArr, 'category_id'));

        // dd($index);
        if ($index === false) {
            return null;
        }

        $filters = $index !== false ? $configValueArr[$keys[$index]]['filters'] : null;
        // dd($filters);
        if (!$filters) {
            return null;
        }

        $filtersArray = preg_split('/\r\n|\r|\n/', $filters);
        $filtersArray = array_filter(array_map('trim', $filtersArray));
        // dd($filtersArray);

        return $filtersArray;
    }

    public function getArraySerializedConfigValue($path) 
    {
        $configValue = $this->scopeConfig->getValue(
            $path, ScopeInterface::SCOPE_STORE
        );

        if ($configValue) {
            return $this->serializer->unserialize($configValue);
        }

        return [];
    }

    // Get my products url for redirection
    public function getMyProductsUrlByCategoryId($categoryId, $productData, $typeId = 'simple') 
    {
        $productTypeLabel = null;
        $myProductsUrlKey = null;

        $myProductsTabsUrlKey = 'index';
        
        $category = $this->mappingHelper->getCategoryById($categoryId);
        if ($category) {
            $mainCategoriesData = $this->mainCategoriesHelper->getMainCategoriesDataByCategories($category->getName());
            if (!$mainCategoriesData) {
                // dd('mainCategoriesData not found.');
            }

            if ($mainCategoriesData) {
                $tab = null;

                $myProductsUrlKey = $mainCategoriesData['myProductsUrlKey'] ?? null;
                $myProductsTabsAttrCode = $mainCategoriesData['myProductsTabsAttrCode'] ?? null;
                $preOrdersAttrCode = $mainCategoriesData['preOrdersAttrCode'] ?? null;
                $myProductsTabs = $mainCategoriesData['myProductsTabs'] ?? null;

                if ($myProductsTabsAttrCode) {
                    $myProductsTabsAttrVal = $productData[$myProductsTabsAttrCode] ?? null;
                    if (!$myProductsTabsAttrVal) {
                        $myProductsTabsAttrCode = $preOrdersAttrCode;
                        $myProductsTabsAttrVal = $productData[$preOrdersAttrCode] ?? null;
                    }

                    if ($myProductsTabsAttrVal) {
                        $myProductsTabsLabel = $this->mappingHelper->getOptionLabelById(
                            $myProductsTabsAttrCode, 
                            $myProductsTabsAttrVal
                        );

                        // dd($myProductsTabsLabel);
                        
                        if ($myProductsTabsLabel) {
                            $tab = $this->mainCategoriesHelper->getTabsByLabel($myProductsTabs, $myProductsTabsLabel);
                            if ($tab) {
                                $myProductsTabsUrlKey = $tab['value'];
                            }
                        }
                    }
                }
            }
        }

        if ($myProductsUrlKey) {
            if ($typeId == "configurable") {
                $myProductsUrlKey .= 'configurable';
            }

            return 'catalog/' . $myProductsUrlKey . '/' . $myProductsTabsUrlKey;
        }

        return 'catalog/dashboard/index';
    }

    public function filterMyProductsByTabs($collection) 
    {
        $this->initMyProductsData();

        $url = $this->getCurrentUrl();
        if (str_contains($url, "configurable")) {
            $collection->addFieldToFilter('type_id', 'configurable');
        } else {
            $collection->addFieldToFilter('type_id', 'simple');
        }

        $optionId = $this->mappingHelper->getOptionId($this->myProductsTabsAttrCode, $this->myProductsTabsLabel);
        $collection->addAttributeToFilter($this->myProductsTabsAttrCode, $optionId);

        return $collection;
    }

    // MY PRODUCTS TABS
    public function getCurrentTabs() {
        $currentUrl = $this->getCurrentUrl();
        if ($currentUrl) {
            if (str_contains($currentUrl,'preorders')) {
                return 'preorders';
            }

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

            // ACTION TOYS MODEL KITS
            if (str_contains($currentUrl,'actiontoysmodelkits')) {
                if (str_contains($currentUrl,'propsreplicakits')) {
                    return 'Props & Replica Kits';
                }

                if (str_contains($currentUrl,'robotmechakits')) {
                    return 'Robot/Mecha Kits';
                }

                if (str_contains($currentUrl,'monsterskits')) {
                    return 'Monsters Kits';
                }

                return 'Character Kits';
            }

            if (str_contains($currentUrl,'parts')) {
                return 'parts';
            }

            if (str_contains($currentUrl,'accessories')) {
                return 'accessories';
            }

            // BRICKS
            if (str_contains($currentUrl,'minifigures')) {
                return 'minifigures';
            }

            if (str_contains($currentUrl,'parts')) {
                return 'parts';
            }

            if (str_contains($currentUrl,'accessories')) {
                return 'accessories';
            }
        }

        return 'sealedproducts';
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

    // my products dataprovider
    public function getMyProductsListingDataProvider($collection) {
        $mainEntityMetadata = $this->getMyProductsMainEntityMetadata();
        // dd($mainEntityMetadata);
        if (!$mainEntityMetadata) {
            throw new \Exception(__('Main Entity Metadata not found.'));
        }

        $isAjax = $this->request->getParam('isAjax');

        $mainAttrCode = $mainEntityMetadata['main_attribute_code'] ?? null;

        $filters = $this->request->getParam('filters');
        if (!isset($filters[$mainAttrCode]) || !$isAjax) {
            return [
                "totalRecords" => 0,
                "items" => []
            ];
        }
        
        $sellerId = $this->helper->getSellerId();

        $productType = $this->getCurrentTabs();
        // dd($productType);

        
        
        
        // dd($mainAttrCode);

        $cacheKey = self::CACHE_TAG . '_' . $sellerId . '_' . $mainAttrCode . '_' . $productType . '_' . json_encode($filters);
        $cachedData = $this->cache->load($cacheKey);

        if ($cachedData) {
            // return unserialize($cachedData);
        }
        
        // Filter by category
        $collection = $this->filterMyProductsDataProviderByCategory(
            $collection, 
            $mainEntityMetadata
        );

        // dd($collection->getData());

        // Filter by seller
        $collection = $this->filterMyProductsDataProviderBySeller($collection, $sellerId);

        // dd($collection->getData());

        // Select all attributes
        // $collection->addAttributeToSelect(['*']);
        
        $attributeToSelect = ['bricks_brand','card_game','release_year','language_version','qty'];

        
        if ($mainAttrCode) {
            array_merge($attributeToSelect, [$mainAttrCode]);
        }

        
$prefix = '';
        
            $filters = $this->request->getParam('filters');
            // dd($filters);
            // dd($mainAttrCode);
            if (isset($filters[$mainAttrCode])) {
                // dd('ok');
                $mainAttrVal = $filters[$mainAttrCode];
// dd($mainAttrVal);
                $prefix = $this->getPrefix(
                    $mainAttrCode,
                    $mainAttrVal
                );

                // dd($prefix);

                if ($prefix) {
                    $currentTab = $this->currentMyProductsTab;
                    // dd($currentTab);
                    if ($currentTab) {
                        $myProductsListingAttributeCodes = $currentTab['attribute_codes'] ?? [];
                        // dd($myProductsListingAttributeCodes);
                        
                        if ($myProductsListingAttributeCodes) {
                            $myProductsListingAttributeCodesWithPrefix = [];
                            foreach($myProductsListingAttributeCodes as $attributeCode) {
                                // $myProductsListingAttributeCodesWithPrefix[] = $prefix . $attributeCode;
                                // dd($attributeCode);
                                $this->selectAttrWithPrefix($collection, $attributeCode);
                            }
                            // dd($myProductsListingAttributeCodesWithPrefix);
                            // $attributeToSelect = array_merge($attributeToSelect, $myProductsListingAttributeCodesWithPrefix);
                        }
                    }
                }
            }
// if ($mainEntityMetadata) {
            
        // }
        // dd($prefix);
        // dd($attributeToSelect);

        


        // $collection->addAttributeToSelect($attributeToSelect);

        // dd($productType);
        // dd($collection->toArray());
// 
        // Filter by product type
        // $collection = $this->filterMyProductsByTabs($collection);
        $collection = $this->filterMyProductsByTabs($collection);

        // dd($collection->getData());

        $customTable = $collection->getTable('catalog_product_entity_int');
        $collection->getSelect()->joinLeft(
            ['cpei' => $customTable],
            'e.entity_id = cpei.entity_id AND cpei.value = 1 AND cpei.attribute_id = 1304', // Ensure attribute_id matches
            [
                'value AS custom_value',
                'is_published' => new \Zend_Db_Expr("IF(cpei.value = 1, 1, IF(cpei.value IS NULL OR cpei.value = 2, 2, 1))")
            ]
        );

        // GROUP & SORT
        $collection->getSelect()->group('e.entity_id');
        $collection->getSelect()->order('entity_id desc');
        
        // ADD FILTER TO MAP
        $collection->addFilterToMap('is_published', 'cpei.value');

        // $collection = $this->setPagination(
        //     $collection
        // );

        // dd($collection->getSize());

        $dataResult = $collection->toArray();

        // dd($result);
        // dd(count($result));

        $result = [
            "totalRecords" => $collection->getSize(),
            "items" => $dataResult
        ];

        // dd($result);

        $this->cache->save(
            serialize($result),
            $cacheKey,
            [self::CACHE_TAG]
        );

        // die('<pre>'.print_r($collection->getFirstItem()->getData(),true));
        // die($collection->getSelect()->__toString());

        return $result;
    }
    //     $isAjax = $this->request->getParam('isAjax');
    //     if (!$isAjax) {
    //         return [
    //             "totalRecords" => 0,
    //             "items" => []
    //         ];
    //     }

    //     $sellerId = $this->helper->getSellerId();

    //     $productType = $this->getCurrentTabs();
    //     // dd($productType);

    //     $filters = $this->request->getParam('filters');
        
    //     $mainEntityMetadata = $this->getMyProductsMainEntityMetadata();
    //     // dd($mainEntityMetadata);
    //     if (!$mainEntityMetadata) {
    //         throw new \Exception(__('Main Entity Metadata not found.'));
    //     }

    //     $mainAttrCode = $mainEntityMetadata['main_attribute_code'] ?? null;
    //     // dd($mainAttrCode);

    //     $cacheKey = self::CACHE_TAG . '_' . $sellerId . '_' . $mainAttrCode . '_' . $productType . '_' . json_encode($filters);
    //     $cachedData = $this->cache->load($cacheKey);

    //     if ($cachedData) {
    //         // return unserialize($cachedData);
    //     }
        
    //     // Filter by category
    //     $collection = $this->filterMyProductsDataProviderByCategory(
    //         $collection, 
    //         $mainEntityMetadata
    //     );

    //     // dd($collection->getData());

    //     // Filter by seller
    //     $collection = $this->filterMyProductsDataProviderBySeller($collection, $sellerId);

    //     // dd($collection->getData());

    //     // Select all attributes
    //     // $collection->addAttributeToSelect(['*']);
        
    //     $attributeToSelect = ['bricks_brand','card_game','release_year','language_version','qty'];
    //     if ($mainAttrCode) {
    //         array_merge($attributeToSelect, [$mainAttrCode]);
    //     }
        
    //     $prefix = '';
        
    //     $filters = $this->request->getParam('filters');
    //     // dd($filters);
    //     // dd($mainAttrCode);
    //     if (isset($filters[$mainAttrCode])) {
    //         // dd('ok');
    //         $mainAttrVal = $filters[$mainAttrCode];
    //         // dd($mainAttrVal);
    //         $prefix = $this->getPrefix(
    //             $mainAttrCode,
    //             $mainAttrVal
    //         );

    //         // dd($prefix);

    //         if ($prefix) {
    //             $currentTab = $this->currentMyProductsTab;
    //             if ($currentTab) {
    //                 $myProductsListingAttributeCodes = $currentTab['attribute_codes'] ?? [];
    //                 // dd($myProductsListingAttributeCodes);
                    
    //                 if ($myProductsListingAttributeCodes) {
    //                     $myProductsListingAttributeCodesWithPrefix = [];
    //                     foreach($myProductsListingAttributeCodes as $attributeCode) {
    //                         // $myProductsListingAttributeCodesWithPrefix[] = $prefix . $attributeCode;
    //                         $this->selectAttrWithPrefix($collection, $attributeCode);
    //                     }
    //                     // dd($myProductsListingAttributeCodesWithPrefix);
    //                     // $attributeToSelect = array_merge($attributeToSelect, $myProductsListingAttributeCodesWithPrefix);
    //                 }
    //             }
    //         }
    //     }

    //     $collection->addAttributeToSelect($attributeToSelect);
        
    //     $collection = $this->filterMyProductsByTabs($collection);
        
    //     $customTable = $collection->getTable('catalog_product_entity_int');
    //     $collection->getSelect()->joinLeft(
    //         ['cpei' => $customTable],
    //         'e.entity_id = cpei.entity_id AND cpei.value = 1 AND cpei.attribute_id = 1304', // Ensure attribute_id matches
    //         [
    //             'value AS custom_value',
    //             'is_published' => new \Zend_Db_Expr("IF(cpei.value = 1, 1, IF(cpei.value IS NULL OR cpei.value = 2, 2, 1))")
    //         ]
    //     );

    //     // GROUP & SORT
    //     $collection->getSelect()->group('e.entity_id');
    //     $collection->getSelect()->order('entity_id desc');
        
    //     // ADD FILTER TO MAP
    //     $collection->addFilterToMap('is_published', 'cpei.value');

    //     $collection = $this->setPagination(
    //         $collection
    //     );

    //     // dd($collection->getSize());

    //     $dataResult = $collection->toArray();

    //     // dd($result);
    //     // dd(count($result));

    //     $result = [
    //         "totalRecords" => $collection->getSize(),
    //         "items" => $dataResult
    //     ];

    //     // dd($result);

    //     $this->cache->save(
    //         serialize($result),
    //         $cacheKey,
    //         [self::CACHE_TAG]
    //     );

    //     return $result;
    // }

    // SELLER | MY PRODUCTS | DATAPROVIDER
    public function getMyProductsDataProvider($collection, $productType2 = 'sealedproducts') 
    {
        $sellerId = $this->helper->getSellerId();

        $productType = $this->getCurrentTabs();
        // dd($productType);

        $filters = $this->request->getParam('filters');
        
        $mainEntityMetadata = $this->getMyProductsMainEntityMetadata();
        // dd($mainEntityMetadata);
        if (!$mainEntityMetadata) {
            throw new \Exception(__('Main Entity Metadata not found.'));
        }

        $mainAttrCode = $mainEntityMetadata['main_attribute_code'] ?? null;
        // dd($mainAttrCode);

        $cacheKey = self::CACHE_TAG . '_' . $sellerId . '_' . $mainAttrCode . '_' . $productType . '_' . json_encode($filters);
        $cachedData = $this->cache->load($cacheKey);

        if ($cachedData) {
            // return unserialize($cachedData);
        }
        
        // Filter by category
        $collection = $this->filterMyProductsDataProviderByCategory(
            $collection, 
            $mainEntityMetadata
        );

        // dd($collection->getData());

        // Filter by seller
        $collection = $this->filterMyProductsDataProviderBySeller($collection, $sellerId);

        // dd($collection->getData());

        // Select all attributes
        // $collection->addAttributeToSelect(['*']);
        
        $attributeToSelect = ['bricks_brand','card_game','release_year','language_version','qty'];

        
        if ($mainAttrCode) {
            array_merge($attributeToSelect, [$mainAttrCode]);
        }

        
$prefix = '';
        
            $filters = $this->request->getParam('filters');
            // dd($filters);
            // dd($mainAttrCode);
            if (isset($filters[$mainAttrCode])) {
                // dd('ok');
                $mainAttrVal = $filters[$mainAttrCode];
// dd($mainAttrVal);
                $prefix = $this->getPrefix(
                    $mainAttrCode,
                    $mainAttrVal
                );

                // dd($prefix);

                if ($prefix) {
                    // if ($mainAttrCode == "card_game") {
                    //     if ($productType == "singles") {
                    //         $attributeToSelect = array_merge($attributeToSelect, [
                    //             $prefix . 'card_name',
                    //             $prefix . 'card_set'
                    //         ]);
                    //     }

                    //     // dd($productType);

                    //     if ($productType == "sealedproducts") {
                    //         $attributeToSelect = array_merge($attributeToSelect, [
                    //             $prefix . 'card_release',
                    //             $prefix . 'card_series',
                    //             $prefix . 'sealed_products'
                    //         ]);
                    //     }
                    // }

                    $currentTab = $this->currentMyProductsTab;
                    if ($currentTab) {
                        $myProductsListingAttributeCodes = $currentTab['attribute_codes'] ?? [];
                        // dd($myProductsListingAttributeCodes);
                        
                        if ($myProductsListingAttributeCodes) {
                            $myProductsListingAttributeCodesWithPrefix = [];
                            foreach($myProductsListingAttributeCodes as $attributeCode) {
                                // $myProductsListingAttributeCodesWithPrefix[] = $prefix . $attributeCode;
                                $this->selectAttrWithPrefix($collection, $attributeCode);
                            }
                            // dd($myProductsListingAttributeCodesWithPrefix);
                            // $attributeToSelect = array_merge($attributeToSelect, $myProductsListingAttributeCodesWithPrefix);
                        }
                    }
                }
            }
// if ($mainEntityMetadata) {
            
        // }
        // dd($prefix);
        // dd($attributeToSelect);

        


        $collection->addAttributeToSelect($attributeToSelect);

        // dd($productType);
        // dd($collection->toArray());
// 
        // Filter by product type
        // $collection = $this->filterMyProductsByTabs($collection);
        $collection = $this->filterMyProductsByTabs($collection);

        // dd($collection->getData());

        $customTable = $collection->getTable('catalog_product_entity_int');
        $collection->getSelect()->joinLeft(
            ['cpei' => $customTable],
            'e.entity_id = cpei.entity_id AND cpei.value = 1 AND cpei.attribute_id = 1304', // Ensure attribute_id matches
            [
                'value AS custom_value',
                'is_published' => new \Zend_Db_Expr("IF(cpei.value = 1, 1, IF(cpei.value IS NULL OR cpei.value = 2, 2, 1))")
            ]
        );

        // GROUP & SORT
        $collection->getSelect()->group('e.entity_id');
        $collection->getSelect()->order('entity_id desc');
        
        // ADD FILTER TO MAP
        $collection->addFilterToMap('is_published', 'cpei.value');

        // $collection = $this->setPagination(
        //     $collection
        // );

        // dd($collection->getSize());

        $result = $collection->toArray();

        // dd($result);
        // dd(count($result));

        // $result = [
        //     "totalRecords" => $collection->getSize(),
        //     "items" => $dataResult
        // ];

        // dd($result);

        $this->cache->save(
            serialize($result),
            $cacheKey,
            [self::CACHE_TAG]
        );

        // die('<pre>'.print_r($collection->getFirstItem()->getData(),true));
        // die($collection->getSelect()->__toString());

        return $result;
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

    public function getParam($param) {
        return $this->request->getParam($param);
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

    public function getPrefixByMainAttr($mainAttrOptionLabel = null, $mainAttrCode = null, $mainAttrOptionId = null) 
    {
        // If main attribute option label is empty and main attribute option id is empty
        if (!$mainAttrOptionLabel && !$mainAttrOptionId) {
            return null;
        }
        
        // Get main attribute option label
        if (!$mainAttrOptionLabel) {
            $mainAttrOptionLabel = $this->mappingHelper->getOptionLabelById($mainAttrCode, $mainAttrOptionId);
        }

        // Get all prefixes from config
        $allPrefixConfigValueArr = $this->getAllPrefixes();
        if (!$allPrefixConfigValueArr) {
            return null;
        }

        foreach ($allPrefixConfigValueArr as $row) {
            // Get prefix
            if ($row['main_attr_label'] == $mainAttrOptionLabel) {
                return $row['prefix'];
            }
        }

        return null;
    }

    public function getPrefixByCategoryId($categoryId, $mainAttrOptionLabel = null, $mainAttrOptionId = null, $mainAttrCode = null) 
    {
        return $this->getPrefixByMainAttr(
            $mainAttrOptionLabel, 
            $mainAttrCode, 
            $mainAttrOptionId
        );
    }

    public function getPrefixesByCategoryId($categoryId) 
    {
        // Get all prefixes from config
        $allPrefixConfigValueArr = $this->getAllPrefixes();
        if (!$allPrefixConfigValueArr) {
            return [];
        }

        $result = array_column($allPrefixConfigValueArr, 'prefix');

        return $result;
    }

    public function getPrefix(
        $mainAttrCode, 
        $mainAttrVal
    ) {
        $prefix = '';

        $mainAttrLabel = $this->mappingHelper->getOptionLabelById($mainAttrCode, $mainAttrVal);

        if (!$mainAttrVal && !$mainAttrLabel) {
            return null;
        }

        // Get all prefixes from config
        $allPrefixConfigValueArr = $this->getAllPrefixes();
        if (!$allPrefixConfigValueArr) {
            return [];
        }

        foreach ($allPrefixConfigValueArr as $row) {
            if ($row['main_attr_label'] == $mainAttrLabel) {
                return $row['prefix'];
            }
        }

        return null;
    }

    public function getMainEntityMetadata($mainEntityKey) 
    {
        $data = [
            'category_url_key' => null,
            'product_type_attr_code' => null,
            'main_attribute_code' => null,
            'set_attribute_code' => null,
            'main_entity_code' => $mainEntityKey
        ];

        // action toys metadata
        if ($mainEntityKey == "actiontoys") {
            $data['category_url_key'] = 'action-toys';
            $data['product_type_attr_code'] = 'action_toys_categories';
            $data['main_attribute_code'] = 'toys_brand';
            $data['set_attribute_code'] = 'set_name';
        }

        if ($mainEntityKey == "actiontoysmodelkits") {
            $data['category_url_key'] = 'model-kits';
            $data['product_type_attr_code'] = 'action_toys_model_kits_categories';
            $data['main_attribute_code'] = 'toys_brand';
            $data['set_attribute_code'] = 'set_name';
        }

        if ($mainEntityKey == "animetoys") {
            $data['category_url_key'] = 'action-toys';
            $data['product_type_attr_code'] = 'toys_product_type';
            $data['main_attribute_code'] = 'toys_brand';
            $data['set_attribute_code'] = 'set_name';
        }

        if ($mainEntityKey == "animemodelkits") {
            $data['category_url_key'] = 'action-toys';
            $data['product_type_attr_code'] = 'toys_product_type';
            $data['main_attribute_code'] = 'toys_brand';
            $data['set_attribute_code'] = 'set_name';
        }

        if ($mainEntityKey == "gundam") {
            $data['category_url_key'] = 'action-toys';
            $data['product_type_attr_code'] = 'toys_product_type';
            $data['main_attribute_code'] = 'toys_brand';
            $data['set_attribute_code'] = 'set_name';
        }

        if ($mainEntityKey == "fungoods") {
            $data['category_url_key'] = 'action-toys';
            $data['product_type_attr_code'] = 'toys_product_type';
            $data['main_attribute_code'] = 'toys_brand';
            $data['set_attribute_code'] = 'set_name';
        }

        // Card game metadata
        if ($mainEntityKey == "cardgame") {
            $data['category_url_key'] = 'collectible-game';
            $data['product_type_attr_code'] = 'card_product_type';
            $data['main_attribute_code'] = 'card_game';
            $data['set_attribute_code'] = 'card_set';
        }

        // Card game metadata
        if ($mainEntityKey == "livingcardgame") {
            $data['category_url_key'] = 'living-card-games';
            $data['product_type_attr_code'] = 'card_product_type';
            $data['main_attribute_code'] = 'living_card_game';
            $data['set_attribute_code'] = 'card_set';
        }

        // Miniature game metadata
        if ($mainEntityKey == "miniaturegame") {
            $data['category_url_key'] = 'miniature-games';
            $data['product_type_attr_code'] = 'card_product_type';
            $data['main_attribute_code'] = 'miniature_game';
            $data['set_attribute_code'] = 'card_set';
        }

        if ($mainEntityKey == "gamesworkshop") {
            $data['category_url_key'] = 'games-workshop';
            $data['product_type_attr_code'] = 'card_product_type';
            $data['main_attribute_code'] = 'gw_miniature_game';
            $data['set_attribute_code'] = 'card_set';
        }

        // Sport cards metadata
        if ($mainEntityKey == "sportcards") {
            $data['category_url_key'] = 'sport-cards';
            $data['product_type_attr_code'] = 'sc_product_type';
            $data['main_attribute_code'] = 'sc_categories';
            $data['set_attribute_code'] = 'sc_set_name';
        }

        if ($mainEntityKey == "nonsportcards") {
            $data['category_url_key'] = 'non-sport-cards';
            $data['product_type_attr_code'] = 'sc_product_type';
            $data['main_attribute_code'] = 'sc_categories';
            $data['set_attribute_code'] = 'sc_set_name';
        }

        if ($mainEntityKey == "fashiondolls") {
            $data['category_url_key'] = 'dolls';
            $data['product_type_attr_code'] = 'sc_product_type';
            $data['main_attribute_code'] = 'sc_categories';
            $data['set_attribute_code'] = 'sc_set_name';
        }

        if ($mainEntityKey == "dollsaccessories") {
            $data['category_url_key'] = 'dolls-accessories';
            $data['product_type_attr_code'] = 'sc_product_type';
            $data['main_attribute_code'] = 'sc_categories';
            $data['set_attribute_code'] = 'sc_set_name';
        }

        if ($mainEntityKey == "railway") {
            $data['category_url_key'] = 'trains';
            $data['product_type_attr_code'] = 'sc_product_type';
            $data['main_attribute_code'] = 'sc_categories';
            $data['set_attribute_code'] = 'sc_set_name';
        }

        if ($mainEntityKey == "railwayaccessories") {
            $data['category_url_key'] = 'layout';
            $data['product_type_attr_code'] = 'sc_product_type';
            $data['main_attribute_code'] = 'sc_categories';
            $data['set_attribute_code'] = 'sc_set_name';
        }

        // Bricks metadata
        if ($mainEntityKey == "bricks") {
            $data['category_url_key'] = 'lego';
            $data['product_type_attr_code'] = 'bricks_categories';
            $data['main_attribute_code'] = 'bricks_brand';
            $data['set_attribute_code'] = 'bricks_set_name';
        }

        if ($mainEntityKey == "modelkitsvehicles") {
            $data['category_url_key'] = 'lego';
            $data['product_type_attr_code'] = 'bricks_categories';
            $data['main_attribute_code'] = 'bricks_brand';
            $data['set_attribute_code'] = 'bricks_set_name';
        }

        if ($mainEntityKey == "modelkitsaircrafts") {
            $data['category_url_key'] = 'lego';
            $data['product_type_attr_code'] = 'bricks_categories';
            $data['main_attribute_code'] = 'bricks_brand';
            $data['set_attribute_code'] = 'bricks_set_name';
        }

        if ($mainEntityKey == "modelkitsmilitary") {
            $data['category_url_key'] = 'lego';
            $data['product_type_attr_code'] = 'bricks_categories';
            $data['main_attribute_code'] = 'bricks_brand';
            $data['set_attribute_code'] = 'bricks_set_name';
        }

        if ($mainEntityKey == "diecastvehicles") {
            $data['category_url_key'] = 'lego';
            $data['product_type_attr_code'] = 'bricks_categories';
            $data['main_attribute_code'] = 'bricks_brand';
            $data['set_attribute_code'] = 'bricks_set_name';
        }

        if ($mainEntityKey == "diecastaircrafts") {
            $data['category_url_key'] = 'lego';
            $data['product_type_attr_code'] = 'bricks_categories';
            $data['main_attribute_code'] = 'bricks_brand';
            $data['set_attribute_code'] = 'bricks_set_name';
        }

        if ($mainEntityKey == "diecastmilitary") {
            $data['category_url_key'] = 'lego';
            $data['product_type_attr_code'] = 'bricks_categories';
            $data['main_attribute_code'] = 'bricks_brand';
            $data['set_attribute_code'] = 'bricks_set_name';
        }

        if ($mainEntityKey == "diecastaccessories") {
            $data['category_url_key'] = 'lego';
            $data['product_type_attr_code'] = 'bricks_categories';
            $data['main_attribute_code'] = 'bricks_brand';
            $data['set_attribute_code'] = 'bricks_set_name';
        }

        if ($mainEntityKey == "hobbysupplies") {
            $data['category_url_key'] = 'hobby_supplies_categories';
            $data['product_type_attr_code'] = 'bricks_categories';
            $data['main_attribute_code'] = 'hobby_supplies_brand';
            $data['set_attribute_code'] = '';
        }

        if ($mainEntityKey == "hobbysuppliescardsgames") {
            $data['category_url_key'] = 'hobby_supplies_cards_games_categories';
            $data['product_type_attr_code'] = 'bricks_categories';
            $data['main_attribute_code'] = 'hobby_supplies_brand';
            $data['set_attribute_code'] = '';
        }

        return $data;
    }

    public function getMyProductsMainEntityMetadata() 
    {
        $mainEntityMetadata = null;

        $url = $this->getCurrentUrl();

        // Action Toys
        if (str_contains($url, '/actiontoys/')) {
            $mainEntityMetadata = $this->getMainEntityMetadata('actiontoys');
        }

        if (str_contains($url, '/actiontoysmodelkits')) {
            $mainEntityMetadata = $this->getMainEntityMetadata('actiontoysmodelkits');
            // dd($mainEntityMetadata);
        }

        if (str_contains($url, '/animetoys/')) {
            $mainEntityMetadata = $this->getMainEntityMetadata('animetoys');
        }

        if (str_contains($url, '/animemodelkits')) {
            $mainEntityMetadata = $this->getMainEntityMetadata('animemodelkits');
        }

        // Action Toys
        if (str_contains($url, '/fungoods')) {
            $mainEntityMetadata = $this->getMainEntityMetadata('fungoods');
        }

        if (str_contains($url, '/gundam')) {
            $mainEntityMetadata = $this->getMainEntityMetadata('gundam');
        }

        // Card Game
        if (str_contains($url, '/cardgame')) {
            $mainEntityMetadata = $this->getMainEntityMetadata('cardgame');
        }

        // Living Card Game
        if (str_contains($url, '/livingcardgames')) {
            $mainEntityMetadata = $this->getMainEntityMetadata('livingcardgame');
        }

        // Living Card Game
        if (str_contains($url, '/miniaturegames')) {
            $mainEntityMetadata = $this->getMainEntityMetadata('miniaturegame');
        }

        if (str_contains($url, '/gamesworkshop')) {
            $mainEntityMetadata = $this->getMainEntityMetadata('gamesworkshop');
        }

        // Sport Cards
        if (str_contains($url, '/sportcards')) {
            $mainEntityMetadata = $this->getMainEntityMetadata('sportcards');
        }

        if (str_contains($url, '/nonsportcards')) {
            $mainEntityMetadata = $this->getMainEntityMetadata('nonsportcards');
        }

         if (str_contains($url, '/fashiondolls')) {
            $mainEntityMetadata = $this->getMainEntityMetadata('fashiondolls');
        }

        if (str_contains($url, '/dollsaccessories')) {
            $mainEntityMetadata = $this->getMainEntityMetadata('dollsaccessories');
        }

        if (str_contains($url, '/railway')) {
            $mainEntityMetadata = $this->getMainEntityMetadata('railway');
        }

        if (str_contains($url, '/railwayaccessories')) {
            $mainEntityMetadata = $this->getMainEntityMetadata('railwayaccessories');
        }

        if (str_contains($url, '/modelkitsaircrafts')) {
            $mainEntityMetadata = $this->getMainEntityMetadata('modelkitsaircrafts');
        }

        if (str_contains($url, '/modelkitsmilitary')) {
            $mainEntityMetadata = $this->getMainEntityMetadata('modelkitsmilitary');
        }

        if (str_contains($url, '/modelkitsvehicles')) {
            $mainEntityMetadata = $this->getMainEntityMetadata('modelkitsvehicles');
        }

        if (str_contains($url, '/diecastaircrafts')) {
            $mainEntityMetadata = $this->getMainEntityMetadata('diecastaircrafts');
        }

        if (str_contains($url, '/diecastmilitary')) {
            $mainEntityMetadata = $this->getMainEntityMetadata('diecastmilitary');
        }

        if (str_contains($url, '/diecastvehicles')) {
            $mainEntityMetadata = $this->getMainEntityMetadata('diecastvehicles');
        }

        if (str_contains($url, '/diecastaccessories')) {
            $mainEntityMetadata = $this->getMainEntityMetadata('diecastaccessories');
        }

        // Bricks
        if (str_contains($url, '/bricks')) {
            $mainEntityMetadata = $this->getMainEntityMetadata('bricks');
        }

        // Bricks
        if (str_contains($url, '/hobbysupplies/')) {
            $mainEntityMetadata = $this->getMainEntityMetadata('hobbysupplies');
        }

        if (str_contains($url, '/hobbysuppliescardsgames')) {
            $mainEntityMetadata = $this->getMainEntityMetadata('hobbysuppliescardsgames');
        }

        return $mainEntityMetadata;
    }

    public function filterMyProductsDataProviderByCategory(
        $collection, 
        $mainEntityMetadata
    ) {
        $filters = $this->request->getParam('filters') ?? [];
        if (!isset($filters['category_id'])) {

            $categoryId = $this->getCategoryIdByMainAttrCode($mainEntityMetadata['main_entity_code']);
            if (!$categoryId) {
                throw new \Exception(__('Categories configuration is not found.'));
            }

            $collection->addCategoriesFilter(['in' => [$categoryId]]);
        }

        return $collection;
    }

    public function getCategoryIdByMainAttrCode($mainEntityKey) {
        $categoryId = $this->scopeConfig->getValue(
            'categories/' . $mainEntityKey . '/category_id', ScopeInterface::SCOPE_STORE
        );
        
        return $categoryId;
    }

    public function filterMyProductsDataProviderBySeller(
        $collection,
        $sellerId
    ) {
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

    public function getPrefixes() {
        $mainEntityMetadata = $this->getMyProductsMainEntityMetadata();
        if (!$mainEntityMetadata) {
            throw new \Exception(__('Main Entity Metadata not found.'));
        }

        // dd($mainEntityMetadata);

        $category_url_key = $mainEntityMetadata['category_url_key'] ?? null;
        if ($category_url_key) {
            // dd($category_url_key);
            $categoryId = null;

            $category = $this->mappingHelper->getCategoryByUrlKey($category_url_key);
            if ($category) {
                $categoryId = (int)$category->getId();
            }

            return $this->getPrefixesByCategoryId($categoryId);
        }
    }
    
    public function removeMyProductsDataProviderPrefix(
        $productData
    ) {
        $prefixes = $this->getPrefixes();

        $cleanedRows = [];

        foreach ($productData as $row) {
            $cleanedRow = [];
            foreach ($row as $key => $value) {
                $newKey = $key;
                foreach ($prefixes as $prefix) {
                    if (strpos($key, $prefix) === 0) {
                        $newKey = substr($key, strlen($prefix));
                        break;
                    }
                }
                $cleanedRow[$newKey] = $value;
            }
            $cleanedRows[] = $cleanedRow;
        }

        // dd($cleanedRows);

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

    public function getOptionLabelById($optionId)
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
            ->where('o.option_id = (?)', $optionId)
            ->where('v.store_id = ?', 0); // global label

        $data = $connection->fetchRow($select);

        return $data['label'] ?? null;
    }
    
    public function getColumnOptions($attrCode, $isUsingPrefix = false) 
    {
        // dd('getColumnOptions');

        $prefix = '';

        $mainAttrLabel = $this->coreRegistry->registry('currentMainAttrLabel');
        $mainAttrVal = $this->coreRegistry->registry('currentMainAttrVal');
        $mainAttrCode = $this->coreRegistry->registry('currentMainAttrCode');
        if ($mainAttrLabel && $mainAttrCode && $isUsingPrefix) {
            // dd($mainAttrLabel);
            $prefix = $this->getPrefixByMainAttrLabel(
                $mainAttrLabel
            );
        }

        // Attribute Code with prefix
        if ($isUsingPrefix) {
            $currentAttrCodeWithPrefix = $prefix . $attrCode;

            /** @var \Magento\Framework\App\ResourceConnection $resource */
            $connection = $this->resource->getConnection();

            $lofTable      = $this->resource->getTableName('lof_marketplace_product');
            $productEntity = $this->resource->getTableName('catalog_product_entity');
            $intTable      = $this->resource->getTableName('catalog_product_entity_int');
            $eavAttr       = $this->resource->getTableName('eav_attribute');
            $optionValue   = $this->resource->getTableName('eav_attribute_option_value');

            $sellerId = $this->helper->getSellerId();

            // 🔹 Ambil semua attribute_id yang match LIKE
            $selectAttr = $connection->select()
                ->from($eavAttr, ['attribute_id', 'attribute_code'])
                ->where('entity_type_id = ?', 4);

            $likeConds = [];
            $likeConds[] = $connection->quoteInto('attribute_code LIKE ?', '%_' . $attrCode);

            $selectAttr->where(implode(' OR ', $likeConds));

            $attributes = $connection->fetchAll($selectAttr);

            // 🔹 Simpan ID & code-nya
            $attributeIds = array_column($attributes, 'attribute_id');
            $attributeMap = array_column($attributes, 'attribute_code', 'attribute_id');

            // dd($attributeIds);

            if (empty($attributeIds)) {
                return []; // tidak ada attribute yang match
            }

            // 🔹 Ambil semua value unik + label untuk semua attribute itu
            $select = $connection->select()
                ->from(['lmp' => $lofTable], [])
                ->join(
                    ['cpei' => $intTable],
                    'cpei.entity_id = lmp.product_id AND cpei.attribute_id IN (' . implode(',', $attributeIds) . ')',
                    [
                        'attribute_id' => 'cpei.attribute_id',
                        'value' => 'cpei.value'
                    ]
                )
                ->joinLeft(
                    ['eaov' => $optionValue],
                    'eaov.option_id = cpei.value',
                    ['label' => 'eaov.value']
                )
                ->where('lmp.seller_id = ?', $sellerId)
                ->group(['cpei.attribute_id', 'cpei.value'])
                ->order('eaov.value ASC');

            $rows = $connection->fetchAll($select);

            // 🔹 Bentuk hasilnya biar rapi per attribute_code
            $optionsByAttrCode = [];
            $allOptions = [];
            foreach ($rows as $row) {
                $attrCodeWithPrefix = $attributeMap[$row['attribute_id']] ?? 'unknown';
                $optionsByAttrCode[$attrCodeWithPrefix][] = [
                    'value' => $row['value'],
                    'label' => $row['label']
                ];

                $allOptions[] = [
                    'value' => $row['value'],
                    'label' => $row['label']
                ];
            }

            

            // dd($rows);

            // dd($optionsByAttrCode);
            $options = $optionsByAttrCode[$currentAttrCodeWithPrefix];

            // $this->cache->save(
            //     serialize($result),
            //     $cacheKey,
            //     [self::CACHE_TAG]
            // );

            if (!$this->coreRegistry->registry($currentAttrCodeWithPrefix)) {
                $this->coreRegistry->register($currentAttrCodeWithPrefix, $options);
            }

            if (!$this->coreRegistry->registry($attrCode)) {
                $this->coreRegistry->register($attrCode, $allOptions);
            }

            // dd($options);

            
            // if ($mainAttrLabel && $mainAttrCode && $isUsingPrefix) {
            if ($attrCode == "card_game" && !$mainAttrLabel) {
                $this->coreRegistry->register('currentMainAttrCode', 'card_game');
                $this->coreRegistry->register('currentMainAttrVal', $options[0]['value']);
                $this->coreRegistry->register('currentMainAttrLabel', $options[0]['label']);
                // return [];
            } else {
                // dd($options);
            }

            return $allOptions;
        }


        // Attribute code without prefix
        $mainAttrLabel = $this->coreRegistry->registry('currentMainAttrLabel');
        $mainAttrVal = $this->coreRegistry->registry('currentMainAttrVal');
        $mainAttrCode = $this->coreRegistry->registry('currentMainAttrCode');

        /** @var \Magento\Framework\App\ResourceConnection $resource */
        $connection = $this->resource->getConnection();

        $lofTable      = $this->resource->getTableName('lof_marketplace_product');
        $productEntity = $this->resource->getTableName('catalog_product_entity');
        $intTable      = $this->resource->getTableName('catalog_product_entity_int');
        $eavAttr       = $this->resource->getTableName('eav_attribute');
        $optionValue   = $this->resource->getTableName('eav_attribute_option_value');

        // 🔹 Dapatkan attribute_id untuk card_game
        $attributeId = (int)$connection->fetchOne(
            $connection->select()
                ->from($eavAttr, 'attribute_id')
                ->where('attribute_code = ?', $attrCode)
                ->where('entity_type_id = ?', 4) // 4 untuk catalog_product
        );

        // dd($attributeId);

        // 🔹 Filter untuk seller_id = 6
        $sellerId = $this->helper->getSellerId();

        // 🔹 Ambil option unik yang dipakai seller
        $select = $connection->select()
            ->from(['lmp' => $lofTable], [])
            ->join(
                ['cpei' => $intTable],
                'cpei.entity_id = lmp.product_id AND cpei.attribute_id = ' . $attributeId,
                ['value' => 'cpei.value']
            )
            ->joinLeft(
                ['eaov' => $optionValue],
                'eaov.option_id = cpei.value',
                ['label' => 'eaov.value']
            )
            ->where('lmp.seller_id = ?', $sellerId)
            ->group('cpei.value') // biar unik, 1 baris per option
            ->order('eaov.value ASC');

        $options = $connection->fetchAll($select);

        // dd($options);

        
        // if ($mainAttrLabel && $mainAttrCode && $isUsingPrefix) {
        if ($attrCode == "card_game" && !$mainAttrLabel) {
            $this->coreRegistry->register('currentMainAttrCode', 'card_game');
            $this->coreRegistry->register('currentMainAttrVal', $options[0]['value']);
            $this->coreRegistry->register('currentMainAttrLabel', $options[0]['label']);
            // return [];
        }
        
        return $options;
    }

    public function getCurrentProductTypeLabel($mainEntityMetadata = null) 
    {
        $url = $this->urlBuilder->getCurrentUrl();

        if ($mainEntityMetadata) {
            $mainEntityCode = $mainEntityMetadata['main_entity_code'];
            if ($mainEntityCode == "actiontoys") {
                if (str_contains($url, "index")) {
                    return 'Action Figures';
                }
            }
        }

        if (str_contains($url, "lots")) {
            return 'Lots/Sets';
        }

        if (str_contains($url, "supplies")) {
            return 'Supplies';
        }

        if (str_contains($url, "preorders")) {
            return 'Pre Orders';

            if (str_contains($url, "sealedproducts")) {
                return 'Sealed Products';
            } elseif (str_contains($url, "singles")) {
                return 'Singles';
            }
        } elseif (str_contains($url, "index")) {
            return 'Sealed Products';
        } elseif (str_contains($url, "singles")) {
            return 'Singles';
        }

        return null;
    }

    public function getCurrentUrlKey() {
        $currentUrl = $this->getCurrentUrl();
        if ($currentUrl) {
            if (preg_match('#/catalog/([^/]+)/[^/]+/?$#', $currentUrl, $matches)) {
                return $matches[1];
            }
        }

        return null;
    }

    public function getCurrentTabUrlKey() {
        $currentUrl = $this->getCurrentUrl();
        if ($currentUrl) {
            if (preg_match('#/catalog/[^/]+/([^/]+)/?$#', $currentUrl, $matches)) {
                return $matches[1];
            }
        }

        return null;
    }

    public function getCurrentMyProductsMainCategoriesData() {
        $urlKey = $this->getCurrentUrlKey();
        if ($urlKey) {
            $mainCategoriesData = $this->mainCategoriesHelper->getMainCategoriesDataByUrlKey($urlKey);
            if ($mainCategoriesData) {
                return $mainCategoriesData;
            }
        }

        return null;
    }

    public function getCurrentMyProductsTabLabel($tabs) 
    {
        $urlKey = $this->getCurrentTabUrlKey();
        if ($urlKey) {
            $tab = $this->mainCategoriesHelper->getTabsByUrlKey($tabs, $urlKey);
            if ($tab) {
                return (string) $tab['label'];
            }
        }

        return null;
    }

    public function getCurrentMyProductsTabs($tabs) 
    {
        $urlKey = $this->getCurrentTabUrlKey();
        if ($urlKey) {
            $tab = $this->mainCategoriesHelper->getTabsByUrlKey($tabs, $urlKey);
            if ($tab) {
                return $tab;;
            }
        }

        return null;
    }

    public function getFilterOptionsWithPrefix($attrCode) 
    {
        $prefix = '';

        $mainAttrLabel = $this->coreRegistry->registry('currentMainAttrLabel');
        $mainAttrVal = $this->coreRegistry->registry('currentMainAttrVal');
        $mainAttrCode = $this->coreRegistry->registry('currentMainAttrCode');

        // dd($mainAttrCode);

        if ($mainAttrLabel && $mainAttrCode) {
            $prefix = $this->getPrefixByMainAttr(
                $mainAttrLabel
            );
        }

        // dd($prefix);

        $currentAttrCodeWithPrefix = $prefix . $attrCode;

        // dd($currentAttrCodeWithPrefix);

        /** @var \Magento\Framework\App\ResourceConnection $resource */
        $connection = $this->resource->getConnection();

        $lofTable      = $this->resource->getTableName('lof_marketplace_product');
        $productEntity = $this->resource->getTableName('catalog_product_entity');
        $intTable      = $this->resource->getTableName('catalog_product_entity_int');
        $eavAttr       = $this->resource->getTableName('eav_attribute');
        $optionValue   = $this->resource->getTableName('eav_attribute_option_value');

        $sellerId = $this->helper->getSellerId();

        // 🔹 Daftar attribute_code yang mau dicari (bisa LIKE)
        $attrCodes = ['card_game', 'toys_brand', 'category_custom'];

        // 🔹 Ambil semua attribute_id yang match LIKE
        $selectAttr = $connection->select()
            ->from($eavAttr, ['attribute_id', 'attribute_code'])
            ->where('entity_type_id = ?', 4);

        $likeConds = [];
        // foreach ($attrCodes as $code) {
            // contoh: LIKE '%_card_game'
            $likeConds[] = $connection->quoteInto('attribute_code LIKE ?', '%_' . $attrCode);
        // }

        $selectAttr->where(implode(' OR ', $likeConds));

        $attributes = $connection->fetchAll($selectAttr);

        // 🔹 Simpan ID & code-nya
        $attributeIds = array_column($attributes, 'attribute_id');
        $attributeMap = array_column($attributes, 'attribute_code', 'attribute_id');

        if (empty($attributeIds)) {
            return []; // tidak ada attribute yang match
        }

        // 🔹 Ambil semua value unik + label untuk semua attribute itu
        $select = $connection->select()
            ->from(['lmp' => $lofTable], [])
            ->join(
                ['cpei' => $intTable],
                'cpei.entity_id = lmp.product_id AND cpei.attribute_id IN (' . implode(',', $attributeIds) . ')',
                [
                    'attribute_id' => 'cpei.attribute_id',
                    'value' => 'cpei.value'
                ]
            )
            ->joinLeft(
                ['eaov' => $optionValue],
                'eaov.option_id = cpei.value',
                ['label' => 'eaov.value']
            )
            ->where('lmp.seller_id = ?', $sellerId)
            ->group(['cpei.attribute_id', 'cpei.value'])
            ->order('eaov.value ASC');

        $rows = $connection->fetchAll($select);

        // 🔹 Bentuk hasilnya biar rapi per attribute_code
        $optionsByAttrCode = [];
        $allOptions = [];
        foreach ($rows as $row) {
            $attrCodeWithPrefix = $attributeMap[$row['attribute_id']] ?? 'unknown';
            $optionsByAttrCode[$attrCodeWithPrefix][] = [
                'value' => $row['value'],
                'label' => $row['label']
            ];

            $allOptions[] = [
                'value' => $row['value'],
                'label' => $row['label']
            ];
        }

        

        // return $result;

        // dd($optionsByAttrCode);
        $options = $optionsByAttrCode[$currentAttrCodeWithPrefix] ?? [];

        // $this->cache->save(
        //     serialize($result),
        //     $cacheKey,
        //     [self::CACHE_TAG]
        // );

        if ($this->coreRegistry->registry($currentAttrCodeWithPrefix) !== null) {
            $this->coreRegistry->register($currentAttrCodeWithPrefix, $options);
        }

        if ($this->coreRegistry->registry($attrCode) !== null) {
            $this->coreRegistry->register($attrCode, $allOptions);
        }

        return $allOptions;
    }

    public function getFilterOptionsWithoutPrefix($attrCode) 
    {
        $mainAttrLabel = $this->coreRegistry->registry('currentMainAttrLabel');
        $mainAttrVal = $this->coreRegistry->registry('currentMainAttrVal');
        $mainAttrCode = $this->coreRegistry->registry('currentMainAttrCode');

        /** @var \Magento\Framework\App\ResourceConnection $resource */
        $connection = $this->resource->getConnection();

        $lofTable      = $this->resource->getTableName('lof_marketplace_product');
        $productEntity = $this->resource->getTableName('catalog_product_entity');
        $intTable      = $this->resource->getTableName('catalog_product_entity_int');
        $eavAttr       = $this->resource->getTableName('eav_attribute');
        $optionValue   = $this->resource->getTableName('eav_attribute_option_value');

        // 🔹 Dapatkan attribute_id untuk card_game
        $attributeId = (int)$connection->fetchOne(
            $connection->select()
                ->from($eavAttr, 'attribute_id')
                ->where('attribute_code = ?', $attrCode)
                ->where('entity_type_id = ?', 4)
        );

        // dd($attrCode);

        // 🔹 Dapatkan attribute_id untuk card_product_type
        $cardProductTypeId = (int)$connection->fetchOne(
            $connection->select()
                ->from($eavAttr, 'attribute_id')
                ->where('attribute_code = ?', $this->myProductsTabsAttrCode)
                ->where('entity_type_id = ?', 4)
        );

        // dd($this->myProductsTabsAttrCode);

        // 🔹 Dapatkan option_id untuk label "Sealed Products"
        $sealedOptionId = (int)$connection->fetchOne(
            $connection->select()
                ->from(['eaov' => $optionValue], 'eaov.option_id')
                ->join(
                    ['eao' => $this->resource->getTableName('eav_attribute_option')],
                    'eao.option_id = eaov.option_id',
                    []
                )
                ->where('eaov.value = ?', $this->myProductsTabsLabel)
                ->where('eao.attribute_id = ?', $cardProductTypeId)
        );

        // dd($cardProductTypeId . ' dan ' . $productTypeLabel);

        // dd($productTypeLabel);

        // 🔹 Filter untuk seller_id
        $sellerId = $this->helper->getSellerId();

        // 🔹 Ambil option unik untuk seller, hanya yang card_product_type = "Sealed Products"
        $select = $connection->select()
            ->from(['lmp' => $lofTable], [])
            ->join(
                ['cpei' => $intTable],
                'cpei.entity_id = lmp.product_id AND cpei.attribute_id = ' . $attributeId,
                ['value' => 'cpei.value']
            )
            ->joinLeft(
                ['eaov' => $optionValue],
                'eaov.option_id = cpei.value',
                ['label' => 'eaov.value']
            )
            // 🔹 Join tambahan untuk card_product_type
            ->join(
                ['cpet' => $intTable],
                'cpet.entity_id = lmp.product_id AND cpet.attribute_id = ' . $cardProductTypeId,
                []
            )
            // 🔹 Filter hanya produk dengan card_product_type = Sealed Products
            ->where('cpet.value = ?', $sealedOptionId)
            ->where('lmp.seller_id = ?', $sellerId)
            ->group('cpei.value')
            ->order('lmp.updated_at ASC');

        $options = $connection->fetchAll($select);

        // die($select->__toString());
            // dd($options);
            $mainAttrCodes = $this->mainCategoriesHelper->getMainAttrCodes();

        // dd($mainAttrCodes);

        // 🔹 Inisialisasi default jika belum ada mainAttr
        if ($options && in_array($attrCode, $mainAttrCodes) && !$mainAttrLabel) {
            $this->coreRegistry->register('currentMainAttrCode', $attrCode);
            $this->coreRegistry->register('currentMainAttrVal', $options[0]['value']);
            $this->coreRegistry->register('currentMainAttrLabel', $options[0]['label']);
        }

        // dd($options);
        
        return $options;
    }

    public function getFilterOptions($attrCode, $isUsingPrefix = false) 
    {
        $this->initMyProductsData();

        // Attribute Code with prefix
        if ($isUsingPrefix) {
            // dd($attrCode);
            return $this->getFilterOptionsWithPrefix($attrCode);
        }

        // Attribute code without prefix
        return $this->getFilterOptionsWithoutPrefix($attrCode);
    }

    public function initMyProductsData() 
    {
        if (!$this->mainCategoriesData) {
            $this->mainCategoriesData = $this->getCurrentMyProductsMainCategoriesData();
        }
        
        

        if (!$this->myProductsTabs) {
            $this->myProductsTabs = $this->mainCategoriesData['myProductsTabs'] ?? null;
        }

        if (!$this->currentMyProductsTab) {
            $this->currentMyProductsTab = $this->getCurrentMyProductsTabs($this->myProductsTabs);
        }

        

        if (!$this->myProductsTabsLabel) {
            $this->myProductsTabsLabel = $this->getCurrentMyProductsTabLabel($this->myProductsTabs);
        }

        if (!$this->myProductsTabsAttrCode) {
            $this->myProductsTabsAttrCode = $this->mainCategoriesData['myProductsTabsAttrCode'] ?? null;
            if ($this->myProductsTabsLabel == "Pre Orders") {
                $this->myProductsTabsAttrCode = $this->mainCategoriesData['preOrdersAttrCode'] ?? null;
            }
        }

        // dd($this->myProductsTabsAttrCode);
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
        $this->filterMyProductsByTabs($sellerProductCollection);

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
        $this->filterMyProductsByTabs($collection);

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

        // dd($attributeIds);

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