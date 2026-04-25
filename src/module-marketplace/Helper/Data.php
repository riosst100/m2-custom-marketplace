<?php

namespace TCGCollective\MarketPlace\Helper;

use Magento\Directory\Model\RegionFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Validator\EmailAddress;
use Lof\MarketPlace\Model\Seller;
use Lof\MarketPlace\Model\Source\CalculateType;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Serialize\SerializerInterface;

class Data extends \Lof\MarketPlace\Helper\Data
{
    /**
     * @var \Lof\MarketPlace\Model\Group
     */
    protected $_groupCollection;

    /**
     * @var \Lof\MarketPlace\Model\Commission
     */
    protected $_commissionCollection;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var array
     */
    protected $_config = [];

    /**
     * @var array
     */
    protected $_seller_by_customers = [];

    /**
     * @var array
     */
    protected $_sellers = [];

    /**
     * @var
     */
    protected $_templateFilterFactory;

    /**
     * @var \Magento\Cms\Model\Template\FilterProvider
     */
    protected $_filterProvider;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productCollectionFactory;

    /**
     * @var \Lof\MarketPlace\App\Area\FrontNameResolver
     */
    protected $_frontNameResolver;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var PriceCurrencyInterface
     */
    protected $_priceCurrency;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceFormatter;

    /**
     * @var
     */
    protected $localeDate;

    /**
     * @var DataRule
     */
    protected $dataRule;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customer;

    /**
     * @var array
     */
    protected $_blocksUseTemplateFromAdminhtml;

    /**
     * @var array
     */
    protected $_modulesUseTemplateFromAdminhtml;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $_moduleManager;

    /**
     * @var \Magento\Directory\Model\CountryFactory
     */
    protected $_countryFactory;

    /**
     * @var \Magento\Framework\App\Request\DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var \Magento\Framework\Url
     */
    protected $_frontendUrl;

    /**
     * @var \Lof\MarketPlace\Model\Zip
     */
    protected $_zip;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $_filesystem;

    /**
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     */
    protected $_fileUploader;

    /**
     * @var \Magento\Framework\Filesystem\Driver\File
     */
    protected $_fileDriver;

    /**
     * @var
     */
    protected $_fileSystem;

    /**
     * @var DirectoryList
     */
    protected $_directoryList;

    /**
     * @var Uploadimage
     */
    protected $uploadimage;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    private $_localeDate;

    /**
     * @var \Magento\Framework\Filesystem\Io\File
     */
    protected $filesystemIo;

    /**
     * @var RegionFactory
     */
    protected $regionFactory;

    /**
     * @var \Magento\Store\Api\WebsiteRepositoryInterface
     */
    protected $websiteRepository;

    /**
     * @var array
     */
    private $postData = null;

    protected $_scopeConfig;
    protected $serializer;

    protected $storeCollectionCache;
    protected $storeGroupCollectionCache;
    protected $cache;
    protected $attributeRepository;
    protected $websiteCollectionCache;
    protected $sellerDataCache;
    protected $request;
    protected $resource;
    protected $productAction;
    protected $customerGroupCollectionFactory;

