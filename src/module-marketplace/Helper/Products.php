<?php

namespace TCGCollective\MarketPlace\Helper;

use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\CacheInterface;
use Lof\MarketPlace\Model\Seller;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Catalog\Model\Product\Action as ProductAction;

class Products extends \Magento\Framework\App\Helper\AbstractHelper
{
    const CACHE_TAG = 'seller_products_cache';

    protected $request;
    protected $jsonFactory;
    protected $productCollectionFactory;
    protected $imageHelper;
    protected $mainCategoriesConfigProvider;
    protected $productListingCategoryConfigFactory;
    protected $productListingCategoryColumnFactory;
    protected $productListingCategoryTabFactory;
    protected $productListingCategoryTabColumnFactory;
    protected $mappingHelper;
    protected $marketplaceSellerHelper;
    protected $productRepository;
    protected $messageManager;
    protected $productAction;
    protected $storeManager;
    protected $websiteCollectionCache;
    protected $priceCurrency;
    protected $configurableType;

    protected $platformDatabaseConfig = null;

    protected $redirect;
    protected $helper;    
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
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Helper\Image $imageHelper,
        \TCGCollective\PlatformDatabase\Model\MainCategoriesConfigProvider $mainCategoriesConfigProvider,
        \TCGCollective\MarketPlace\Model\ProductListingCategoryConfigFactory $productListingCategoryConfigFactory,
        \TCGCollective\MarketPlace\Model\ProductListingCategoryColumnFactory $productListingCategoryColumnFactory,
        \TCGCollective\MarketPlace\Model\ProductListingTabFactory $productListingCategoryTabFactory,
        \TCGCollective\MarketPlace\Model\ProductListingTabColumnFactory $productListingCategoryTabColumnFactory,
        \TCGCollective\ProductAttributesLink\Helper\Data $mappingHelper,
        \TCGCollective\MarketPlace\Helper\Seller $marketplaceSellerHelper,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        ProductAction $productAction,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \TCGCollective\MarketPlace\Model\WebsiteCollectionCache $websiteCollectionCache,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurableType,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,