    /**
     * Data constructor.
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Lof\MarketPlace\Model\Group $groupCollection
     * @param \Lof\MarketPlace\Model\Commission $commissionCollection
     * @param DataRule $dataRule
     * @param \Lof\MarketPlace\Model\Zip $zip
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Cms\Model\Template\FilterProvider $filterProvider
     * @param \Lof\MarketPlace\App\Area\FrontNameResolver $frontNameResolver
     * @param \Magento\Catalog\Model\ProductFactory $productCollectionFactory
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param PriceCurrencyInterface $priceCurrency
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory
     * @param \Magento\Framework\Filesystem\Driver\File $fileDriver
     * @param PriceCurrencyInterface $priceFormatter
     * @param \Magento\Framework\Url $frontendUrl
     * @param \Magento\Customer\Model\CustomerFactory $customer
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Filesystem\Io\File $filesystemIo
     * @param DirectoryList $directoryList
     * @param \Magento\Directory\Model\CountryFactory $countryFactory
     * @param \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor
     * @param Uploadimage $uploadimage
     * @param \Magento\Framework\App\Helper\Context $context
     * @param RegionFactory $regionFactory
     * @param \Magento\Store\Api\WebsiteRepositoryInterface $websiteRepository
     * @param array $modulesUseTemplateFromAdminhtml
     * @param array $blocksUseTemplateFromAdminhtml
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Lof\MarketPlace\Model\Group $groupCollection,
        \Lof\MarketPlace\Model\Commission $commissionCollection,
        \Lof\MarketPlace\Helper\DataRule $dataRule,
        \Lof\MarketPlace\Model\Zip $zip,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Cms\Model\Template\FilterProvider $filterProvider,
        \Lof\MarketPlace\App\Area\FrontNameResolver $frontNameResolver,
        \Magento\Catalog\Model\ProductFactory $productCollectionFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
        \Magento\Framework\Filesystem\Driver\File $fileDriver,
        PriceCurrencyInterface $priceFormatter,
        \Magento\Framework\Url $frontendUrl,
        \Magento\Customer\Model\CustomerFactory $customer,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Filesystem\Io\File $filesystemIo,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor,
        \Lof\MarketPlace\Helper\Uploadimage $uploadimage,
        RegionFactory $regionFactory,
        \Magento\Store\Api\WebsiteRepositoryInterface $websiteRepository,
        SerializerInterface $serializer,
        \TCGCollective\MarketPlace\Model\StoreCollectionCache $storeCollectionCache,
        \TCGCollective\MarketPlace\Model\StoreGroupCollectionCache $storeGroupCollectionCache,
        \Magento\Framework\App\CacheInterface $cache,
        \Magento\Catalog\Api\ProductAttributeRepositoryInterface $attributeRepository,
        \TCGCollective\MarketPlace\Model\WebsiteCollectionCache $websiteCollectionCache,
        \TCGCollective\MarketPlace\Model\SellerDataCache $sellerDataCache,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Customer\Model\ResourceModel\Group\CollectionFactory $customerGroupCollectionFactory,
        \Magento\Catalog\Model\Product\Action $productAction,
        array $modulesUseTemplateFromAdminhtml = [],
        array $blocksUseTemplateFromAdminhtml = []
    ) {
        parent::__construct(
            $context,
            $storeManager,
            $groupCollection,
            $commissionCollection,
            $dataRule,
            $zip,
            $filesystem,
            $filterProvider,
            $frontNameResolver,
            $productCollectionFactory,
            $objectManager,
            $priceCurrency,
            $localeDate,
            $fileUploaderFactory,
            $fileDriver,
            $priceFormatter,
            $frontendUrl,
            $customer,
            $customerSession,
            $filesystemIo,
            $directoryList,
            $countryFactory,
            $dataPersistor,
            $uploadimage,
            $modulesUseTemplateFromAdminhtml,
            $blocksUseTemplateFromAdminhtml
        );
        $this->_directoryList = $directoryList;
        $this->uploadimage = $uploadimage;
        $this->_frontendUrl = $frontendUrl;
        $this->_countryFactory = $countryFactory;
        $this->dataPersistor = $dataPersistor;
        $this->_moduleManager = $context->getModuleManager();
        $this->_fileDriver = $fileDriver;
        $this->dataRule = $dataRule;
        $this->customer = $customer;
        $this->_localeDate = $localeDate;
        $this->_fileUploader = $fileUploaderFactory;
        $this->_filterProvider = $filterProvider;
        $this->_storeManager = $storeManager;
        $this->_groupCollection = $groupCollection;
        $this->_commissionCollection = $commissionCollection;
        $this->customerSession = $customerSession;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->_frontNameResolver = $frontNameResolver;
        $this->_objectManager = $objectManager;
        $this->_priceCurrency = $priceCurrency;
        $this->priceFormatter = $priceFormatter;
        $this->filesystemIo = $filesystemIo;
        $this->_blocksUseTemplateFromAdminhtml = $blocksUseTemplateFromAdminhtml;
        $this->_modulesUseTemplateFromAdminhtml = $modulesUseTemplateFromAdminhtml;
        $this->_zip = $zip;
        $this->_filesystem = $filesystem;
        $this->regionFactory = $regionFactory;
        $this->websiteRepository = $websiteRepository;
        $this->_scopeConfig = $context->getScopeConfig();     
        $this->serializer = $serializer;
        $this->storeCollectionCache = $storeCollectionCache;
        $this->storeGroupCollectionCache = $storeGroupCollectionCache;
        $this->cache = $cache;
        $this->attributeRepository = $attributeRepository;
        $this->websiteCollectionCache = $websiteCollectionCache;
        $this->sellerDataCache = $sellerDataCache;
        $this->request = $request;
        $this->resource = $resource;
        $this->customerGroupCollectionFactory = $customerGroupCollectionFactory;
        $this->productAction = $productAction;
    }

    public function updateSellerProductsShipFrom($countryId) 
    {
        $sellerId = $this->getSellerId();
        if ($sellerId) {
            $collection = $this->productCollectionFactory->create()
            ->getCollection();

            $collection->getSelect()->joinLeft(
                ['lmp' => 'lof_marketplace_product'],
                'lmp.product_id=e.entity_id',
                []
            )->where('lmp.seller_id = ' . $sellerId);

            foreach ($collection as $product) {
                try {
                    $this->productAction->updateAttributes([$product->getId()], [
                        'ship_from' => $countryId
                    ], 0);
                } catch (\Exception $e) {
                    dd($e);
                }
            }
        }
    }

    public function getCustomerGroupByName($groupName) 
    {
        $collection = $this->customerGroupCollectionFactory->create();
        $collection->addFieldToFilter('customer_group_code', $groupName);
        if ($collection->getSize()) {
            $group = $collection->getFirstItem();
            
            return $group;
        }

        return null;
    }

    /**
     * Format datetime sesuai konfigurasi store
     */
    public function formatDateTime($datetime = null, $format = \IntlDateFormatter::MEDIUM, $showTime = false)
    {
        // Jika $datetime tidak diisi, pakai waktu sekarang
        if ($datetime === null) {
            $datetime = $this->_localeDate->date();
        } else {
            $datetime = $this->_localeDate->date(new \DateTime($datetime));
        }

        return $this->_localeDate->formatDateTime(
            $datetime,
            $format, // format date
            $showTime ? $format : \IntlDateFormatter::NONE // format time
        );
    }

    /**
     * Mendapatkan timezone aktif
     */
    public function getStoreTimezone()
    {
        return $this->_localeDate->getConfigTimezone();
    }

    public function getParam($code) {
        return $this->request->getParam($code);
    }

    public function getSellerListingCategories() 
    {
        $listingCategories = [];

        $seller = $this->sellerDataCache->getSellerData();
        if ($seller) {
            // dd($seller);
            /** @var \Magento\Framework\App\ResourceConnection $resource */
            $connection = $this->resource->getConnection();

            // $lofTable = $this->resource->getTableName('lof_marketplace_product');
            // $ccpTable = $this->resource->getTableName('catalog_category_product');

            // $sellerId = $seller['seller_id'];

            // $select = $connection->select()
            //     ->from(['lmp' => $lofTable], [])
            //     ->join(
            //         ['ccp' => $ccpTable],
            //         'lmp.product_id = ccp.product_id',
            //         []
            //     )
            //     ->columns([
            //         'category_id'   => 'ccp.category_id',
            //         'total_product' => new \Zend_Db_Expr('COUNT(lmp.product_id)')
            //     ])
            //     ->where('lmp.seller_id = ?', $sellerId) // << filter seller_id
            //     ->group('ccp.category_id')
            //     ->order('total_product DESC');
            $lofTable     = $this->resource->getTableName('lof_marketplace_product');
            $ccpTable     = $this->resource->getTableName('catalog_category_product');
            $ccEntity     = $this->resource->getTableName('catalog_category_entity');
            $ccVarchar    = $this->resource->getTableName('catalog_category_entity_varchar');
            $eavAttribute = $this->resource->getTableName('eav_attribute');

            $sellerId = $seller['seller_id']; // filter seller

            // Ambil attribute_id untuk 'name'
            $attributeId = $connection->fetchOne(
                $connection->select()
                    ->from($eavAttribute, ['attribute_id'])
                    ->where('entity_type_id = ?', 3)   // 3 = category entity
                    ->where('attribute_code = ?', 'name')
            );

            // Ambil kategori parent (langsung pakai parent_id)
            $select = $connection->select()
            ->from(['lmp' => $lofTable], [])
            ->join(['ccp' => $ccpTable], 'lmp.product_id = ccp.product_id', [])
            ->join(['cce' => $ccEntity], 'ccp.category_id = cce.entity_id', [])
            ->join(['parent' => $ccEntity], 'parent.entity_id = cce.parent_id', [])
            ->join(
                ['cev' => $ccVarchar],
                'parent.entity_id = cev.entity_id AND cev.attribute_id = ' . (int)$attributeId,
                []
            )
            ->columns([
                'category_id'   => 'parent.entity_id',
                'category_name' => 'cev.value'
            ])
            ->where('lmp.seller_id = ?', $sellerId)
            ->where('parent.level = ?', 2) // pastikan ambil parent level 2
            ->order('cev.value')
            ->group('parent.entity_id');

$listingCategories = $connection->fetchPairs($select);
        }
        // dd($listingCategories);
        return $listingCategories;
    }