        \Magento\Framework\App\Response\RedirectInterface $redirect,
        \Lof\MarketPlace\Helper\Data $helper,
        \Magento\Framework\Serialize\SerializerInterface $serializer,
        \Magento\Framework\App\ResourceConnection $resource,
        CacheInterface $cache,
        \Magento\Framework\Registry $registry,
        \TCGCollective\MarketPlace\Helper\MainCategoriesHelper $mainCategoriesHelper,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory $eavAttributeCollectionFactory,
        \Lof\MarketPlace\Model\ResourceModel\Seller\CollectionFactory $sellerCollectionFactory
    ) {
        $this->redirect = $redirect;
        $this->helper = $helper;
        $this->urlBuilder = $context->getUrlBuilder();
        $this->serializer = $serializer;
        $this->resource = $resource;
        $this->cache = $cache;
        $this->coreRegistry = $registry;
        $this->mainCategoriesHelper = $mainCategoriesHelper;
        $this->eavAttributeCollectionFactory = $eavAttributeCollectionFactory;
        $this->sellerCollectionFactory = $sellerCollectionFactory;
        $this->productRepository = $productRepository;
        $this->messageManager = $messageManager;
        $this->productAction = $productAction;
        $this->storeManager = $storeManager;
        $this->websiteCollectionCache = $websiteCollectionCache;
        $this->priceCurrency = $priceCurrency;
        $this->configurableType = $configurableType;

        $this->request = $request;
        $this->jsonFactory = $jsonFactory;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->imageHelper = $imageHelper;
        $this->mainCategoriesConfigProvider = $mainCategoriesConfigProvider;
        $this->productListingCategoryConfigFactory = $productListingCategoryConfigFactory;
        $this->productListingCategoryColumnFactory = $productListingCategoryColumnFactory;
        $this->productListingCategoryTabFactory = $productListingCategoryTabFactory;
        $this->productListingCategoryTabColumnFactory = $productListingCategoryTabColumnFactory;
        $this->mappingHelper = $mappingHelper;
        $this->marketplaceSellerHelper = $marketplaceSellerHelper;
        parent::__construct(
            $context
        );
    }

    public function getProductListingTabsArray($categoryConfigId, $attributeCode) 
    {
        $result = [];

        $collection = $this->productListingCategoryTabFactory->create()
        ->getCollection();
        $collection->addFieldToFilter('category_config_id', $categoryConfigId);
        if ($collection->getSize()) {
            $optionLabelMapping = [];

            $attribute = $this->getProductAttribute($attributeCode);
            $options = $attribute->getOptions();
            if ($options) {
                foreach($options as $option) {
                    $optionLabelMapping[$option->getValue()] = $option->getLabel();
                }
            }

            foreach($collection as $tab) {
                $tabData = $tab->getData();

                $tabData['label'] = $optionLabelMapping[$tabData['tab_value']];

                $result[] = $tabData;
            }
        }

        return $result;
    }

    public function removeCacheByKey($cacheKey) {
        $this->cache->remove($cacheKey);
    }

    // GET PUBLISH DATA AJAX
    public function getPublishData($productId, $currentWebsiteIds = []) 
    {
        $result = [
            'success' => false,
            'messages' => '',
            'product_id' => null
        ];

        if (!$productId) {
            $result['messages'] = __('Please specify the product to publish.');
            return $result;
        }

        $result['product_id'] = $productId;

        $cacheKey = self::CACHE_TAG . '_publish_data_' . $productId;

        $cachedData = $this->cache->load($cacheKey);
        if ($cachedData) {
            $result['success'] = true;
            $result['websites'] = unserialize($cachedData);
    
            return $result;
        }

        $seller = $this->marketplaceSellerHelper->getCurrentSeller();

        $listingWebsiteIds = isset($seller['listing_website_ids']) && $seller['listing_website_ids'] ? 
        explode(",", $seller['listing_website_ids']) : [];

        $defaultWebsiteId = $this->storeManager->getStore()->getWebsite()->getId();
        
        $publishToDefaultCountry = false;
        if ($currentWebsiteIds && in_array($defaultWebsiteId, $currentWebsiteIds)) {
            $publishToDefaultCountry = true;
        }

        $publishDataArr = [];

        $websiteCollection = $this->websiteCollectionCache->getWebsiteCollection();
        if ($websiteCollection) {
            $websites = $websiteCollection['items'] ?? [];
            if ($websites) {
                foreach ($websites as $website) {
                    $publishData = [];
                    $websiteName = $website['name'] ?? null;
                    
                    $websiteId = $website['website_id'] ?? null;
                    if (!$websiteId) {
                        continue;
                    }

                    $websiteObj = $this->storeManager->getWebsite($websiteId);
                    $storeId = $websiteObj->getDefaultStore()->getId();
                    $currencyCode = $websiteObj->getBaseCurrencyCode();

                    $publishData['id'] = $websiteId;
                    $publishData['name'] = $websiteName;
                    $publishData['currency'] = $currencyCode;
                    $publishData['price'] = $this->getProductPriceByWebsiteId($productId, $storeId);

                    if ($websiteId == $defaultWebsiteId) {
                        $publishData['is_default'] = true;
                        $publishData['allowed'] = true;
                    } else {
                        $publishData['is_default'] = false;
                        if (in_array($websiteId, $listingWebsiteIds)) {
                            $publishData['allowed'] = true;
                        } else {
                            $publishData['allowed'] = false;
                        }
                    }

                    if ($currentWebsiteIds && in_array($websiteId, $currentWebsiteIds)) {
                        $publishData['published'] = true;
                    } else {
                        $publishData['published'] = false;
                    }

                    $publishData['is_default_country_published'] = $publishToDefaultCountry;

                    $publishDataArr[] = $publishData;
                }
            }
        }

        // Sort by default countries/website and status
        usort($publishDataArr, fn($a, $b) => $b['is_default'] <=> $a['is_default']);
        usort($publishDataArr, fn($a, $b) => $b['allowed'] <=> $a['allowed']);

        // Save to cache
        $this->cache->save(
            serialize($publishDataArr),
            $cacheKey,
            [self::CACHE_TAG]
        );
        
        // Convert to JSON
        $result['success'] = true;
        $result['websites'] = $publishDataArr;
        
        return $result;
    }

    // PUBLISH PRODUCT AJAX
    public function publishProduct($productId, $publishDataArr) 
    {
        $result = [
            'success' => false,
            'messages' => ''
        ];

        if (!$productId) {
            $result['messages'] = __('Please specify the product to publish.');
            return $result;
        }

        if (!$publishDataArr) {
            $result['messages'] = __('Please specify the publish data.');
            return $result;
        }

        $product = $this->productRepository->getById($productId);
        if (!$product) {
            $result['messages'] = __('Product not found.');
            return $result;
        }

        $newWebsiteIds = [];

        // Get published website ids
        foreach ($publishDataArr as $publishData) 
        {
            $websiteId = $publishData['website_id'];
            $price     = $publishData['price'];

            $newWebsiteIds[] = $websiteId;
        }

        $product->setWebsiteIds($newWebsiteIds);

        $this->productRepository->save($product);

        // Update current product websites
        $currentWebsiteIds = $product->getWebsiteIds();
        if ($currentWebsiteIds) {
            foreach($currentWebsiteIds as $websiteId) {
                if (!in_array($websiteId, $newWebsiteIds)) {
                    $website = $this->storeManager->getWebsite($websiteId);
                    if ($website) {
                        $stores = $website->getStores();
                        if ($stores) {
                            foreach($stores as $store) {
                                try {
                                    $this->productAction->updateAttributes([$productId], [
                                        'publish_status'=> 2
                                    ], $store->getId());
                                } catch (\Exception $e) {
                                    $result['messages'] = __('There is error when published product. Please try again or contact Administrator.');
                                    return $result;
                                }
                            }
                        }
                    }
                }
            }
        }

        if ($publishDataArr) {
            foreach ($publishDataArr as $publishData) 
            {
                $websiteId = $publishData['website_id'];
                $price     = $publishData['price'];

                // Set product as published on website
                $website = $this->storeManager->getWebsite($websiteId);
                if ($website) {
                    $stores = $website->getStores();
                    if ($stores) {
                        foreach($stores as $store) {
                            try {
                                $this->productAction->updateAttributes([$productId], [
                                    'publish_status'=> 1,
                                    'price' => $price
                                ], $store->getId());
                            } catch (\Exception $e) {
                                $result['messages'] = __('There is error when published product. Please try again or contact Administrator.');
                                return $result;
                            }
                        }
                    }
                }
            }
        }

        $result['success'] = true;
        $result['messages'] = __('Product published successfully.');

        $cacheKey = self::CACHE_TAG . '_publish_data_' . $productId;
        $this->removeCacheByKey($cacheKey);

        return $result;
    }
    
    public function getProductListingConfig() 
    {
        $categoryId = $this->getCurrentCategoryId();
        if (!$categoryId) {
            return null;
        }

        // 1. Get Category Config
        $config = $this->productListingCategoryConfigFactory
            ->create()
            ->getCollection()
            ->addFieldToFilter('category_id', $categoryId)
            ->getFirstItem();

        if (!$config->getId()) {
            return null;
        }

        $configId = $config->getId();

        // 2. Get Default Columns
        $columnCollection = $this->productListingCategoryColumnFactory
            ->create()
            ->getCollection()
            ->addFieldToFilter('category_config_id', $configId)
            ->setOrder('sort_order', 'ASC');

        $defaultColumns = [];
        foreach ($columnCollection as $column) {
            $defaultColumns[] = [
                'attribute_code' => $column->getAttributeCode(),
                'is_sortable'    => $column->getIsSortable(),
                'is_filterable'  => $column->getIsFilterable(),
                'is_visible'     => $column->getIsVisible(),
                'sort_order'     => $column->getSortOrder(),
            ];
        }

        // 3. Get Tabs
        $tabsCollection = $this->productListingCategoryTabFactory
            ->create()
            ->getCollection()
            ->addFieldToFilter('category_config_id', $configId)
            ->addFieldToFilter('is_active', 1)
            ->setOrder('sort_order', 'ASC');

        $tabs = [];

        foreach ($tabsCollection as $tab) {

            // 4. Get Tab Columns
            $tabColumnCollection = $this->productListingCategoryTabColumnFactory
                ->create()
                ->getCollection()
                ->addFieldToFilter('tab_id', $tab->getId())
                ->setOrder('sort_order', 'ASC');

            $tabColumns = [];

            foreach ($tabColumnCollection as $tabColumn) {
                $tabColumns[] = [
                    'attribute_code' => $tabColumn->getAttributeCode(),
                    'is_sortable'    => $tabColumn->getIsSortable(),
                    'is_filterable'  => $tabColumn->getIsFilterable(),
                    'is_visible'     => $tabColumn->getIsVisible(),
                    'sort_order'     => $tabColumn->getSortOrder(),
                ];
            }

            $tabs[] = [
                'tab_id'     => $tab->getId(),
                'tab_value'  => $tab->getTabValue(),
                'label'      => $tab->getLabel(),
                'sort_order' => $tab->getSortOrder(),
                'columns'    => $tabColumns
            ];
        }

        return [
            'config'          => $config,
            'default_columns' => $defaultColumns,
            'tabs'            => $tabs
        ];
    }

    public function getProductStatusOptionsArray() 
    {
        return [
            [
                'label' => __('Published'),
                'value' => 1,
            ],
            [
                'label' => __('Draft'),
                'value' => 2,
            ]
        ];
    }

    public function getProductListingDefaultColumns() 
    {
        return [
            [
                'label' => 'Name',
                'attr_code' => 'name',
                'is_wrap_col' => true,
                'filter' => 'text'
            ],
            [
                'label' => 'SKU',
                'attr_code' => 'sku',
                'is_wrap_col' => true,
                'filter' => 'text'
            ],
            [
                'label' => 'Price',
                'attr_code' => 'price',
                'is_wrap_col' => false,
                'filter' => 'text'
            ],
            [
                'label' => 'Quantity',
                'attr_code' => 'qty',
                'is_wrap_col' => false,
                'filter' => 'text'
            ],
            [
                'label' => 'Status',
                'attr_code' => 'is_published',
                'is_wrap_col' => false,
                'filter' => 'select',
                'options' => $this->getProductStatusOptionsArray()
            ],
            [
                'label' => 'Countries/Regions',
                'attr_code' => 'website_id',
                'is_wrap_col' => false,
                'filter' => 'select',
                'options' => $this->marketplaceSellerHelper->getSellerStoresOptionsArray()
            ],
        ];
    }

    public function getPlatformDatabaseConfig() 
    {
        if (!$this->platformDatabaseConfig) {
            $categoryId = $this->request->getParam('category');
            if ($categoryId) {
                $this->platformDatabaseConfig = $this->mainCategoriesConfigProvider->getByCategoryId($categoryId);
            }
        }

        return $this->platformDatabaseConfig;
    }

    public function getCurrentCategoryId() 
    {
        $platformDatabaseConfig = $this->getPlatformDatabaseConfig();
        if ($platformDatabaseConfig) {
            return $platformDatabaseConfig['categoryId'] ?? null;
        }

        return null;
    }

    public function getProductAttribute($attrCode) 
    {
        return $this->mappingHelper->getAttribute($attrCode);
    }

    // Seller Products Listing Data
    public function getSellerProductsListingData() 
    {
        $request = $this->request;

        $draw   = (int)$request->getParam('draw');
        $start  = (int)$request->getParam('start', 0);
        $length = (int)$request->getParam('length', 20);

        $order  = $request->getParam('order');
        $search = $request->getParam('search');

        $collection = $this->productCollectionFactory->create();

        $productListingConfig = $this->getProductListingConfig();

        $productListingCategoryAttributes = $productListingConfig && $productListingConfig['default_columns'] ? 
            $productListingConfig['default_columns'] : [];

        $attributeToSelect = ['thumbnail','name', 'price'];

        if ($productListingCategoryAttributes) {
            foreach($productListingCategoryAttributes as $column) {
                $attrCode = $column['attribute_code'] ?? null;
                // $this->selectAttrWithPrefix($collection, $attrCode);
                if ($attrCode) {
                $attributeToSelect[] = $attrCode;
                }
            }
        }

        

        // dd($attributeToSelect);

        // $collection->addAttributeToSelect(['card_game']);
        $collection->addAttributeToSelect($attributeToSelect);

        // $attr = $collection->getFirstItem()->getResource()->getAttribute('card_game');
// dd(var_dump($attr));

        // dd($collection->getSelect()->__toString());

        $status   = $request->getParam('status');
        $categoryId = $request->getParam('category');

        /* =========================
        FILTER BY SELLER
        ========================== */
        $sellerId = $this->helper->getSellerId();
        $collection->addAttributeToFilter('seller_id', ['eq' => $sellerId]);

        /* =========================
        FILTER BY CATEGORY
        ========================== */
        $collection = $this->filterProductsCollectionByCategory($collection, $categoryId);

        /* =========================
        GET PUBLISH STATUS VALUE
        ========================== */
        // $customTable = $collection->getTable('catalog_product_entity_int');
        // $collection->getSelect()->joinLeft(
        //     ['cpei' => $customTable],
        //     'e.entity_id = cpei.entity_id AND cpei.value = 1 AND cpei.attribute_id = 1304', // Ensure attribute_id matches
        //     [
        //         'value AS custom_value',
        //         'is_published' => new \Zend_Db_Expr("IF(cpei.value = 1, 1, IF(cpei.value IS NULL OR cpei.value = 2, 2, 1))")
        //     ]
        // );

        $attributeId = 1304;
        $customTable = $collection->getTable('catalog_product_entity_int');

        $collection->getSelect()->columns([
            'is_published' => new \Zend_Db_Expr("
    IF(
        EXISTS (
            SELECT 1 
            FROM {$customTable} cpei
            WHERE cpei.entity_id = e.entity_id
            AND cpei.attribute_id = {$attributeId}
            AND cpei.value = 1
            LIMIT 1
        ),
        1,
        2
    )
")
        ]);

//         echo $collection->getSelect();
// die();

        /* =========================
        FILTERS BY PRODUCT TYPE (SIMPLE/CONFIGURABLE)
        ========================== */
        $productType = 'simple';
        $currentUrl = $this->redirect->getRefererUrl();
        if (str_contains($currentUrl, '/variations')) {
            $productType = 'configurable';
        }
        $collection->addFieldToFilter('type_id', ['eq' => $productType]);

        /* =========================
        FILTERS BY ACTIVE TAB
        ========================== */
        $tab = $this->request->getParam('tab');
        if ($tab) {
            $activeTab = $tab['active_tab'] ?? null;
            if ($activeTab) {
                $collection->addAttributeToFilter($tab['attribute_code'], ['eq' => $activeTab]);
            }
        }

        /* =========================
        FILTERS COLUMNS
        ========================== */
        $filters = $this->request->getParam('filters');
        if ($filters) {
            foreach ($filters as $field => $data) {
                if ($field == "website_id") {
                    $collection->addWebsiteFilter($data['value']);

                    break;
                }

                if ($field == "is_published") {
                    $attributeId = 1304;
                    $customTable = $collection->getTable('catalog_product_entity_int');

                    if ($data['value'] == 1) {
                        $collection->getSelect()->where("
                            EXISTS (
                                SELECT 1 FROM {$customTable} cpei
                                WHERE cpei.entity_id = e.entity_id
                                AND cpei.attribute_id = {$attributeId}
                                AND cpei.value = 1
                            )
                        ");
                    } else {
                        $collection->getSelect()->where("
                            NOT EXISTS (
                                SELECT 1 FROM {$customTable} cpei
                                WHERE cpei.entity_id = e.entity_id
                                AND cpei.attribute_id = {$attributeId}
                                AND cpei.value = 1
                            )
                        ");
                    }
                    break;
                }

                if ($data['type'] === 'select') {
                    $collection->addAttributeToFilter($field, $data['value']);
                } else {
                    $collection->addAttributeToFilter(
                        $field,
                        ['like' => '%' . $data['value'] . '%']
                    );
                }
            }
        }   

        /* =========================
        TOTAL BEFORE FILTER
        ========================== */
        $recordsTotal = $collection->getSize();

        /* =========================
        SEARCH
        ========================== */
        $searchValue = $request->getParam('search')['value'] ?? '';

        if ($searchValue) {
            $collection->addAttributeToFilter(
                'name',
                ['like' => '%' . $searchValue . '%']
            );
        }

        $recordsFiltered = $collection->getSize();

        /* =========================
        SORTING
        ========================== */
        if (!empty($order[0])) {
            $columnIndex = (int)$order[0]['column'];
            $dir         = $order[0]['dir'];

            $columnsMap = [
                0 => 'entity_id',
                1 => 'name',
                2 => 'price'
            ];

            if (isset($columnsMap[$columnIndex])) {
                $collection->setOrder(
                    $columnsMap[$columnIndex],
                    strtoupper($dir) === 'DESC' ? 'DESC' : 'ASC'
                );
            }
        }

        /* =========================
        PAGINATION
        ========================== */
        $collection->setPageSize($length);
        $collection->setCurPage(($start / $length) + 1);

        

        $data = [];

        foreach ($collection as $item) {

            $imageUrl = $this->imageHelper->init(
                $item,
                'product_thumbnail_image'
            )->resize(75, 75)->getUrl();

            $productData = [
                'entity_id' => $item->getId(),
                'thumbnail' => $imageUrl,
                'name'      => $item->getName(),
                'sku' => $item->getSku(),
                'price'     => $this->getPriceColumnData($item),
                'qty'     => $item->getQty(),
                'is_published'     => $item->getData('is_published') == 1 ? 'Published' : 'Draft',
                'website_id'     => $this->getProductWebsitesByIds($item->getWebsiteIds()),
                'website_ids'     => $item->getWebsiteIds()
            ];

            if ($productListingCategoryAttributes) {
                foreach($productListingCategoryAttributes as $column) {
                    $attrCode = $column['attribute_code'] ?? null;
                    $productData[$attrCode] = $item->getData($attrCode) ? $item->getAttributeText($attrCode) : '';
                }
            }

            $data[] = $productData;
        }

        return $this->jsonFactory->create()->setData([
            'draw'            => $draw,
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $data
        ]);
    }

    public function getPriceColumnData($item) 
    {
        $isPublished = $item->getData('is_published');
        if ($isPublished != 1) {
            return '-';
        }

        $websiteIds = $item->getWebsiteIds();
        if (!$websiteIds) {
            return '-';
        }
    
        $typeId = $item->getTypeId();
        $countListingCountry = count($websiteIds);

        $priceCol = '';

        $websiteCollection = $this->websiteCollectionCache->getWebsiteCollection();
        if ($websiteCollection) {
            $websites = $websiteCollection['items'] ?? [];
            if ($websites) {
                foreach ($websites as $index => $website) {
                    $websiteName = $website['name'] ?? '';
                    $websiteId = $website['website_id'] ?? '';
                    if (in_array($websiteId, $websiteIds)) {
                        if ($countListingCountry > 1) {
                            $priceCol .= '<div class="listing-product-countries">'.$websiteName.':</div>';
                        }
                        
                        $storeId = $this->helper->getWebsiteDefaultStoreId($website);

                        $price = $this->formatPriceWithLocalCurrency(
                            $this->getProductPriceByWebsiteId($item['entity_id'], $storeId),
                            $storeId
                        );

                        $priceCol .= '<span class="listing-product-price">'.$price.'</span>';

                        if ($countListingCountry > 1 && $index+1 != count($websites)) {
                            $priceCol .= '<br/>';
                        }
                    }
                }
            }
        }

        return $priceCol;
    }

    public function formatPriceWithLocalCurrency($price, $storeId) 
    {
        $store = $this->storeManager->getStore($storeId);

        return $this->priceCurrency->format(
            sprintf("%F", $price),
            false,
            PriceCurrencyInterface::DEFAULT_PRECISION,
            $store
        );
    }

    public function getProductPriceByWebsiteId($productId, $storeId) 
    {
        $result = 0;

        $product = $this->productRepository->getById($productId, false, $storeId);
        if ($product->getTypeId() == "configurable") {
            $usedProducts = $this->configurableType->getUsedProducts($product);
            $prices = [];

            foreach ($usedProducts as $child) {
                $prices[] = $child->getFinalPrice();
            }
            
            if ($prices) {
                $minPriceFormatted = min($prices);
                $maxPriceFormatted = max($prices);

                $result = $minPriceFormatted . ' - ' . $maxPriceFormatted;
            }
        } else {
            $result = $product->getFinalPrice();
        }

        return $result;
    }

    public function getProductWebsitesByIds($websiteIds) 
    {
        $websiteNameArr = [];

        $websiteCollection = $this->websiteCollectionCache->getWebsiteCollection();
        if ($websiteCollection) {
            $websites = $websiteCollection['items'] ?? [];
            if ($websites) {
                foreach ($websites as $website) {
                    if (in_array($website['website_id'], $websiteIds)) {
                        $websiteNameArr[] = $website['name'];
                    }
                }
            }
        }

        return $websiteNameArr ? implode('<br />', $websiteNameArr) : '-';
    }

    public function filterProductsCollectionByCategory($collection, $categoryId) 
    {
        // $filters = $this->request->getParam('filters') ?? [];
        // if (!isset($filters['category_id'])) {
            $categoryIds = [];
            
            if ($categoryId) {
                $categoryIds[] = (int)$categoryId;
            }

            $collection->addCategoriesFilter(['in' => $categoryIds]);
        // }

        return $collection;
    }


    // END OF NEW FUNCT
}