    public function getSellerListingCountry() 
    {
        $listingCountryArr = [];

        $websiteCollection = $this->websiteCollectionCache->getWebsiteCollection();
        if ($websiteCollection) {
            $websites = $websiteCollection['items'] ?? [];
            if ($websites) {
                $defaultWebsiteId = $this->_storeManager->getStore()->getWebsite()->getId();

                $seller = $this->sellerDataCache->getSellerData();
                $listingWebsiteIds = isset($seller['listing_website_ids']) && $seller['listing_website_ids'] ? 
                explode(",", $seller['listing_website_ids']) : [];

                foreach ($websites as $website) {
                    $websiteId = $website['website_id'] ?? null;
                    $websiteName = $website['name'] ?? null;
                    if (!$websiteId) {
                        continue;
                    }

                    // $storeId = $this->helper->getWebsiteDefaultStoreId($website);

                    // $website['publish_link'] = $this->urlBuilder->getUrl('catalog/product/edit', [
                    //     'id' => $productId,
                    //     'store' => $storeId,
                    //     'country' => $websiteName,
                    //     'website_id' => $websiteId,
                    //     'action' => 'publish'
                    // ]);

                    // $currentUrl = $this->urlBuilder->getUrl('*/*/*', ['_current' => true]);

                    if ($websiteId == $defaultWebsiteId) {
                        $website['is_default'] = true;
                        $website['name'] = $websiteName;
                        $website['is_enabled'] = true;
                    } else {
                        $website['is_default'] = false;
                        if (in_array($websiteId, $listingWebsiteIds)) {
                            $website['is_enabled'] = true;
                        } else {
                            $website['is_enabled'] = false;
                        }
                    }

                    $listingCountryArr[] = $website;
                }
            }
        }

        if ($listingCountryArr) {
            usort($listingCountryArr, fn($a, $b) => $b['is_default'] <=> $a['is_default']);
            usort($listingCountryArr, fn($a, $b) => $b['is_enabled'] <=> $a['is_enabled']);
        }

        return $listingCountryArr;
    }

    public function getSellerCountries() {
        $websites = [];

        $websiteCollection = $this->websiteCollectionCache->getWebsiteCollection();
        if ($websiteCollection) {
            $websitesData = $websiteCollection['items'] ?? [];
            if ($websitesData) {
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $this->sellerHelper = $objectManager->create(\Lof\MarketPlace\Helper\Seller::class);

                $seller = $this->sellerHelper->getSeller();

                $sellerListingWebsiteIds = $this->getSellerListingWebsiteIds($seller);
                
                foreach ($websitesData as $index => $website) {
                    // dd($website);
                    $websiteId = $website['website_id'] ?? '';
                    $websiteName = $website['name'] ?? '';
                    $websiteCode = $website['code'] ?? '';

                    if (
                        in_array($websiteId, $sellerListingWebsiteIds)
                        || $websiteCode == $seller->getDefaultWebsiteCode()
                    ) {
                        $currencyCode = $this->_scopeConfig->getValue(
                            'currency/options/default',
                            \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITES,
                            $websiteCode
                        );

                        // Append currency
                        $website['currency'] = $currencyCode;

                        $websites[$websiteId] = $website;
                    }

                    if (in_array($websiteId, $sellerListingWebsiteIds) || $websiteCode == $seller->getDefaultWebsiteCode()) {
                        $websites[$websiteId] = $website;
                    }
                }
            }
        }
        
        return $websites;
    }

    public function getSellerListingWebsiteIds($seller) 
    {
        $listingWebsiteIdsArr = [];

        $currentListingCountry = $seller->getListingWebsiteIds();
        if ($currentListingCountry) {
            $listingWebsiteIdsArr = explode(",", $currentListingCountry);
        }

        return $listingWebsiteIdsArr;
    }
        
    /**
     * @param $section
     * @param $groups
     * @param $seller_id
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function savePaymentData($section, $groups, $seller_id)
    {
        foreach ($groups as $code => $values) {
            if (is_array($values) && count($values) > 0) {
                foreach ($values as $name => $value) {
                    $serialized = 0;
                    $key = strtolower($section . '/' . $code . '/' . $name);
                    if (is_array($value)) {
                        $value = implode(',', $value);
                    }
                    $key_tmp = $this->getTableKey('key');
                    $seller_id_tmp = $this->getTableKey('seller_id');
                    $setting = $this->_objectManager->get(\Lof\MarketPlace\Model\ConfigFactory::class)->create()
                        ->loadByField([$key_tmp, $seller_id_tmp], [$key, $seller_id]);
                    if ($setting && $setting->getId()) {
                        $setting->setSellerId($seller_id)
                            ->setGroup($section)
                            ->setKey($key)
                            ->setValue($value)
                            ->setSerialized($serialized)
                            ->save();
                    } else {
                        $setting = $this->_objectManager->get(\Lof\MarketPlace\Model\ConfigFactory::class)->create();
                        $setting->setSellerId($seller_id)
                            ->setGroup($section)
                            ->setKey($key)
                            ->setValue($value)
                            ->setSerialized($serialized)
                            ->save();
                    }
                }
            }
        }
    }
    
    public function getAttributeOptions($attributeCode) 
    {
        if ($attributeCode) {
            $cacheTag = 'tcgcollective_attribute_options_cache';
            $cacheKey = $cacheTag . $attributeCode;

            // Load existing
            $cachedData = $this->cache->load($cacheKey);
            if ($cachedData) {
                return unserialize($cachedData);
            }

            // Fetch collection and cache it permanently
            // $collection = $this->attributeRepository->get($attributeCode, 'catalog_product')->getOptions();
            $collection = array_map(function($option) {
                return ['value' => $option->getValue(), 'label' => $option->getLabel()];
            }, $this->attributeRepository->get($attributeCode, 'catalog_product')->getOptions());
            // dd($collection);
            if ($collection) {
                $data = $collection;
                $this->cache->save(
                    serialize($data),
                    $cacheKey,
                    [$cacheTag]
                );
            }
        }

        return $data;
    }

    public function getAttributeOptionLabel($attributeCode, $optionId) 
    {
        if ($attributeCode) {
            $options = $this->getAttributeOptions($attributeCode);
            if ($options) {
                $option = $this->getFilteredData($options, 'value', $optionId);
                if ($option) {
                    return $option['label'] ?? '';
                }
            }
        }

        return null;
    }

    public function getFilteredData($dataArr, $field, $value) 
    {
        if ($dataArr) {
            $filteredItem = array_filter($dataArr, function($item) use ($value,$field) {
                return $item[$field] === $value;
            });

            $result = array_values($filteredItem)[0] ?? null;
            return $result;
        }

        return null;
    }

    public function removeCache($cacheKey) {
        $this->cache->remove($cacheKey);
    }

    public function getWebsiteDefaultStore($website) 
    {
        $defaultStoreId = $this->getWebsiteDefaultStoreId($website);
        if ($defaultStoreId) {
            $store = $this->storeCollectionCache->getRow('store_id', $defaultStoreId);
            return $store;
        }
        
        return null;
    }

    public function getWebsiteDefaultStoreId($website) 
    {
        $defaultGroupId = $website['default_group_id'] ?? '';
        if ($defaultGroupId) {
            $storeGroup = $this->storeGroupCollectionCache->getRow('group_id', $defaultGroupId);
            if ($storeGroup) {
                $defaultStoreId = $storeGroup['default_store_id'] ?? null;
                return $defaultStoreId;
            }
        }
        
        return null;
    }

    public function checkPublishStatusByWebsiteData($website, $productId) 
    {
        $cacheTag = 'product_publish_status_cache';
        $cacheKey = 'product_publish_status_cache' . $productId . '_' . $website['website_id'];

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->productRepository = $objectManager->create(\Magento\Catalog\Model\ProductRepository::class);
        $this->storeGroupCollectionCache = $objectManager->create(\TCGCollective\MarketPlace\Model\StoreGroupCollectionCache::class);
        $this->cache = $objectManager->create(\Magento\Framework\App\CacheInterface::class);

        $cachedData = $this->cache->load($cacheKey);
        if ($cachedData) {
            // return $cachedData;
        }

        $status = 0;
        
        $defaultGroupId = $website['default_group_id'] ?? '';
        if ($defaultGroupId) {
            $storeGroup = $this->storeGroupCollectionCache->getRow('group_id', $defaultGroupId);
            if ($storeGroup) {
                $defaultStoreId = $storeGroup['default_store_id'] ?? null;
                if ($defaultStoreId) {
                    $status = $this->getProductPublishStatus($productId, $defaultStoreId);
                }
            }
        }
        
        $this->cache->save(
            $status,
            $cacheKey,
            [$cacheTag]
        );
        
        return $status == 1 ? true : false;
    }

    public function getProductPublishStatus($productId, $storeId) 
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->resource = $objectManager->create(\Magento\Framework\App\ResourceConnection::class);

        // Get the database connection
        $connection = $this->resource->getConnection();

        // Table names
        $eavTable = $this->resource->getTableName('catalog_product_entity_int'); // Table for integer attributes
        $attributeTable = $this->resource->getTableName('eav_attribute'); // EAV attribute table

        // Fetch the attribute ID for 'publish_status'
        $attributeId = $connection->fetchOne(
            $connection->select()
                ->from($attributeTable, ['attribute_id'])
                ->where('attribute_code = ?', 'publish_status')
                ->where('entity_type_id = ?', 4) // 4 is the entity_type_id for products
        );

        if (!$attributeId) {
            throw new \Exception('Attribute publish_status does not exist');
        }

        // Query to get the attribute value for the specific product and store
        $select = $connection->select()
            ->from($eavTable, ['value'])
            ->where('attribute_id = ?', $attributeId)
            ->where('entity_id = ?', $productId)
            ->where('store_id IN (?)', [$storeId]) // Check global (store_id = 0) and specific store value
            ->order('store_id DESC') // Prefer store-specific value over global
            ->limit(1);

            // dd($select);

        // Fetch the value
        $publishStatus = $connection->fetchOne($select);

        return (int)$publishStatus;
    }

    public function getCurrentSeller() 
    {
        $customerId = $this->getCustomerId();
        if ($customerId) {
            return $this->getSellerByCustomerId($customerId);
        }

        return null;
    }

    public function getCountryName($countryCode = null) 
    {
        if ($countryCode) {
            $country = $this->_countryFactory->create()
            ->loadByCode($countryCode);
            if ($country) {
                return $country->getName();
            }
        }

        return '';
    }

    public function getCurrentUrl() 
    {
        return $this->_frontendUrl->getCurrentUrl();
    }

    public function autoSelectWebsite($targetWebsiteCode = null, $targetUrl = null) 
    {
        $baseUrl = $this->getCurrentBaseUrl();
        // dd($targetWebsiteCode.' dan '.$baseUrl);
        
        if ($baseUrl && $targetWebsiteCode) {
            $currentWebsiteCode = trim(str_replace("/", "", parse_url($baseUrl, PHP_URL_PATH)));
            if ($currentWebsiteCode) {
                $currentWebsiteCode = trim(str_replace("/", "", $currentWebsiteCode));
                if ($currentWebsiteCode != $targetWebsiteCode) {

                    if ($targetUrl) {
                        $urlPath = $targetUrl;
                    } else {
                        $currentUrl = $this->_frontendUrl->getCurrentUrl();
                        $urlPath = parse_url($currentUrl, PHP_URL_PATH);

                        $urlPathArr = explode("/", $urlPath);

                        unset($urlPathArr[0]);
                        unset($urlPathArr[1]);

                        $urlPath = implode("/", $urlPathArr);
                    }

                    $websites = $this->_storeManager->getWebsites(false, true);
                    if ($websites) {
                        $targetWebsite = null;
                        foreach($websites as $code => $website) {
                            if ($code == $targetWebsiteCode) {
                                $targetWebsite = $website;
                            }
                        }
                        
                        if ($targetWebsite) {
                            return $targetWebsite->getDefaultStore()->getBaseUrl().$urlPath;
                        }
                    }
                }
            } else {
                if ($targetWebsiteCode) {

                    if ($targetUrl) {
                        $urlPath = $targetUrl;
                    } else {
                        $currentUrl = $this->_frontendUrl->getCurrentUrl();
                        $urlPath = parse_url($currentUrl, PHP_URL_PATH);

                        $urlPathArr = explode("/", $urlPath);

                        unset($urlPathArr[0]);

                        $urlPath = implode("/", $urlPathArr);
                    }

                    $websites = $this->_storeManager->getWebsites(false, true);
                    if ($websites) {
                        $targetWebsite = null;
                        foreach($websites as $code => $website) {
                            if ($code == $targetWebsiteCode) {
                                $targetWebsite = $website;
                            }
                        }
                        
                        if ($targetWebsite) {
                            return $targetWebsite->getDefaultStore()->getBaseUrl().$urlPath;
                        }
                    }
                }
            }
        }

        return null;
    }

    public function getCurrentBaseUrl() 
    {
        return $this->_storeManager->getStore()->getBaseUrl();
    }

    public function getPwaBaseUrl() 
    {
        return $this->getConfig('pwa/base_url') ?? '/';
    }

    public function getRegionData($regionCode)
    {
        $regionColl = $this->regionFactory->create()
        ->getCollection()
        ->addFieldToFilter('code', $regionCode);
        if ($regionColl->getSize()) {
            return $regionColl->getFirstItem();
        }

        return null;
    }

    public function setStoreBySellerCountry($sellerCountryID = null) 
    {
        $selectedWebsiteCode = $sellerCountryID ?? 'base';

        $storeId = null;
        $websiteId = $this->getWebsiteIdByCode($selectedWebsiteCode);
        if ($websiteId) {
            $storeId = $this->_storeManager->getWebsite($websiteId)->getDefaultStore()->getId();
        }
        
        if ($storeId) {
            $this->_storeManager->setCurrentStore($storeId);
        }

        return true;
    }

    public function getWebsiteIdByCode($code)
    {
        $websiteId = null;

        try {
            $website = $this->websiteRepository->get($code);
            $websiteId = (int)$website->getId();
        } catch (\Exception $exception) {}

        return $websiteId;
    }
}