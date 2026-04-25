<?php

namespace TCGCollective\PlatformDatabase\Helper;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\CacheInterface;
use Psr\Log\LoggerInterface;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const CACHE_TAG = 'seller_setsrelease_selection_cache';
    
    protected $logger;
    protected $urlBuilder;
    protected $request;
    protected $redirect;
    protected $state;
    protected $directory;
    protected $helper;

    // Variable
    protected $attributeSetName = null;
    protected $setData = null;
    protected $prefix = null;
    protected $debug = true;
    protected $setId = null;
    protected $singlesId = null;
    protected $setsData = null;
    protected $nextPriority = null;
    protected $currentCustomTableData = null;
    protected $nextPriorityMappingAttributeCode = null;
    protected $nextPriorityMappingAttributeVal = null;
    protected $customTableType = null;
    protected $customTableCategories = null;
    protected $mainAttrVal = null;
    protected $mainProductTypeAttrVal = null;
    
    protected $setsTableName = null;
    protected $sealedProductsTableName = null;
    protected $singlesTableName = null;
    protected $mainAttrCode = null;
    protected $mainProductTypeAttrCode = null;
    protected $mainReleaseAttrCode = null;
    protected $mainSetsAttrCode = null;
    protected $mainSetsNumberAttrCode = null;
    protected $mainSinglesAttrCode = null;
    protected $mainSinglesNumberAttrCode = null;

    protected $customTableData = null;

    protected $customTableModel = null;

    protected $fieldsToDownload = [];
    protected $fieldsToDownloadForCatalog = [];
    protected $processedAttributeCodes = [];
    protected $attributeSetData = null;

    protected $registry;
    protected $messageManager;
    protected $backendUrl;

    protected $debugGetNextPriorityMappingAttributeCode = [];

    // Custom table
    protected $cardGameSetsFactory;
    protected $resource;

    protected $prdAttrLinkCollFactory;
    protected $categoriesName;
    protected $mainCategoriesConfigProvider;
    protected $categoryId = null;
    protected $isDebug = null;
    protected $cache;
    protected $mainAttrValLabel = null;
    protected $mainCategoriesConfigData = null;
    
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Store\App\Response\Redirect $redirect,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\App\State $state,
        \TCGCollective\ProductAttributesLink\Helper\Data $helper,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \TCGCollective\ProductAttributesLink\Model\CardGamesSetsFactory $cardGameSetsFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Backend\Model\UrlInterface $backendUrl,
        LoggerInterface $logger,
        \Magento\Framework\App\ResourceConnection $resource,
        CacheInterface $cache,
        \TCGCollective\ProductAttributesLink\Model\ResourceModel\ProductAttributesLink\CollectionFactory $prdAttrLinkCollFactory,
        \TCGCollective\PlatformDatabase\Model\MainCategoriesConfigProvider $mainCategoriesConfigProvider
    ) {
        parent::__construct(
            $context
        );
        $this->mainCategoriesConfigProvider = $mainCategoriesConfigProvider;
        $this->prdAttrLinkCollFactory = $prdAttrLinkCollFactory;
        $this->logger = $logger;
        $this->urlBuilder = $context->getUrlBuilder();
        $this->request = $request;
        $this->redirect = $redirect;
        $this->state = $state;
        $this->helper = $helper;
        $this->messageManager = $messageManager;
        $this->registry = $registry;
        $this->backendUrl = $backendUrl;
        $this->resource = $resource;
        $this->cache = $cache;
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);

        // Custom table
        $this->cardGameSetsFactory = $cardGameSetsFactory;

        $this->logger->info('== LOGGER | PlatformDatabaseHelper.php ==');
    }

    public function getAttributeSetMappingAttributeCodesArr() 
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

        return $fields;

        // return [
        //     'card_product_type',
        //     'action_toys_categories'
        // ];
    }

    // ADMIN / MARKETPLACE | get page title
    public function getPlatformDbTitle() {
        return $this->processGetPlatformDbTitle();
    }
    // ADMIN / MARKETPLACE | delete data
    public function deleteCustomTableData() {
        return $this->processDeleteCustomTableData();
    }

    public function syncPlatformDatabaseToCustomAttributes() 
    {
        $this->processSyncPlatformDatabaseToCustomAttributes();
    }

    public function processSyncPlatformDatabaseToCustomAttributes() 
    {
        $this->initCustomTableData();

        $connection = $this->resource->getConnection();

        $customTableData = $this->customTableData;

        $singlesTableName = $customTableData['singlesTableName'];
        $setsTableName = $customTableData['setsTableName'];
        $sealedProductsTableName = $customTableData['sealedProductsTableName'];

        /** =============================
         *  Get UNIQUE rows (all columns)
         * ============================== */

        // Singles
        $uniqueSingles = $connection->fetchAll(
            $connection->select()
                ->distinct()
                ->from($singlesTableName) // no specific columns = all columns
        );

        dd($uniqueSingles);

        // Sets
        $uniqueSets = $connection->fetchAll(
            $connection->select()
                ->distinct()
                ->from($setsTableName)
        );

        // Sealed Products
        $uniqueSealed = $connection->fetchAll(
            $connection->select()
                ->distinct()
                ->from($sealedProductsTableName)
        );


    }

    public function getCacheTag() 
    {
        return self::CACHE_TAG;
    }

    // IMPORT SETS/SEALED PRODUCTS/SINGLES | sync to custom attributes
    public function syncToCustomAttributes($dataToSync, $customAttributesTableName) {
        return $this->processSyncToCustomAttributes($dataToSync, $customAttributesTableName);
    }

    public function processSyncToCustomAttributes($dataToSync, $customAttributesTableName) 
    {
        $counts = array_count_values(array_column($dataToSync, 'unique_key'));
        
        // Ambil hanya unique_key yang muncul lebih dari 1 (duplikat)
        $duplicates = array_filter($counts, function ($count) {
            return $count > 1;
        });
        
        $connection = $this->resource->getConnection();
        $tableName = $this->resource->getTableName($customAttributesTableName);
        foreach ($dataToSync as $row) {
            $connection->insertOnDuplicate(
                $tableName,
                $row,
                ['attribute_value']
            );
        }
    }

    public function removePlatformDatabaseCache($categoryName, $customTableType) 
    {
        $cacheKey = self::CACHE_TAG . '_' . $this->convertToSlug($categoryName) . '_' . $customTableType;
        // dd($cacheKey);
        $this->removeCache($cacheKey);

        $cacheKey = self::CACHE_TAG . '_' . $this->convertToSlug($categoryName) . '_index';
        // dd($cacheKey);
        $this->removeCache($cacheKey);

        return true;
    }

    public function marketplaceIsAllowedDownload() 
    {
        $currentUrl = $this->getCurrentUrl();
        if (
            str_contains($currentUrl, 'sealedproducts/index') 
            || str_contains($currentUrl, 'singles/index')
        ) {
            return true;
        }

        if (
            str_contains($currentUrl, 'sets/singles') 
            || str_contains($currentUrl, 'sets/index') 
            && !str_contains($currentUrl, 'cardgame')
        ) {
            return true;
        }

        return false;
    }

    // EXTERNAL | UPDATE CUSTOM TABLE DATA
    public function updateCustomTableData()
    {
        $this->initCustomTableType();
        $this->initCustomTableData();

        try {
            // Get sets/singles model
            $this->initCustomTableModel();
            
            $data = $this->request->getParams();
            $exist = false;

            $id = isset($data['entity_id']) ? $data['entity_id'] : null;

            // Get sets data
            $model = $this->customTableModel;
            // dd($model);
            // $model = $this->helper->getSinglesById('action_toys', $id, true);
            $collection = $model->getCollection()->addFieldToFilter('entity_id', $id);
            if ($collection->getSize()) {
                // $model = $collection->getFirstItem();

                // $data['entity_id'] = $collection->getFirstItem()->getId();
                $setId = $collection->getFirstItem()->getSetId();
                // dd($setId
                // dd($model->getData());

                $exist = true;

                $model->load($id);
                $model->setData($data);
                $model->save();

                // Update data di sets (singles only)
                $this->updateSetsData($data);
            }
            
            if ($exist) {
                $this->messageManager->addSuccess(__('Data updated successfully'));
            } else {
                $this->messageManager->addSuccess(__('Data saved successfully'));
            }
        } catch (\Exception $e) {   
            $this->messageManager->addError(__($e->getMessage()));
        }

        $refererUrl = $this->redirect->getRefererUrl();
        return $refererUrl;
    }

    public function updateSetsData($data) 
    {
        if ($this->customTableType == "singles") {
            $setsModel = $this->getCustomTableModel($this->mainAttrCode, 'sets');
            if ($setsModel) {
                $setId = $this->request->getParam('set_id');
                $setsModel->load($setId);
                $data['entity_id'] = $setId;

                // Jangan update sets specific data
                $data = array_filter(
                    $data,
                    fn($key) => !in_array($key, ['created_at','updated_at','images', 'name', 'supplier_sku']),
                    ARRAY_FILTER_USE_KEY
                );

                $setsModel->setData($data);
                $setsModel->save();
            }
        }
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

    // EXTERNAL | MARKETPLACE DOWNLOAD CSV
    public function marketplaceDownloadPlatformDatabase() 
    {
        $this->initCustomTableData();

        $result = [
            'success' => false,
            'name' => null,
            'file' => null,
            'refererUrl' => null
        ];
        
        $this->initCustomTableModel();
        
        // Get sets data
        $model = $this->customTableModel;
        
        $collection = $model->getCollection();

        $mainAttrColumn = $this->mainAttrCode;

        // Filter sets that only having singles
        // if ($this->customTableType == "sets") {
        //     $collection->getSelect()->join(
        //         ['singles' => $this->singlesTableName],
        //         'main_table.entity_id = singles.set_id',
        //         []
        //     )->group('main_table.entity_id');

        //     $mainAttrColumn = $this->mainAttrCode;
        // }
        
        $connection = $this->customTableModel->getResource()->getConnection();
// dd($this->customTableType);
        // Get sets data
        if ($this->customTableType == "singles") {
            $setsTableName = $this->customTableModel->getResource()->getTable($this->setsTableName);

            // Ambil daftar kolom dari DB
            $setsTableColumns = array_keys($connection->describeTable($setsTableName));

            // dd($setsTableColumns);
            // dd($this->fieldsToDownload);

            // $fieldToSelectFromSets = array_intersect($this->fieldsToDownload, $setsTableColumns);
            
            $fieldToSelectFromSets2 = array_filter(
                $setsTableColumns, fn($col) => !in_array($col, ['entity_id', 'images','name','supplier_sku'])
            );

            $collection->getSelect()->join(
                ['sets' => $this->setsTableName],
                'sets.entity_id = main_table.set_id',
                []
            )->group('main_table.entity_id');
        }

        if ($this->customTableType == "singles") {
            $mainAttrColumn = 'sets.' . $this->mainAttrCode;
        }

        // Filter by main attribute code
        $collection->addFieldToFilter($mainAttrColumn, $this->mainAttrVal);
        

        if ($this->customTableType == "singles") {
            $collection->addFieldToFilter('set_id', $this->setId);

            $model = $this->getCustomTableModel($this->mainAttrCode, 'sets');
            if ($model) {
                // dd($model);
                // dd($this->setId);
                $setscollection = $model->getCollection();
                $setscollection->addFieldToFilter('entity_id', $this->setId);
                $this->currentCustomTableData = $setscollection->getFirstItem()->getData();
            }
        } else {
            $this->currentCustomTableData = $collection->getFirstItem()->getData();
        }

        // dd($this->currentCustomTableData);

        $this->getAttributeSetFieldsToDownload(true);

        $fieldToSelects = [];

        if ($this->customTableType == "singles") {
            $setsTableName = $this->customTableModel->getResource()->getTable($this->setsTableName);

            // Ambil daftar kolom dari DB
            $setsTableColumns = array_keys($connection->describeTable($setsTableName));

            // dd($setsTableColumns);
            // dd($this->fieldsToDownload);

            $fieldToSelectFromSets = array_intersect($this->fieldsToDownload, $setsTableColumns);
            
            $fieldToSelectFromSets = array_filter(
                $fieldToSelectFromSets, fn($col) => !in_array($col, ['entity_id', 'images','name','supplier_sku'])
            );

            $fieldToSelectFromSets = array_map(fn($col) => "sets.$col", $fieldToSelectFromSets);
            // dd($fieldToSelectFromSets);
            
            
            // $collection->getSelect()->columns($fieldToSelectFromSets);
            $collection->getSelect()->columns($fieldToSelectFromSets, 'sets');
        }

        $tableName = $this->customTableModel->getResource()->getMainTable();

        // Ambil daftar kolom dari DB
        $tableColumns = array_keys($connection->describeTable($tableName));
        $validFields = array_intersect($this->fieldsToDownload, $tableColumns);

        // $fieldToSelects = array_merge($fieldToSelects, $fieldToSelectFromSets);
        // $validFields = array_merge($validFields, $fieldToSelectFromSets);

        // dd($this->fieldsToDownload);

        // dd($validFields);
        // dd($fieldToSelectFromSets);

$collection->getSelect()->columns($validFields, 'main_table');

        // $collection->addFieldToSelect($validFields);
        // $collection->getSelect()->columns($validFields);
        // $collection->getSelect()->columns($fieldToSelectFromSets);
        
        if (!$collection->getSize()) {
            $this->messageManager->addError(__('We can\'t find a record to exportcsv.'));

            return $result;
        }

        // dd($collection->getSelect()->__toString());
        // dd($collection->getSize());

        $items = $collection->getData();

        // dd($items[0]);
        // dd($this->fieldsToDownloadForCatalog);
        
        // dd($items[0]);

        $this->defaultWebsiteName = '';

                
                $objectManager =  \Magento\Framework\App\ObjectManager::getInstance();
                $this->websiteRepository = $objectManager->get('\Magento\Store\Api\WebsiteRepositoryInterface');
                $this->lofSellerHelper = $objectManager->get('\Lof\MarketPlace\Helper\Seller');

                
                // $defaultWebsite = $this->storeManager->getStore()->getWebsite()->getName();
                $seller = $this->lofSellerHelper->getSeller();
                $defaultWebsiteCode = $seller->getDefaultWebsiteCode() ? strtolower($seller->getDefaultWebsiteCode()) : null;

                $defaultWebsite = $this->websiteRepository->get($defaultWebsiteCode);
                if ($defaultWebsite) {
                    $this->defaultWebsiteName = $defaultWebsite->getName();
                }
                // dd($defaultWebsiteName);

        
        // Get only image basename
        // Change master attribute value to label
        $items = array_map(
            function ($r) {
                // dd($this->mainProductTypeAttrVal);
                // dd($this->mainProductTypeAttrCode);
                if (key_exists($this->mainProductTypeAttrCode, $r)) {
                    $r[$this->mainProductTypeAttrCode] = $this->mainProductTypeAttrVal;
                }

                if ($this->isDebug()) {
                    $mappingGroupName = $this->attributeSetData['mapping_group_name'] ?? null;
                    $r['attribute_set'] = $mappingGroupName;
                }

                $singlesAttrValLabel = $r[$this->mainSinglesAttrCode] ?? null;
                $setAttrValLabel = $r[$this->mainSetsAttrCode] ?? null;
                $mainAttrValLabel = $this->mainAttrValLabel ?? null;

                $name = $r['name'] ?? null;
                if (!$name) {
                    if ($singlesAttrValLabel) {
                        $name .= $singlesAttrValLabel . ' | ';
                    }

                    if ($setAttrValLabel) {
                        $name .= $setAttrValLabel . ' | ';
                    }
                    
                    if ($mainAttrValLabel) {
                        $name .= $mainAttrValLabel;
                    }
                }

                $supplierSku = $r['supplier_sku'] ?? null;
                if (!$supplierSku) {
                    if ($singlesAttrValLabel) {
                        $supplierSku .= $this->convertToSlug($singlesAttrValLabel) . '-';
                    }

                    if (!$singlesAttrValLabel && $setAttrValLabel) {
                        $supplierSku .= $this->convertToSlug($setAttrValLabel) .'-';
                    }
                    
                    $supplierSku .= $this->makeId(4);
                }

                
                $r['name'] = $name;
                $r['supplier_sku'] = $supplierSku;

                // PUBLISH DATA
                $r['publish_status'] = 0;
                $r['listing_country'] = $this->defaultWebsiteName;
                $r['price'] = 0;
                $r['qty'] = 0;
                

                $imagesData = '';
                $thumbData = '';
                
                $imagesDataRaw = $r['images'] ?? null;
                // dd($imagesDataRaw);
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

                if (isset($r['release_date']) && $r['release_date']) {
                    // dd($r['release_date']);
                    $date = \DateTime::createFromFormat('d/m/Y', $r['release_date']);
                    $r['release_date'] = $date->format('Y-m-d'); // hasil: 2025-02-14;
                }
                
                // IMAGES
                $r['base_image'] = (string)trim($thumbData);
                $r['additional_images'] = (string)trim($imagesData);
                $r['categories'] = $this->getCategoriesName();

                

                

                // dd($r);

                // Lanjutkan logika existing
                return array_replace(
                    array_fill_keys($this->fieldsToDownloadForCatalog, null),
                    $r,
                    [$this->mainAttrCode => $this->mainAttrValLabel]
                    // [$this->mainAttrCode => $this->mainAttrValLabel]
                );
            },
            $items,
        );

        // dd($items);

        $items = $this->transformData($items, $this->fieldsToDownloadForCatalog, $this->prefix);
        // dd($items);

        // set headeres
        $headers = array_keys($items[0]);

        // Set filename
        $fileName = '';
        if ($this->mainAttrValLabel) {
            $fileName .= $this->helper->convertToFileName($this->mainAttrValLabel);
        }

        // Include set name on file name
        $set = $this->currentCustomTableData[$this->mainSetsAttrCode] ?? null;
        if ($set) {
            $fileName .= '-' . $this->helper->convertToFileName($set);
        }

        // dd($this->customTableType);

        $fileName .= '-' . $this->customTableType;
        $fileName .= '__' . $this->helper->getDateTime('Y-m-d') . '_' . $this->helper->getDateTime('H-i-s') . '.csv';
        
        // File path
        $file = 'export/'.$this->customTableType.'/'. $fileName;

        // Generate csv file
        $this->directory->create('export');
        $stream = $this->directory->openFile($file, 'w+');
        $stream->lock();
        $stream->writeCsv($headers);

        // Process data before write to CSV
        foreach ($items as $row) {
            $stream->writeCsv($row);
        }
        
        $stream->unlock();
        $stream->close();
        
        $result = [
            'success' => true,
            'name' => $fileName,
            'file' => [
                'type' => 'filename',
                'value' => $file,
                'rm' => true  // can delete file after use
            ]
        ];

        return $result;
    }

    public function getCategoriesName() 
    {
        $mainAttrCode = $this->mainAttrCode;
        if ($mainAttrCode == "card_game") {
            return "Collectible Card Games";
        }

        return null;
    }

    // ✨ Transform otomatis: kombinasi prefix + non-prefix
    public function transformData(array $rows, array $final_keys, string $prefix): array
{
    $result = [];

    // 💡 Precompute mapping dulu
    $map = [];
    $prefixLen = strlen($prefix);

    foreach ($final_keys as $key) {
        // Jika key pakai prefix, simpan key asal (tanpa prefix)
        if (strpos($key, $prefix) === 0) {
            $map[$key] = substr($key, $prefixLen);
        } else {
            $map[$key] = $key;
        }
    }

    // 💨 Sekarang loop barisnya aja (1 foreach)
    foreach ($rows as $row) {
        // ambil langsung dengan array_map-like
        $new = [];
        foreach ($map as $finalKey => $oldKey) {
            $new[$finalKey] = $row[$oldKey] ?? null;
        }
        $result[] = $new;
    }

    return $result;
}

    public function downloadPlatformDatabaseSampleCsv() 
    {
        $this->initCustomTableData();

        $this->getAttributeSetFieldsToDownload(false, true);
        
        $this->fieldsToDownload = array_filter(
                $this->fieldsToDownload, fn($col) => !in_array($col, ['entity_id'])
            );
        $items = $this->fieldsToDownload;
        if ($items) {
            $items = array_fill_keys($items, "");

            

            $items = [$items];

            // dd($items);

            $items = array_map(
                function ($r) {

                    if ($this->isDebug()) {
                        $mappingGroupName = $this->attributeSetData['mapping_group_name'] ?? null;
                        $r['attribute_set'] = $mappingGroupName;
                    }

                    // Lanjutkan logika existing
                    return array_replace(
                        array_fill_keys($this->fieldsToDownload, null),
                        $r,
                        [$this->mainAttrCode => $this->mainAttrValLabel]
                    );
                },
                $items
            );


            // dd($items);

            return $this->generateCsvFile($items);
        }

        return null;
    }
    

    // EXTERNAL | ADMIN DOWNLOAD CSV
    public function downloadPlatformDatabase() 
    {
        $this->initCustomTableData();
        $this->initCustomTableModel();
        
        // Get sets data
        $model = $this->customTableModel;
        
        $collection = $model->getCollection();

        // dd($collection);

        $mainAttrColumn = $this->mainAttrCode;

        // dd($mainAttrColumn);

        // Filter sets that only having singles
        // if ($this->customTableType == "sets") {
        //     $collection->getSelect()->join(
        //         ['singles' => $this->singlesTableName],
        //         'main_table.entity_id = singles.set_id',
        //         []
        //     )->group('main_table.entity_id');

        //     $mainAttrColumn = $this->mainAttrCode;
        // }
        
        $connection = $this->customTableModel->getResource()->getConnection();
// dd($this->customTableType);
        // Get sets data
        if ($this->customTableType == "singles") {
            $setsTableName = $this->customTableModel->getResource()->getTable($this->setsTableName);

            // Ambil daftar kolom dari DB
            $setsTableColumns = array_keys($connection->describeTable($setsTableName));

            // dd($setsTableColumns);
            // dd($this->fieldsToDownload);

            // $fieldToSelectFromSets = array_intersect($this->fieldsToDownload, $setsTableColumns);
            
            $fieldToSelectFromSets2 = array_filter(
                $setsTableColumns, fn($col) => !in_array($col, ['entity_id', 'images','name','supplier_sku'])
            );

            $collection->getSelect()->join(
                ['sets' => $this->setsTableName],
                'sets.entity_id = main_table.set_id',
                []
            )->group('main_table.entity_id');
        }

        if ($this->customTableType == "singles") {
            $mainAttrColumn = 'sets.' . $this->mainAttrCode;
        }

        // dd($this->mainAttrCode);

        // dd($mainAttrColumn);

        // Filter by main attribute code
        $collection->addFieldToFilter($mainAttrColumn, $this->mainAttrVal);
        

        // if ($this->customTableType == "singles") {
        //     $collection->addFieldToFilter('set_id', $this->setId);

        //     $model = $this->getCustomTableModel($this->mainAttrCode, 'sets');
        //     if ($model) {
        //         // dd($model);
        //         // dd($this->setId);
        //         $setscollection = $model->getCollection();
        //         $setscollection->addFieldToFilter('entity_id', $this->setId);
        //         $this->currentCustomTableData = $setscollection->getFirstItem()->getData();
        //     }
        // } else {
            $this->currentCustomTableData = $collection->getFirstItem()->getData();
        // }

        // dd($this->currentCustomTableData);

        $this->getAttributeSetFieldsToDownload();

        if (!$this->fieldsToDownload) {
            return null;
        }

        if ($this->customTableType == "singles") {
            $setsTableName = $this->customTableModel->getResource()->getTable($this->setsTableName);

            // Ambil daftar kolom dari DB
            $setsTableColumns = array_keys($connection->describeTable($setsTableName));

            // dd($setsTableColumns);
            // dd($this->fieldsToDownload);

            $fieldToSelectFromSets = array_intersect($this->fieldsToDownload, $setsTableColumns);
            
            $fieldToSelectFromSets = array_filter(
                $fieldToSelectFromSets, fn($col) => !in_array($col, ['entity_id', 'images','name','supplier_sku'])
            );

            $fieldToSelectFromSets = array_map(fn($col) => "sets.$col", $fieldToSelectFromSets);
            // dd($fieldToSelectFromSets);
            
            
            $collection->getSelect()->columns($fieldToSelectFromSets);
        }

        $tableName = $this->customTableModel->getResource()->getMainTable();

        // Ambil daftar kolom dari DB
        $tableColumns = array_keys($connection->describeTable($tableName));
        $validFields = array_intersect($this->fieldsToDownload, $tableColumns);

        // dd($this->fieldsToDownload);

        $collection->addFieldToSelect($validFields);

        if ($this->customTableType == "singles") {
            $collection->addFieldToFilter('set_id', $this->setId);
        }
        
        if (!$collection->getSize()) {
            $this->messageManager->addError(__('We can\'t find a record to exportcsv.'));

            return $result;
        }

        // dd($collection->getSelect()->__toString());
        // dd($collection->getSize());

        $items = $collection->getData();

        // dd('<pre>'.print_r($items,true));
        // dd('<pre>'.print_r($this->fieldsToDownload,true));

        // dd($this->prefix);

        // Get only image basename
        // Change master attribute value to label
        $items = array_map(
            function ($r) {
                // Ubah nilai 'images' menjadi hanya nama file
                if (!empty($r['images'])) {
                    $imageName = basename($r['images']);
                    $r['images'] = $imageName;
                } else {
                    $r['images'] = '';
                }

                if ($this->isDebug()) {
                    $mappingGroupName = $this->attributeSetData['mapping_group_name'] ?? null;
                    $r['attribute_set'] = $mappingGroupName;
                }

                // Lanjutkan logika existing
                return array_replace(
                    array_fill_keys($this->fieldsToDownload, null),
                    $r,
                    [$this->mainAttrCode => $this->mainAttrValLabel]
                    // [$this->mainAttrCode => $this->mainAttrValLabel]
                );
            },
            $items
        );

        // dd('<pre>'.print_r($items,true));
        
        return $this->generateCsvFile($items);
    }

    public function generateCsvFile($items) 
    {
        $result = [
            'success' => false,
            'name' => null,
            'file' => null,
            'refererUrl' => null
        ];

        // set headeres
        $headers = array_keys($items[0]);

        // Set filename
        $fileName = '';
        if ($this->mainAttrValLabel) {
            $fileName .= $this->helper->convertToFileName($this->mainAttrValLabel);
        }

        // dd($this->customTableType);

        $fileName .= '-' . $this->customTableType;
        $fileName .= '__' . $this->helper->getDateTime('Y-m-d') . '_' . $this->helper->getDateTime('H-i-s') . '.csv';
        
        // File path
        $file = 'export/sets/'. $fileName;

        // Generate csv file
        $this->directory->create('export');
        $stream = $this->directory->openFile($file, 'w+');
        $stream->lock();
        $stream->writeCsv($headers);

        // Process data before write to CSV
        foreach ($items as $row) {
            $stream->writeCsv($row);
        }
        
        $stream->unlock();
        $stream->close();
        
        $result = [
            'success' => true,
            'name' => $fileName,
            'file' => [
                'type' => 'filename',
                'value' => $file,
                'rm' => true  // can delete file after use
            ]
        ];

        return $result;
    }

    public function getCustomAttributesListingDataProvider() 
    {
        $isAjax = $this->request->getParam('isAjax');
        if (!$isAjax) {
            return null;
        }

        $cacheKey = self::CACHE_TAG;
        
        $cachedData = $this->cache->load($cacheKey);
        if ($cachedData) {
            // dd('pk');
            // return unserialize($cachedData);
        }
        
        $this->initCustomTableData();
        $this->initCustomTableModel();

        // dd('334sss');

        // dd($this->customTableModel);

        $collection = $this->customTableModel;

        // dd($this->mainAttrCode);
        // dd($this->customTableModel);
        
        // dd('ok');

        $refererUrl = $this->redirect->getRefererUrl();

        // $this->categoryId = $category->getId();

        // dd($this->categoryId);

        // INIRIO

        // $categoryId = '';

        // dd($collection);

        

        if (str_contains($refererUrl, '/customattributes/')) {
            // dd($this->mainAttrVal);
            // $mainAttrCode = $this->mainCategoriesConfigData['mainAttrCode'] ?? null;
            $collection->addFieldToFilter($this->mainAttrCode, $this->mainAttrVal);
            if (str_contains($refererUrl, '/customattributes/linkedvalues/')) {
                // dd($this->request->getParams());
                $collection->addFieldToFilter('linked_attribute_code', ['neq' => null]);
                $collection->addFieldToFilter('attribute_code', $this->request->getParam('attribute_code'));
                $collection->addFieldToFilter('linked_attribute_code', $this->request->getParam('linked_attribute_code'));
                $collection->addFieldToFilter('attribute_value', $this->request->getParam('attribute_value'));
                $collection->getSelect()
                ->group('main_table.linked_attribute_value');

                // dd($collection->getSelect()->__toString());
                // dd($collection->getData());
                // $collection->addFieldToFilter('linked_attribute_code', $this->request->getParam('linked_attribute_code'));
                // $collection->addFieldToFilter('linked_attribute_code', ['null' => false]);
            } elseif (str_contains($refererUrl, '/customattributes/linked/')) {
                // dd($this->request->getParams());
                $collection->addFieldToFilter('linked_attribute_code', ['neq' => null]);
                $collection->addFieldToFilter('attribute_code', $this->request->getParam('attribute_code'));
                $collection->addFieldToFilter('attribute_value', $this->request->getParam('attribute_value'));
                $collection->getSelect()
                ->columns([
                    'number_of_options' => new \Zend_Db_Expr(
                    'COUNT(DISTINCT linked_attribute_value)'
                )])
                ->group('main_table.linked_attribute_code');

                // dd($collection->getSelect()->__toString());
                // dd($collection->getData());
                // $collection->addFieldToFilter('linked_attribute_code', $this->request->getParam('linked_attribute_code'));
                // $collection->addFieldToFilter('linked_attribute_code', ['null' => false]);
            } elseif (str_contains($refererUrl, '/customattributes/values/')) {
                $collection->addFieldToFilter('attribute_code', $this->request->getParam('attribute_code'));
                $collection->addFieldToFilter('linked_attribute_code', ['null' => true]);
            } else {
                $collection->getSelect()
                ->columns([
                    'number_of_options' => new \Zend_Db_Expr(
                    'COUNT(DISTINCT attribute_value)'
                )])
                ->group('main_table.attribute_code');
            }
        } elseif (str_contains($refererUrl, 'attributes')) {
            // $collection->addFieldToFilter('category_id', $this->categoryId);

            // $collection->getSelect() 
            // ->columns(['number_of_attributes' => new \Zend_Db_Expr('COUNT(*)')])
            // ->group('main_table.attribute_code');
            // dd($this->mainCategoriesConfigData);
            $customAttributesTableName = $this->mainCategoriesConfigData['customAttributesTableName'] ?? null;
            $this->mainAttrCode = $this->mainCategoriesConfigData['mainAttrCode'] ?? null;

            $mainAttrCodeColumn = 'custom_attributes.' . $this->mainAttrCode;

            $collection->getSelect()->join(
                ['eav_attribute' => 'eav_attribute'],
                'eav_attribute.attribute_id = main_table.attribute_id',
                []
            )->where('eav_attribute.attribute_code = "'.$this->mainAttrCode.'"');

            $collection->getSelect()->join(
                ['eaov' => 'eav_attribute_option_value'],
                'main_table.option_id = eaov.option_id',
                [$this->mainAttrCode => 'eaov.value']
            );

            $collection->getSelect()->join(
                ['custom_attributes' => $customAttributesTableName],
                $mainAttrCodeColumn . ' = main_table.option_id',
                []
            )->group('main_table.option_id');

            $collection->getSelect()
            // ->reset(\Zend_Db_Select::COLUMNS)
            ->columns([
                'custom_attributes.attribute_code',
                'number_of_attributes' => new \Zend_Db_Expr(
                    'COUNT(DISTINCT custom_attributes.attribute_code)'
                )
            ])
            ->group($mainAttrCodeColumn);

            // dd($collection->getFirstItem()->getData());
            // dd($collection->getSelect()->__toString());
            
            // // dd($this->mainAttrCode);
            
            // $collection->getSelect()->join(
            //     ['ea' => 'eav_attribute'],
            //     'ea.attribute_id = main_table.attribute_id',
            //     ['*']
            // );

            // // $this->collection->getSelect()->join(
            // //     ['eaov' => 'eav_attribute_option_value'],
            // //     'main_table.option_id = eaov.option_id',
            // //     []
            // // );

            // $joinFields = $mainAttrCodeColumn ." = main_table.option_id";
            
            // $collection->getSelect()->join(
            //     ['custom_attributes' => $customAttributesTableName],
            //     $joinFields,
            //     ['*']
            // );

            // $collection->getSelect()
            // ->reset(\Zend_Db_Select::COLUMNS)
            // ->columns([
            //     $mainAttrCodeColumn,
            //     'custom_attributes.attribute_code',
            //     'number_of_attributes' => new \Zend_Db_Expr(
            //         'COUNT(DISTINCT custom_attributes.attribute_code)'
            //     )
            // ])
            // ->group($mainAttrCodeColumn);

            // $collection->getSelect()
            // ->where("ea.attribute_code='" . $this->mainAttrCode . "'")
            // ->group('main_table.option_id');

            
            // dd($collection->getFirstItem()->getData());
            // dd($collection->getSelect()->__toString());
            
            // $collection->getSelect()
            // ->reset(\Zend_Db_Select::COLUMNS)
            // ->columns([
            //     $mainAttrCodeColumn,
            //     'number_of_attributes' => new \Zend_Db_Expr(
            //         'COUNT(DISTINCT custom_attributes.attribute_code)'
            //         )
            // ])
            // ->group($mainAttrCodeColumn);
            // ->group('main_table.attribute_value')
            // ->group('main_table.linked_attribute_code')
            // ->group('main_table.linked_attribute_value');
        }
        
        // if (!str_contains($refererUrl, 'customattributes')) {
        //     // dd($collection);
        //     $collection->getSelect()->join(
        //         ['eav_attribute' => 'eav_attribute'],
        //         'eav_attribute.attribute_id = main_table.attribute_id',
        //         []
        //     )->where('eav_attribute.attribute_code = "' . $this->mainAttrCode .'"');

        //     $collection->getSelect()->join(
        //         ['eaov' => 'eav_attribute_option_value'],
        //         'main_table.option_id = eaov.option_id',
        //         [$this->mainAttrCode => 'eaov.value']
        //     );

        //     $collection->getSelect()->join(
        //         ['customAttributesTable' => 'marketplace_custom_attributes'],
        //         'main_table.option_id = customAttributesTable.main_attr_val',
        //         []
        //     )->group('main_table.option_id');

        //     $filters = $this->request->getParam('filters') ?? [];
        //     $grid_filters = $this->request->getParam('grid_filters') ?? [];

        //     $filters = array_merge($filters, $grid_filters);
        //     if ($filters) {
        //         foreach($filters as $column => $value) {
        //             if ($column == "placeholder") {
        //                 continue;
        //             }

        //             $dbColumn = 'eaov.value';

        //             // if (in_array($column, $inputTypesAttributeCodes) || $column == $mainAttrCode) {
        //                 $collection->addFieldToFilter(
        //                     $dbColumn,
        //                     [
        //                         ['like' => strtolower($value) . '%'],
        //                         ['like' => '%' . strtolower($value)],
        //                         ['like' => '%' . strtolower($value) . '%']
        //                     ]
        //                 );
        //             // } else {
        //             //     $collection->addFieldToFilter($dbColumn, ['eq' => $value]);
        //             // }
        //         }
        //     }
        //     // dd($filters);

        //     $collection->setOrder('eaov.value', 'ASC');

        //     $collection->addFilterToMap($this->mainAttrCode, 'eaov.value');
        // }

        $result = $collection->toArray();
        // dd($result);
        
        $this->cache->save(
            serialize($result),
            $cacheKey,
            [self::CACHE_TAG]
        );

        return $result;
    }

    // EXTERNAL | DELETE CUSTOM TABLE DATA
    public function processDeleteCustomTableData() 
    {
        $refererUrl = $this->redirect->getRefererUrl();

        $this->initCustomTableData();

        // dd($this->customTableType);

        if ($this->customTableType == "customattributes") {
            $count = 0;
            $entity = null;

            $categories = null;
            $atttributeCode = null;
            $mainAttrVal = null;

            $id = $this->request->getParam('id') ?: null;
            $selectedIds = $this->request->getParam('selected') ?: null;
            $excluded = $this->request->getParam('excluded') ?: null;
            
            if ($id || $selectedIds || !$selectedIds && $excluded == "false") {
                try {
                    $this->initCustomTableModel();

                    // dd($this->customTableModel);

                    $collection = $this->customTableModel->getCollection();
                    if ($id) {
                        $collection->addFieldToFilter('entity_id', $id);
                    } elseif ($selectedIds) {
                        $collection->addFieldToFilter('entity_id', ['in' => $selectedIds]);
                    } elseif (!$excluded == "false") {
                        $this->messageManager->addError(__('Please select data to delete.'));
                        return $refererUrl;
                    }

                    if ($collection->getSize()) {
                        foreach($collection as $model) {
                            $categories = $model->getCategories();
                            $atttributeCode = $model->getAttributeCode();
                            $mainAttrVal = $model->getMainAttrVal();

                            $model->delete();

                            $count++;
                        }
                    }
                    
                    $this->messageManager->addSuccess(__('A total of %1 data have been deleted.', $count));
                } catch (\Exception $e) {
                    $this->messageManager->addError(__($e->getMessage()));
                }

                // if ($setId) {
                //     return $this->backendUrl->getUrl('*/*/index/set_id/'.$setId);
                // }

                $currentUrl = $this->getCurrentUrl();

//                 $path = parse_url($currentUrl, PHP_URL_PATH);

//                 // pisahkan berdasarkan "/"
//                 $parts = explode("/", trim($path, "/"));

//                 // ambil bagian ke-2 dan ke-3
//                 $result = $parts[1] . "/" . $parts[2];
// // 
//                 // dd($currentUrl);

//                 $action = 'index';
//                 // if (str_contains($currentUrl, '/singles/')) {
//                 //     $action = 'singles';
//                 // }

                return $this->backendUrl->getUrl('productattributeslink/customattributes/values/categories/' . $categories .'/attribute_code/'.$atttributeCode .'/id/'.$mainAttrVal);
            } else {
                $this->messageManager->addError(__('Please select data to delete.'));
            }
        }

        // DELETE SINGLES
        if ($this->customTableType == "singles") {
            $setId = null;
            // dd($refererUrl);
            if ($refererUrl) {
                if (preg_match('/set_id\/(\d+)/', $refererUrl, $matches)) {
                    $setId = $matches[1]; // The first capturing group contains the set_id
                }

                if (preg_match('/id\/(\d+)/', $refererUrl, $matches)) {
                    $setId = $matches[1]; // The first capturing group contains the set_id
                }
            }
            
            $count = 0;
            $entity = null;

            $id = $this->request->getParam('id') ?: null;
            $selectedIds = $this->request->getParam('selected') ?: null;
            $excluded = $this->request->getParam('excluded') ?: null;
            
            if ($id || $selectedIds || !$selectedIds && $excluded == "false") {
                try {
                    $this->initCustomTableModel();

                    $collection = $this->customTableModel->getCollection();
                    if ($id) {
                        $collection->addFieldToFilter('entity_id', $id);
                    } elseif ($selectedIds) {
                        $collection->addFieldToFilter('entity_id', ['in' => $selectedIds]);
                    } elseif ($excluded == "false" && $setId) {
                        $collection->addFieldToFilter('set_id', $setId);
                    } else {
                        $this->messageManager->addError(__('Please select data to delete.'));
                        return $refererUrl;
                    }

                    if ($collection->getSize()) {
                        foreach($collection as $model) {
                            if ($model->getSetId()) {
                                $setId = $model->getSetId();
                            }

                            $model->delete();

                            $count++;
                        }
                    }
                    
                    $this->messageManager->addSuccess(__('A total of %1 data have been deleted.', $count));
                } catch (\Exception $e) {
                    $this->messageManager->addError(__($e->getMessage()));
                }

                // if ($setId) {
                //     return $this->backendUrl->getUrl('*/*/index/set_id/'.$setId);
                // }

                $currentUrl = $this->getCurrentUrl();

                $path = parse_url($currentUrl, PHP_URL_PATH);

                // pisahkan berdasarkan "/"
                $parts = explode("/", trim($path, "/"));

                // ambil bagian ke-2 dan ke-3
                $result = $parts[1] . "/" . $parts[2];
// 
                // dd($currentUrl);

                $action = 'index';
                // if (str_contains($currentUrl, '/singles/')) {
                //     $action = 'singles';
                // }

                return $this->backendUrl->getUrl($result .'/' . $action .'/set_id/'.$setId);
            } else {
                $this->messageManager->addError(__('Please select data to delete.'));
            }
        }
        // dd($this->customTableType);

        // DELETE SETS
        if ($this->customTableType == "sets") {
            // $setId = null;
            // dd($refererUrl);
            // if ($refererUrl) {
            //     if (preg_match('/'.$entityCode.'\/(\d+)/', $refererUrl, $matches)) {
            //         $entity = $matches[1];
            //     }
            // }
            // dd($this->mainAttrVal);
            
            $count = 0;
            $entity = null;

            $id = $this->request->getParam('id') ?: null;
            $selectedIds = $this->request->getParam('selected') ?: null;
            $excluded = $this->request->getParam('excluded') ?: null;
            
            if ($id || $selectedIds || !$selectedIds && $excluded == "false") {
                try {
                    $this->initCustomTableModel();

                    $collection = $this->customTableModel->getCollection();
                    // dd($collection);
                    if ($id) {
                        $collection->addFieldToFilter('entity_id', $id);
                    } elseif ($selectedIds) {
                        $collection->addFieldToFilter('entity_id', ['in' => $selectedIds]);
                    } elseif ($excluded == "false" && $this->mainAttrVal) {
                        $collection->addFieldToFilter($this->mainAttrCode, $this->mainAttrVal);
                    } else {
                        $this->messageManager->addError(__('Please select data to delete.'));
                        return $refererUrl;
                    }

                    if ($collection->getSize()) {
                        foreach($collection as $model) {

                            $model->delete();

                            $count++;
                        }
                    }
                    
                    $this->messageManager->addSuccess(__('A total of %1 data have been deleted.', $count));
                } catch (\Exception $e) {
                    $this->messageManager->addError(__($e->getMessage()));
                }

                $currentUrl = $this->getCurrentUrl();

                $path = parse_url($currentUrl, PHP_URL_PATH);

                // pisahkan berdasarkan "/"
                $parts = explode("/", trim($path, "/"));

                // ambil bagian ke-2 dan ke-3
                $result = $parts[1] . "/" . $parts[2];
// 
                // dd($currentUrl);

                $action = 'index';
                // if (str_contains($currentUrl, '/singles/')) {
                //     $action = 'singles';
                // }

                return $this->backendUrl->getUrl($result .'/' . $action .'/' . $this->mainAttrCode .'/'.$this->mainAttrVal);
            } else {
                $this->messageManager->addError(__('Please select data to delete.'));
            }
        }

        if ($this->customTableType == "sealedproducts") {
            // $setId = null;
            // dd($refererUrl);
            // if ($refererUrl) {
            //     if (preg_match('/'.$entityCode.'\/(\d+)/', $refererUrl, $matches)) {
            //         $entity = $matches[1];
            //     }
            // }
            // dd($this->mainAttrVal);

            // dd('ok');
            // dd($this->getCurrentUrl());
            
            $count = 0;
            $entity = null;

            $id = $this->request->getParam('id') ?: null;
            $selectedIds = $this->request->getParam('selected') ?: null;
            $excluded = $this->request->getParam('excluded') ?: null;
            
            if ($id || $selectedIds || !$selectedIds && $excluded == "false") {
                try {
                    $this->initCustomTableModel();

                    $collection = $this->customTableModel->getCollection();
                    // dd($collection);
                    if ($id) {
                        $collection->addFieldToFilter('entity_id', $id);
                    } elseif ($selectedIds) {
                        $collection->addFieldToFilter('entity_id', ['in' => $selectedIds]);
                    } elseif ($excluded == "false" && $this->mainAttrVal) {
                        $collection->addFieldToFilter($this->mainAttrCode, $this->mainAttrVal);
                    } else {
                        $this->messageManager->addError(__('Please select data to delete.'));
                        return $refererUrl;
                    }

                    if ($collection->getSize()) {
                        foreach($collection as $model) {

                            $model->delete();

                            $count++;
                        }
                    }
                    
                    $this->messageManager->addSuccess(__('A total of %1 data have been deleted.', $count));
                } catch (\Exception $e) {
                    $this->messageManager->addError(__($e->getMessage()));
                }

                $currentUrl = $this->getCurrentUrl();

                $path = parse_url($currentUrl, PHP_URL_PATH);

                // pisahkan berdasarkan "/"
                $parts = explode("/", trim($path, "/"));

                // ambil bagian ke-2 dan ke-3
                $result = $parts[1] . "/" . $parts[2];
// 
                // dd($result);

                return $this->backendUrl->getUrl($result .'/index/' . $this->mainAttrCode .'/'.$this->mainAttrVal);
            } else {
                $this->messageManager->addError(__('Please select data to delete.'));
            }
        }

        return $refererUrl;
    }

    // process get page title
    public function processGetPlatformDbTitle() 
    {
        // dd('ok');
        $title = [];

        $this->initCustomTableType();

        $this->initCategories();

        $this->initCustomTableData();

        $currentUrl = $this->getCurrentUrl();

        // dd($currentUrl);

        // dd($this->customTableType);
        if ($this->customTableType == "customattributes") {
            

            
            if ($this->categoriesName) {
                $title[] = $this->categoriesName . ' Attributes';
            } else {
                $title[] = 'Custom Attributes';
            }

            $linkedAttributeValue = $this->request->getParam('attribute_value');
            if ($linkedAttributeValue) {
                $title[] = $linkedAttributeValue;
            }
           

            // dd($customTableData);
        } else {
            if (!str_contains($currentUrl, '/edit/')) {
                if (!$this->customTableType || $this->customTableType == "sets" && !str_contains($currentUrl, '/singles/')) {
                    $title[] = 'Sets/Release Database';
                }
                
                if ($this->customTableType == "sealedproducts") {
                    $title[] = 'Sealed Products Database';
                }
                
                if ($this->customTableType == "singles" || str_contains($currentUrl, '/singles/')) {
                    $title[] = 'Singles Database';
                }
            } else {
                $this->getPlatformDatabaseFormDataProvider();
            }

            if ($this->categoriesName) {
                $title[] = $this->categoriesName;
            }
        }

        //  dd($this->categoriesName);
        if ($this->mainAttrValLabel) {
            $title[] = $this->mainAttrValLabel;
        }
        
        if ($this->setData) {
            $set = $this->setData;
            if (isset($set[$this->mainSetsAttrCode]) && $set[$this->mainSetsAttrCode]) {
                $title[] = $set[$this->mainSetsAttrCode];
            }
        }
        
        // dd($title);

        // $this->getPlatformDatabaseFormDataProvider();
        if ($this->customTableType == "customattributes") {
            $attrCode = $this->request->getParam('attribute_code');
            if ($attrCode) {
                $title[] = $attrCode;
            }
        }

        return implode(" | ", $title);
    }

    // CUSTOM TABLE MODEL
    public function getCustomTableModel($mainAttrCode = null, $customTableType = null) 
    {
        // dd($customTableType);
        // dd($mainAttrCode);
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->actionToysSetsFactory = $objectManager->create('\TCGCollective\ProductAttributesLink\Model\ActionToySetsFactory');
        $this->actionToysSinglesFactory = $objectManager->create('\TCGCollective\ProductAttributesLink\Model\ActionToySinglesFactory');

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

        $this->dollsSetsFactory = $objectManager->create('\TCGCollective\ProductAttributesLink\Model\DollsSetsFactory');
        $this->dollsSinglesFactory = $objectManager->create('\TCGCollective\ProductAttributesLink\Model\DollsSinglesFactory');

        $this->cardGameSinglesFactory = $objectManager->create('\TCGCollective\ProductAttributesLink\Model\CardGamesSetsCardFactory');
        $this->cardGameSealedProductsFactory = $objectManager->create('\TCGCollective\ProductAttributesLink\Model\CardGamesSealedProductsFactory');

        $this->livingCardGameSetsFactory = $objectManager->create('\TCGCollective\ProductAttributesLink\Model\LivingCardGameSetsFactory');
        $this->livingCardGameSealedProductsFactory = $objectManager->create('\TCGCollective\ProductAttributesLink\Model\LivingCardGameSealedProductsFactory');
        $this->livingCardGameSinglesFactory = $objectManager->create('\TCGCollective\ProductAttributesLink\Model\LivingCardGameSinglesFactory');
        
        $this->miniatureGameSetsFactory = $objectManager->create('\TCGCollective\ProductAttributesLink\Model\MiniatureGameSetsFactory');
        $this->miniatureGameSinglesFactory = $objectManager->create('\TCGCollective\ProductAttributesLink\Model\MiniatureGameSinglesFactory');

        $this->gamesWorkshopSetsFactory = $objectManager->create('\TCGCollective\ProductAttributesLink\Model\GamesWorkshopSetsFactory');
        $this->gamesWorkshopSinglesFactory = $objectManager->create('\TCGCollective\ProductAttributesLink\Model\GamesWorkshopSinglesFactory');

        $this->bricksSetsFactory = $objectManager->create('\TCGCollective\ProductAttributesLink\Model\LegoSetsFactory');
        $this->bricksSinglesFactory = $objectManager->create('\TCGCollective\ProductAttributesLink\Model\LegoSinglesFactory');

        $this->diecastSetsFactory = $objectManager->create('\TCGCollective\ProductAttributesLink\Model\DiecastSetsFactory');
        $this->diecastSinglesFactory = $objectManager->create('\TCGCollective\ProductAttributesLink\Model\DiecastSinglesFactory');

        $this->modelKitsSetsFactory = $objectManager->create('\TCGCollective\ProductAttributesLink\Model\ModelKitsSetsFactory');
        $this->modelKitsSinglesFactory = $objectManager->create('\TCGCollective\ProductAttributesLink\Model\ModelKitsSinglesFactory');

        $this->sportCardsSetsFactory = $objectManager->create('\TCGCollective\ProductAttributesLink\Model\SportCardsSetsFactory');
        $this->sportCardsSealedProductsFactory = $objectManager->create('\TCGCollective\ProductAttributesLink\Model\SportCardsSealedProductsFactory');
        $this->sportCardsSinglesFactory = $objectManager->create('\TCGCollective\ProductAttributesLink\Model\SportCardsSetsCardFactory');

        $this->nonSportCardsSetsFactory = $objectManager->create('\TCGCollective\ProductAttributesLink\Model\NonSportCardsSetsFactory');
        $this->nonSportCardsSealedProductsFactory = $objectManager->create('\TCGCollective\ProductAttributesLink\Model\NonSportCardsSealedProductsFactory');
        $this->nonSportCardsSinglesFactory = $objectManager->create('\TCGCollective\ProductAttributesLink\Model\NonSportCardsSetsCardFactory');


        $this->railwaySetsFactory = $objectManager->create('\TCGCollective\ProductAttributesLink\Model\RailwaySetsFactory');
        $this->railwaySinglesFactory = $objectManager->create('\TCGCollective\ProductAttributesLink\Model\RailwaySinglesFactory');

        $this->attributeOptionCollectionFactory = $objectManager->create('\Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory');

        // dd($mainAttrCode)
        // dd($customTableType);
        

        if ($customTableType == "customattributes") {
            $refererUrl = $this->redirect->getRefererUrl();
            if (!str_contains($refererUrl, 'customattributes')) {
            //     $this->customAttributesFactory = $objectManager->create('\Magento\Eav\Model\Entity\Attribute\OptionFactory');
            //     // dd($this->customAttributesFactory);
            //     return $this->customAttributesFactory->create();
                return $this->attributeOptionCollectionFactory->create();
            } else {
                // $this->customAttributesFactory = $objectManager->create('\TCGCollective\ProductAttributesLink\Model\CustomAttributesFactory');
                // return $this->customAttributesFactory->create();
                if ($this->mainCategoriesConfigData) {
                    $customAttributesTableName = $this->mainCategoriesConfigData['customAttributesTableName'] ?? null;
                    // dd($customAttributesTableName);
                    if ($customAttributesTableName) {
                        return $this->mainCategoriesConfigProvider->getCustomTableCollection($customAttributesTableName);
                    }
                }

                return null;
            }
        }
        
        if ($mainAttrCode == "toys_brand") {
            if ($this->customTableCategories == 'actiontoys') {
                if ($customTableType == "sets") {
                    return $this->actionToysSetsFactory->create();
                }

                if ($customTableType == "singles") {
                    return $this->actionToysSinglesFactory->create();
                }
            }

            if ($this->customTableCategories == 'actiontoysmodelkits') {
                if ($customTableType == "sets") {
                    return $this->actionToysModelKitsSetsFactory->create();
                }

                if ($customTableType == "singles") {
                    return $this->actionToysModelKitsSinglesFactory->create();
                }
            }

            if ($this->customTableCategories == 'animemodelkits') {
                if ($customTableType == "sets") {
                    return $this->animeModelKitsSetsFactory->create();
                }

                if ($customTableType == "singles") {
                    return $this->animeModelKitsSinglesFactory->create();
                }
            }

            if ($this->customTableCategories == 'gundam') {
                if ($customTableType == "sets") {
                    return $this->gundamSetsFactory->create();
                }

                if ($customTableType == "singles") {
                    return $this->gundamSinglesFactory->create();
                }
            }

            if ($this->customTableCategories == 'fungoods') {
                if ($customTableType == "sets") {
                    return $this->fungoodsSetsFactory->create();
                }

                if ($customTableType == "singles") {
                    return $this->fungoodsSinglesFactory->create();
                }
            }

            if ($this->customTableCategories == 'anime') {
                if ($customTableType == "sets") {
                    return $this->animeToysSetsFactory->create();
                }

                if ($customTableType == "singles") {
                    return $this->animeToysSinglesFactory->create();
                }
            }

            if ($this->customTableCategories == 'dolls') {
                if ($customTableType == "sets") {
                    return $this->dollsSetsFactory->create();
                }

                if ($customTableType == "singles") {
                    return $this->dollsSinglesFactory->create();
                }
            }
        }

        if ($mainAttrCode == "card_game") {
            if ($customTableType == "sets") {
                return $this->cardGameSetsFactory->create();
            }

            if ($customTableType == "sealedproducts") {
                return $this->cardGameSealedProductsFactory->create();
            }

            if ($customTableType == "singles") {
                return $this->cardGameSinglesFactory->create();
            }
        }

        if ($mainAttrCode == "living_card_game") {
            if ($customTableType == "sets") {
                return $this->livingCardGameSetsFactory->create();
            }

            if ($customTableType == "sealedproducts") {
                return $this->livingCardGameSealedProductsFactory->create();
            }

            if ($customTableType == "singles") {
                return $this->livingCardGameSinglesFactory->create();
            }
        }

        if ($mainAttrCode == "miniature_game") {
            if ($customTableType == "sets") {
                return $this->miniatureGameSetsFactory->create();
            }

            if ($customTableType == "singles") {
                return $this->miniatureGameSinglesFactory->create();
            }
        }

        if ($mainAttrCode == "gw_miniature_game") {
            if ($customTableType == "sets") {
                return $this->gamesWorkshopSetsFactory->create();
            }

            if ($customTableType == "singles") {
                return $this->gamesWorkshopSinglesFactory->create();
            }
        }

        // dd($customTableType);

        if ($mainAttrCode == "bricks_brand") {
            if ($customTableType == "sets") {
                return $this->bricksSetsFactory->create();
            }

            if ($customTableType == "singles") {
                return $this->bricksSinglesFactory->create();
            }
        }

        if ($mainAttrCode == "sc_categories") {
            if ($customTableType == "sets") {
                return $this->sportCardsSetsFactory->create();
            }

            if ($customTableType == "sealedproducts") {
                return $this->sportCardsSealedProductsFactory->create();
            }

            

            if ($customTableType == "singles") {
                return $this->sportCardsSinglesFactory->create();
            }
        }

        if ($mainAttrCode == "nsc_categories") {
            if ($customTableType == "sets") {
                return $this->nonSportCardsSetsFactory->create();
            }

            if ($customTableType == "sealedproducts") {
                return $this->nonSportCardsSealedProductsFactory->create();
            }

            

            if ($customTableType == "singles") {
                return $this->nonSportCardsSinglesFactory->create();
            }
        }

        if ($mainAttrCode == "diecast_brand") {
            if ($customTableType == "sets") {
                return $this->diecastSetsFactory->create();
            }

            if ($customTableType == "singles") {
                return $this->diecastSinglesFactory->create();
            }
        }

        if ($mainAttrCode == "model_kits_brand") {
            if ($customTableType == "sets") {
                return $this->modelKitsSetsFactory->create();
            }

            if ($customTableType == "singles") {
                return $this->modelKitsSinglesFactory->create();
            }
        }

        if ($mainAttrCode == "railway_brand") {
            if ($customTableType == "sets") {
                return $this->railwaySetsFactory->create();
            }

            if ($customTableType == "singles") {
                return $this->railwaySinglesFactory->create();
            }
        }
        
        return null;
    }

    // PLATFORM DATABASE | SETS/SINGLES/SEALED PRODUCTS FORM DATAPROVIDER
    public function getPlatformDatabaseFormDataProvider() 
    {
        $this->initCustomTableData();

        $result = [
            'success' => false,
            'name' => null,
            'file' => null,
            'refererUrl' => null
        ];
        
        $this->initCustomTableModel();
        
        // Get sets data
        $model = $this->customTableModel;
        if (!$model) {
            dd('Model not exist');
        }

        $connection = $this->customTableModel->getResource()->getConnection();

        // dd($this->customTableType);
        
        if ($this->customTableType == "customattributes") {
            $collection = $model;
        } else {
            $collection = $model->getCollection();
        }
        // dd($collection);

        $mainAttrColumn = $this->mainAttrCode;

        $entityId = $this->request->getParam('entity_id');
        if ($entityId) {
            $collection->addFieldToFilter('entity_id', $entityId);
        }

        if ($this->customTableType == "customattributes") {
            if (!$collection->getSize()) {
                return null;
            }

            $items = $collection->getData();

            // dd($items);
            $items = array_column($items, null, 'entity_id');

            return $items;
        }

        // Get sets data
        if ($this->customTableType == "singles") {
            $setsTableName = $this->customTableModel->getResource()->getTable($this->setsTableName);

            // Ambil daftar kolom dari DB
            $setsTableColumns = array_keys($connection->describeTable($setsTableName));

            // dd($setsTableColumns);
            // dd($this->fieldsToDownload);

            // $fieldToSelectFromSets = array_intersect($this->fieldsToDownload, $setsTableColumns);
            
            $fieldToSelectFromSets2 = array_filter(
                $setsTableColumns, fn($col) => !in_array($col, ['entity_id', 'images','name','supplier_sku'])
            );

            // dd($fieldToSelectFromSets2);

            $collection->getSelect()->join(
                ['sets' => $this->setsTableName],
                'sets.entity_id = main_table.set_id',
                $fieldToSelectFromSets2
            )->group('main_table.entity_id');
            
            $mainAttrColumn = 'sets.' . $this->mainAttrCode;
        }

        // dd($this->mainAttrVal);

        // Filter by main attribute code
        $collection->addFieldToFilter($mainAttrColumn, $this->mainAttrVal);

        $this->currentCustomTableData = $collection->getFirstItem()->getData();

        // dd($this->currentCustomTableData);

        $this->getAttributeSetFieldsToDownload();

        if ($this->customTableType == "singles") {

            $fieldToSelectFromSets = array_intersect($this->fieldsToDownload, $setsTableColumns);
        
            $fieldToSelectFromSets = array_filter(
                $fieldToSelectFromSets, fn($col) => !in_array($col, ['entity_id', 'images','name','supplier_sku'])
            );
            

            $fieldToSelectFromSets = array_map(fn($col) => "sets.$col", $fieldToSelectFromSets);
            // dd($fieldToSelectFromSets);
            
            
            $collection->getSelect()->columns($fieldToSelectFromSets);
        }

        $tableName = $this->customTableModel->getResource()->getMainTable();

        // Ambil daftar kolom dari DB
        $tableColumns = array_keys($connection->describeTable($tableName));
        $validFields = array_intersect($this->fieldsToDownload, $tableColumns);

        // dd($validFields);

        $collection->addFieldToSelect($validFields);

        $id = $this->request->getParam('entity_id');
        $collection->addFieldToFilter('entity_id', $id);
        
        if (!$collection->getSize()) {
            return null;
        }

        $items = $collection->getData();

        // dd($items);

        // Get only image basename
        // Change master attribute value to label
        $items = array_map(
            function ($r) {
                // Ubah nilai 'images' menjadi hanya nama file
                // if (!empty($r['images'])) {
                //     $imageName = basename($r['images']);
                //     $r['images'] = $imageName;
                // } else {
                //     $r['images'] = '';
                // }

                // Lanjutkan logika existing
                return array_replace(
                    array_fill_keys($this->fieldsToDownload, null),
                    $r,
                    []
                    // [$this->mainAttrCode => $this->mainAttrValLabel]
                );
            },
            $items
        );

        // dd($items[0]);

        // get first data
        if (!$this->registry->registry('current_edit_singles')) {
            $this->registry->register('current_edit_singles', $items[0]);
        }

        // Set entity_id as key
        $items = array_column($items, null, 'entity_id');

// 
        // die('<pre>'.print_r($items,true));

        return $items;
    }

    // CUSTOM TABLE LISTING DATAPROVIDER
    public function getPlatformDatabaseDataProvider() 
    {
        // dd('ok');
        $this->initCustomTableData();

        $result = [
            'success' => false,
            'name' => null,
            'file' => null,
            'refererUrl' => null
        ];
        
        $this->initCustomTableModel();
        
        // Get sets data
        $model = $this->customTableModel;

        $connection = $this->customTableModel->getResource()->getConnection();

        // dd($this->customTableType);
        
        $collection = $model->getCollection();
        // dd($collection);

        $mainAttrColumn = $this->mainAttrCode;

        // Get sets data
        if ($this->customTableType == "singles") {
            $setsTableName = $this->customTableModel->getResource()->getTable($this->setsTableName);

            // Ambil daftar kolom dari DB
            $setsTableColumns = array_keys($connection->describeTable($setsTableName));

            // dd($setsTableColumns);
            // dd($this->fieldsToDownload);

            // $fieldToSelectFromSets = array_intersect($this->fieldsToDownload, $setsTableColumns);
            
            $fieldToSelectFromSets2 = array_filter(
                $setsTableColumns, fn($col) => !in_array($col, ['entity_id', 'images','name','supplier_sku'])
            );

            $collection->getSelect()->join(
                ['sets' => $this->setsTableName],
                'sets.entity_id = main_table.set_id',
                $fieldToSelectFromSets2
            )->group('main_table.entity_id');
            
            $mainAttrColumn = 'sets.' . $this->mainAttrCode;
        }

        // Filter by main attribute code
        $collection->addFieldToFilter($mainAttrColumn, $this->mainAttrVal);

        $this->currentCustomTableData = $collection->getFirstItem()->getData();

        // dd($this->currentCustomTableData);

        $this->getAttributeSetFieldsToDownload();

        if ($this->customTableType == "singles") {

            $fieldToSelectFromSets = array_intersect($this->fieldsToDownload, $setsTableColumns);
        
            $fieldToSelectFromSets = array_filter(
                $fieldToSelectFromSets, fn($col) => !in_array($col, ['entity_id', 'images','name','supplier_sku'])
            );
            

            $fieldToSelectFromSets = array_map(fn($col) => "sets.$col", $fieldToSelectFromSets);
            // dd($fieldToSelectFromSets);
            
            
            $collection->getSelect()->columns($fieldToSelectFromSets);
        }

        $tableName = $this->customTableModel->getResource()->getMainTable();

        // Ambil daftar kolom dari DB
        $tableColumns = array_keys($connection->describeTable($tableName));
        $validFields = array_intersect($this->fieldsToDownload, $tableColumns);

        // dd($validFields);

        $collection->addFieldToSelect($validFields);
        
        if (!$collection->getSize()) {
            return null;
        }

        $items = $collection->getData();

        // Get only image basename
        // Change master attribute value to label
        $items = array_map(
            function ($r) {
                // Ubah nilai 'images' menjadi hanya nama file
                // if (!empty($r['images'])) {
                //     $imageName = basename($r['images']);
                //     $r['images'] = $imageName;
                // } else {
                //     $r['images'] = '';
                // }

                // Lanjutkan logika existing
                return array_replace(
                    array_fill_keys($this->fieldsToDownload, null),
                    $r,
                    []
                    // [$this->mainAttrCode => $this->mainAttrValLabel]
                );
            },
            $items
        );

        // get first data
        if (!$this->registry->registry('current_edit_singles')) {
            $this->registry->register('current_edit_singles', $items[0]);
        }

        // Set entity_id as key
        $items = array_column($items, null, 'entity_id');

// 
        // die('<pre>'.print_r($items,true));

        return $items;
    }

    // CUSTOM TABLE TYPE
    public function initCustomTableType() 
    {
        $currentUrl = $this->getCurrentUrl();

        
        if (str_contains($currentUrl,'sampleCsv')) {
            $currentUrl = $this->request->getParam('custom_table_type');
        }

        // dd($currentUrl);
        if (str_contains($currentUrl, 'platformdatabase/')) {
            $currentUrl = $this->redirect->getRefererUrl();
        }

        // dd($currentUrl);

        $customTableType = null;
        if (str_contains($currentUrl, 'attributes/')) {
            $this->customTableType = 'customattributes';
        } elseif (str_contains($currentUrl, 'sets')) {
            $this->customTableType = 'sets';
        } elseif (str_contains($currentUrl, 'sealedproduct')) {
            $this->customTableType = 'sealedproducts';
        } elseif (str_contains($currentUrl, 'singles') || str_contains($currentUrl, 'cardgamescard')) {
            $this->customTableType = 'singles';
        }

        // dd($this->customTableType);

        return $this->customTableType;
    }

    public function getCurrentUrl() 
    {
        $isAjax = $this->request->getParam('isAjax');
        if ($isAjax) {
            return $this->redirect->getRefererUrl();
        }

        if (str_contains($this->urlBuilder->getCurrentUrl(), 'download')) {
            return $this->redirect->getRefererUrl();
        }

        if (str_contains($this->urlBuilder->getCurrentUrl(), 'platformDatabase/save')) {
            return $this->redirect->getRefererUrl();
        }

        if (str_contains($this->urlBuilder->getCurrentUrl(), 'platformDatabase/massDelete')) {
            return $this->redirect->getRefererUrl();
        }

        return $this->urlBuilder->getCurrentUrl();
    }
    
    public function initMainAttrVal($rowData = null) 
    {
        $currentUrl = $this->getCurrentUrl();
        if (str_contains($currentUrl,'customattributes') || str_contains($currentUrl,'attributes/')) {
            // $this->setId = $rowData['entity_id'] ?? null;
            // dd($this->singlesId);
            // $this->setData = $rowData;
            // dd($this->setData);
            // dd($this->request->getParam('categories'));
            $this->mainAttrVal = $this->request->getParam('id');

            return;
        }

        if (str_contains($currentUrl,'sampleCsv')) {
            // $this->setId = $rowData['entity_id'] ?? null;
            // dd($this->singlesId);
            // $this->setData = $rowData;
            // dd($this->setData);
            // dd($this->request->getParam('categories'));
            $this->mainAttrVal = $this->request->getParam('categories');

            return;
        }

        if ($rowData) {
            $this->setId = $rowData['entity_id'] ?? null;
            // dd($this->singlesId);
            $this->setData = $rowData;
            // dd($this->setData);
            $this->mainAttrVal = $this->setData[$this->mainAttrCode] ?? null;

            return;
        }
        
        // dd($currentUrl);
        if ($this->customTableType == "sets" || $this->customTableType == "sealedproducts") {
            if (preg_match('/'.$this->mainAttrCode.'\/(\d+)/', $currentUrl, $matches)) {
                $this->mainAttrVal = $matches[1];
            }

            

            if ($this->customTableType == "sets") {
                if (preg_match('/entity_id\/(\d+)/', $currentUrl, $matches)) {
                    $this->setId = $matches[1];
                    // dd($this->singlesId);
                    $this->setData = $this->getSetDataById();
                    // dd($this->setData);
                    $this->mainAttrVal = $this->setData[$this->mainAttrCode] ?? null;

                    // $this->mainProductTypeAttrVal = '';
                }
            }

            if ($this->customTableType == "sealedproducts") {
                if (preg_match('/entity_id\/(\d+)/', $currentUrl, $matches)) {
                    $this->setId = $matches[1];
                    // dd($this->singlesId);
                    $this->setData = $this->getSetDataBySealedProductsId();
                    // dd($this->setData);
                    $this->mainAttrVal = $this->setData[$this->mainAttrCode] ?? null;

                    // $this->mainProductTypeAttrVal = '';
                }
            }

            // dd($this->mainAttrVal);

            if ($this->customTableType == "sealedproducts") {
                $this->mainProductTypeAttrVal = 'Sealed Products';
            }
        }

        if ($this->customTableType == "singles") {
            // dd($currentUrl);
            if (preg_match('/set_id\/(\d+)/', $currentUrl, $matches)) {
                $this->setId = $matches[1];
                $this->setData = $this->getSetDataById();
                $this->mainAttrVal = $this->setData[$this->mainAttrCode] ?? null;

                $this->mainProductTypeAttrVal = 'Singles';
            }

            if (preg_match('/entity_id\/(\d+)/', $currentUrl, $matches)) {
                $this->singlesId = $matches[1];
                // dd($this->singlesId);
                $this->setData = $this->getSetDataBySinglesId();
                // dd($this->setData);
                $this->setId = $this->setData['entity_id'] ?? null;
                $this->mainAttrVal = $this->setData[$this->mainAttrCode] ?? null;

                $this->mainProductTypeAttrVal = 'Singles';
            }
        }
        
        return;
    }

    public function initCategories($rowData = null) 
    {
        $currentUrl = $this->getCurrentUrl();

        if (str_contains($currentUrl, 'attributes/')) {
        //     $urlKey = $this->getSegmentFromUrl($currentUrl, 2);
        //     if ($this->getCurrentArea() == "marketplace") {
        //         $urlKey = $this->getSegmentFromUrl($currentUrl, 3);
        //     }
        //     dd($urlKey);
        //     // dd($this->customTableCategories);
        //     return;
            $refererUrl = $this->redirect->getRefererUrl();
            // dd($refererUrl);
            // if ($refererUrl) {
            //     if (preg_match('/entity_id\/(\d+)/', $refererUrl, $matches)) {
            //         $entityId = $matches[1]; // The first capturing group contains the set_id
            //     }
            // dd($currentUrl);
                $path = parse_url($currentUrl, PHP_URL_PATH);

                // Pecah URL menjadi array
                $segments = explode('/', trim($path, '/'));

                // dd($segments);

                // Ambil segmen ke-3 (0-based: 0=backoffice, 1=productattributeslink, 2=cardgameattributes)
                $entity = str_replace("attributes", "", $segments[2]);
                // dd($entity);
                $this->customTableCategories = $entity;
                return;
            // }
        }

        if (str_contains($currentUrl,'customattributes')) {
            $this->customTableCategories = $this->request->getParam('categories');
            // dd($this->customTableCategories);
            return;
        }

        if (str_contains($currentUrl,'sampleCsv') || $rowData) {
            $this->customTableCategories = $this->request->getParam('entity');
            return;
        }

        $urlKey = $this->getSegmentFromUrl($currentUrl, 2);
        if ($this->getCurrentArea() == "marketplace") {
            $urlKey = $this->getSegmentFromUrl($currentUrl, 3);
        }

        $this->customTableCategories = $urlKey ? str_replace(['sets', 'singles','sealedproducts'], '', $urlKey) : null;
        // dd($this->customTableCategories);
    }
    
    public function getSetDataBySinglesId() 
    {
        // dd($this->mainAttrCode);
        $model = $this->getCustomTableModel($this->mainAttrCode, 'sets');
        if ($model) {
            // dd($model);
            // dd($this->setId);
            $collection = $model->getCollection();
            // if ($this->customTableType == "sets") {
            $collection->getSelect()->join(
                ['singles' => $this->singlesTableName],
                'main_table.entity_id = singles.set_id',
                []
            )->group('main_table.entity_id')->where('singles.entity_id = ?', $this->singlesId);

            // dd($collection->getSelect()->__toString());

        //     $mainAttrColumn = $this->mainAttrCode;
        // }
            if ($collection->getSize()) {
                $this->currentCustomTableData = $collection->getFirstItem()->getData();
                // dd($collection->getFirstItem()->getData());
                return $collection->getFirstItem()->getData();
            }
        }

        return null;
    }

    public function getSetDataById() 
    {
        // dd($this->mainAttrCode);
        $model = $this->getCustomTableModel($this->mainAttrCode, 'sets');
        if ($model) {
            // dd($model);
            // dd($this->setId);
            $collection = $model->getCollection()->addFieldToFilter('entity_id', $this->setId);
            if ($collection->getSize()) {
                return $collection->getFirstItem()->getData();
            }
        }

        return null;
    }

    public function getSetDataBySealedProductsId() 
    {
        $model = $this->getCustomTableModel($this->mainAttrCode, 'sealedproducts');
        if ($model) {
            // dd($model);
            // dd($this->setId);
            $collection = $model->getCollection()->addFieldToFilter('entity_id', $this->setId);
            if ($collection->getSize()) {
                return $collection->getFirstItem()->getData();
            }
        }

        return null;
    }

    public function getAllCustomTableData() 
    {
        return $this->getCurrentCustomTableData(null, true);
    }

    public function getCustomTableTypeAttrCodes() 
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

        return $fields;

        // return [
        //     'card_product_type',
        //     'action_toys_categories'
        // ];
    }

    public function getHigherPriorityAttributeSetMapping($attrCode, $attrVal, $mainAttrCode, $mainAttrVal, $groupName = null) 
    {
        $mapping = null;

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->attributeSetMappingCollectionFactory = $objectManager->create('\TCGCollective\ProductAttributesLink\Model\ResourceModel\ProductAttributesLink\CollectionFactory');

        $collection = $this->attributeSetMappingCollectionFactory->create()
        ->addFieldToFilter('attribute_code', ['eq' => $attrCode])
        ->addFieldToFilter('attribute_option_id', ['eq' => $attrVal])
        ->addFieldToFilter('mapping_type', 'attribute_set');
        if ($collection->getSize()) {
            // dd($collection->toArray());
            $mapping = $collection->getFirstItem();
            $groupName = $mapping->getGroupName();
        }
        
        $collection = $this->attributeSetMappingCollectionFactory->create();
        // ->addFieldToFilter('attribute_code', ['eq' => $attrCode])
        // ->addFieldToFilter('attribute_option_id', ['eq' => $attrVal])
        // ->addFieldToFilter('mapping_type', 'attribute_set');
        if ($mapping) {
            // $collection->addFieldToFilter('group_name', ['like' => $groupName . ' | %']);
        // }
        // if ($collection->getSize()) {
        //     // dd($collection->toArray());
        //     $mapping = $collection->getFirstItem();

            // dd($mapping);

            $collection = $this->attributeSetMappingCollectionFactory->create()
            ->addFieldToFilter('mapping_type', 'attribute_set')
            ->addFieldToFilter('group_name', ['like' => $mapping->getGroupName() . ' | %'])
            ->addFieldToFilter('priority', ['gt' => $mapping->getPriority() . '%'])
            ->setPageSize(1);
            // dd($collection->toArray());
            if ($collection->getSize()) {
                return true;
            }
        }

        return false;
    }

    public function getProductCreationCustomTableType() 
    {
        $attrCode = $this->request->getParam('code');
        $groupName = $this->request->getParam('groupName');

        $attrVal = $this->request->getParam('val');
        $filters = $this->request->getParam('filters');

        $mainAttrCode = $this->request->getParam('mainAttrCode');
        $mainAttrVal = $this->request->getParam('mainAttrVal');

        // $productData = $this->request->getParam('product');

        // dd($attrCode);

        if (!$mainAttrCode && !$mainAttrVal) {
            return null;
        }

        $valLabel = $this->helper->getOptionLabelById($attrCode, $attrVal);
        

        $customTableData = '';

        $customTableData = $this->getCustomTableDataByValue('mainAttrCode', $mainAttrCode);
        // dd($customTableData);
        if (!$customTableData) {
            return null;
        }

        $checkSetsExist = $this->checkSetsDatabaseExist($customTableData, $mainAttrCode, $mainAttrVal, $filters);
        
        // dd($checkSetsExist);

        $result['is_custom_table_exist'] = $checkSetsExist;

        if (!$checkSetsExist) {
            return $result;
        }
        
        // dd($this->getHigherPriorityAttributeSetMapping($attrCode, $attrVal, $mainAttrCode, $mainAttrVal));

        // Check if there is higher priority mapping
        if ($this->getHigherPriorityAttributeSetMapping($attrCode, $attrVal, $mainAttrCode, $mainAttrVal, $groupName)) {
            $result['next_priority'] = true;
            return $result;
        } else {
            if ($valLabel) {
                $databaseVal = [
                    'Singles',
                    'Sealed Products',
                    'Graded',
                    'Pre Orders'
                ];

                if (!in_array($valLabel, $databaseVal)) {
                    return null;
                }
            }
        }

        
        
                // dd($mainAttrValLabel);    
        // if ($valLabel) {
            // if (str_contains($valLabel, "Case") || str_contains($valLabel, "Boxes")) {
            $result['is_having_release'] = false;
        
            // }
        if ($valLabel && str_contains($valLabel, "Singles") || str_contains($valLabel, "Graded")) {
            $result['is_singles'] = true;
        }

        if ($valLabel && str_contains($valLabel, "Pre Orders") || str_contains($valLabel, "Sealed") || str_contains($valLabel, "Box")) {
            $result['is_sealedproducts'] = true;
        }

        $mainEntityWithRelease = ['Magic: The Gathering','Secret Lair','Baseball','Football'];

        $mainAttrValLabel = $this->helper->getOptionLabelById($mainAttrCode, $mainAttrVal);
        if (in_array($mainAttrValLabel, $mainEntityWithRelease)) {
            $result['is_having_release'] = true;
        }
        //     $result['is_custom_table_exist'] = $this->checkCustomTableExist($entityCode, $entityVal);
        // }
        $result['sets_selection_scope'] = $customTableData['productCreationDatabaseScope'] ?? null;

        return $result;
    }

    public function checkSetsDatabaseExist($customTableData, $mainAttrCode, $mainAttrVal, $filters = null) 
    {
        $tableName = $customTableData['setsTableName'] ?? null;
        // dd($tableName);
        if (!$tableName) {
            return false;
        }

        /** @var \Magento\Framework\App\ResourceConnection $resource */
        $connection = $this->resource->getConnection();
        $resourceTableName  = $this->resource->getTableName($tableName);

        $columns = array_keys($connection->describeTable($resourceTableName));

        // dd($columns);

        $select = $connection->select()
            ->from($resourceTableName, ['count' => new \Zend_Db_Expr('COUNT(*)')])
            ->where("$mainAttrCode = ?", $mainAttrVal);
            // dd($filters);
        if ($filters) {
            foreach($filters as $col => $val) {
                if (!in_array($col, $columns)) {
                    continue;
                }
                $select->where("$col = ?", $val);
            }
        }
        $count = (int) $connection->fetchOne($select);
        // dd($select->__toString());
        if (!$count) {
            return false;
        }

        return true;
    }

    public function getCustomTableDataByProductCreationCustomTableAttrCode($code) 
    {
        $customTableData = $this->getCustomTableData();
        if (!$customTableData) {
            return null;
        }

        foreach ($customTableData as $key => $config) {
            if (is_array($config) &&
                ($config['productCreationDatabaseAttrCode'] ?? null) === $code
            ) {
                return $config;
            }
        }

        return null;
    }

    public function getCustomTableDataByValue($code, $value) 
    {
        $customTableData = $this->getCustomTableData();
        if (!$customTableData) {
            return null;
        }

        foreach ($customTableData as $key => $config) {
            if (is_array($config) &&
                ($config[$code] ?? null) === $value
            ) {
                return $config;
            }
        }

        return null;
    }

    public function getCustomTableData() 
    {
        return $this->mainCategoriesConfigProvider->getAll();
    }

    public function getCategoryByEntityKey() 
    {
        $entity = $this->customTableCategories;

        if ($entity == "nonsportcard") {
            $entity = "nonsportcards";
        }

        // dd($this->customTableCategories);

        if ($entity == "cardgame") {
            // $entity = "cardgames";
        }

        // dd($entity);
        
        $categoryId = $this->scopeConfig->getValue(
            'categories/' . $entity . '/' . 'category_id',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        // dd($categoryId);
        
        if ($categoryId) {
            $category = $this->helper->getCategoryById($categoryId);
            if ($category) {
                $this->categoriesName = $category->getName();
                $this->categoryId = $category->getId();
            }
        }

        return [];
    }

    public function getCustomTableDataByKey($key) 
    {
        $config = null;

        $configMap = $this->getCustomTableData();

        // Resolve alias (pakai '@' untuk pointing ke config lain)
        if (isset($configMap[$key]) && is_string($configMap[$key]) && str_starts_with($configMap[$key], '@')) {
            $aliasKey = substr($configMap[$key], 1);
            $config = $configMap[$aliasKey] ?? [];
        } else {
            $config = $configMap[$key] ?? [];
        }
        
        return $config;
    }

    public function getCurrentCustomTableData($rowData = null, $all = false) 
    {
        $this->initCategories($rowData);

        // dd($this->customTableCategories);

        $configMap = $this->getCustomTableData();

        if ($all) {
            return $configMap;
        }

        if ($this->customTableCategories) {
            // Ambil config sesuai kategori
            $category = strtolower($this->customTableCategories);

            // Resolve alias (pakai '@' untuk pointing ke config lain)
            if (isset($configMap[$category]) && is_string($configMap[$category]) && str_starts_with($configMap[$category], '@')) {
                $aliasKey = substr($configMap[$category], 1);
                $config = $configMap[$aliasKey] ?? [];
            } else {
                $config = $configMap[$category] ?? [];
            }

            // Apply config ke properti class
            foreach ($config as $key => $value) {
                $this->$key = $value;
            }
        }

        return [
            'setsTableName' => $this->setsTableName,
            'sealedProductsTableName' => $this->sealedProductsTableName,
            'singlesTableName' => $this->singlesTableName,
            'mainAttrCode' => $this->mainAttrCode,
            'mainProductTypeAttrCode' => $this->mainProductTypeAttrCode,
            'mainReleaseAttrCode' => $this->mainReleaseAttrCode,
            'mainSetsAttrCode' => $this->mainSetsAttrCode,
            'mainSetsNumberAttrCode' => $this->mainSetsNumberAttrCode,
            'mainSinglesAttrCode' => $this->mainSinglesAttrCode,
            'mainSinglesNumberAttrCode' => $this->mainSinglesNumberAttrCode
        ];
    }

    public function getAttributeCodeVal($attrCode) {
        // dd($attrCode);
        if ($this->customTableType == "singles") {
            if ($attrCode == "card_product_type") {
                return 'Singles';
            }

            if ($attrCode == "living_card_product_type") {
                return 'Singles';
            }

            
        }

        if ($this->customTableType == "sealedproducts") {
            if ($attrCode == "card_product_type") {
                return 'Sealed Products';
            }

            if ($attrCode == "living_card_product_type") {
                return 'Sealed Products';
            }
        }

        return null;
    }

    public function initCustomTableModel() 
    {
        // dd($this->mainAttrCode);
        $this->initCustomTableType();

        $this->customTableModel = $this->getCustomTableModel($this->mainAttrCode, $this->customTableType);

        return $this->customTableModel;
    }

    public function getAttributeSetForDownload() 
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->attributeSetRepository = $objectManager->create('Magento\Eav\Api\AttributeSetRepositoryInterface');
        $this->attributeGroup = $objectManager->get('Magento\Eav\Model\Entity\Attribute\Group');
        $this->attribute = $objectManager->get('Magento\Eav\Model\Entity\Attribute');
    }

    

    public function getNextPriorityMappingAttributeCode() 
    {
        if ($this->attributeSetData) {
            // dd($this->attributeSetData);
            $mappingGroupName = $this->attributeSetData['mapping_group_name'] ?? null;
            $priority = $this->attributeSetData['mapping_priority'] ?? null;

            // dd($priority);

            // GET NEXT MAPPING WITH PRIORITY AND LIKE GROUP NAME
            // $collection = $this->attributeSetMappingCollectionFactory->create()
            // ->addFieldToFilter('group_name', ['like' => $mappingGroupName . '%'])
            // ->addFieldToFilter('priority', ['gt' => $priority])
            // ->addFieldToFilter('mapping_type', 'attribute_set');
            
            $collection = $this->getGroupCollection($mappingGroupName)
            ->addFieldToFilter('priority', ['gt' => $priority]);

            $collection->setOrder('priority', 'ASC');

            

            if ($priority == 1) {
                // dd($mappingGroupName);
                // dd($collection->getSize());
            // die('<pre>'.print_r($collection->toArray()['items'],true));
            }

            $this->debugGetNextPriorityMappingAttributeCode[] = [
                'priority' => $priority,
                'group' => $mappingGroupName,
                'data' => $collection->getData()
            ];

            if (!$collection->getSize()) {
                // dd($collection->getSelect()->__toString());
                $this->nextPriorityMappingAttributeCode = null;
                $this->nextPriorityMappingAttributeVal = null;

                return;
            }
                
            $mapping = $collection->getFirstItem();

            // if (
            //     $this->currentCustomTableData 
            //     && isset($this->currentCustomTableData[$mapping->getAttributeCode()])
            // ) {
            if ($priority == 1) {
                // dd($mapping->getData());
                // dd($this->nextPriorityMappingAttributeCode);
                // dd($this->nextPriorityMappingAttributeVal);
            // dd('ok'.$this->currentCustomTableData[$mapping->getAttributeCode()]);
            }
            if ($mapping) {
                
                // dd($this->currentCustomTableData[$mapping->getAttributeCode()]);

                $this->debugGetNextPriorityMappingAttributeCode[] = [
                    'next_priority' => $mapping->getPriority(),
                    'next_attrcode' => $mapping->getAttributeCode()
                ];
                
                $this->nextPriorityMappingAttributeCode = $mapping->getAttributeCode();
                $this->nextPriority = $mapping->getPriority();

                // dd($this->nextPriorityMappingAttributeCode);

                
            }

            if ($priority == 1) {
                // dd($mapping);
                // dd($this->nextPriorityMappingAttributeCode);
                // dd($this->nextPriorityMappingAttributeVal);
            // dd('ok'.$this->currentCustomTableData[$mapping->getAttributeCode()]);
            }

            // $this->nextPriorityMappingAttributeCode = null;
            // $this->nextPriority = null;
            // $this->nextPriorityMappingAttributeVal = null;

            
            
                // }
        }

        // $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/PlatformDbHelper.log');
        // $logger = new \Zend_Log();
        // $logger->addWriter($writer);
        // $logger->info('getNextPriorityMappingAttributeCode | nextPriorityMappingAttributeCode | '.print_r($this->nextPriorityMappingAttributeCode,true));
        // $logger->info('getNextPriorityMappingAttributeCode | nextPriority | '.print_r($this->nextPriority,true));
        // $logger->info('getNextPriorityMappingAttributeCode | $this->currentCustomTableData[$mapping->getAttributeCode()] | '.print_r($this->currentCustomTableData[$mapping->getAttributeCode()],true));
        // $logger->info('getNextPriorityMappingAttributeCode | nextPriorityMappingAttributeVal | '.print_r($this->nextPriorityMappingAttributeVal,true));
        // $logger->info('getNextPriorityMappingAttributeCode | currentCustomTableData | '.print_r($this->currentCustomTableData,true));
    }

    public function getAllowedOptionIds() 
    {
        // dd($this->customTableCategories);
        // dd($this->customTableType);
        if ($this->customTableCategories == "sportcard") {
            $this->customTableCategories = "sportcards";
        }

        if ($this->customTableCategories == "nonsportcard") {
            $this->customTableCategories = "nonsportcards";
        }

        // dd($this->customTableCategories);
        $configValue = $this->scopeConfig->getValue(
            'categories/' . $this->customTableCategories . '/' . $this->customTableType . '_database',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if ($configValue) {
            // dd($configValue);
            return explode(",", $configValue);
        }

        return [];
    }

    public function getNextPriorityMapping($isSample = false) 
    {
        // dd($this->currentCustomTableData);
        // dd($this->nextPriority);
        // dd($this->nextPriorityMappingAttributeVal);
        // dd($this->nextPriorityMappingAttributeCode);
        if ($this->nextPriorityMappingAttributeCode) {
            // if (!$isSample) {
                if (!isset($this->currentCustomTableData[$this->nextPriorityMappingAttributeCode])) {
                    $this->currentCustomTableData[$this->nextPriorityMappingAttributeCode] = $this->getAttributeCodeVal($this->nextPriorityMappingAttributeCode);
                }

                // dd($this->currentCustomTableData);

                if ($this->currentCustomTableData[$this->nextPriorityMappingAttributeCode]) {
                    $this->nextPriorityMappingAttributeVal = $this->helper->getOptionId(
                        $this->nextPriorityMappingAttributeCode,
                        $this->currentCustomTableData[$this->nextPriorityMappingAttributeCode]
                    );

                    // return;
                }
            // }
            // dd($this->nextPriorityMappingAttributeVal);

            // dd($this->currentCustomTableData[$this->nextPriorityMappingAttributeCode]);

            $this->debugGetNextPriorityMappingAttributeCode[] = [
                'next_val' => $this->nextPriorityMappingAttributeVal
            ];


            $mappingGroupName = $this->attributeSetData['mapping_group_name'] ?? null;
            $priority = $this->attributeSetData['mapping_priority'] ?? null;

            if ($this->nextPriority == 2) {
                // dd($this->nextPriorityMappingAttributeVal);
                // dd($priority);
            }

            $collection = $this->attributeSetMappingCollectionFactory->create()
            ->addFieldToFilter('attribute_code', $this->nextPriorityMappingAttributeCode);

            if ($this->nextPriorityMappingAttributeVal) {
                $collection->addFieldToFilter('attribute_option_id', $this->nextPriorityMappingAttributeVal);
            } else {
                // dd($this->nextPriorityMappingAttributeCode);
                // dd($this->getAllowedOptionIds());
                $collection->addFieldToFilter('attribute_option_id', ['in' => $this->getAllowedOptionIds()]);
            }

            // die($collection->getSelect()->__toString());

            $collection
            ->addFieldToFilter('group_name', ['like' => $mappingGroupName . '%'])
            ->addFieldToFilter('priority', ['eq' => $this->nextPriority])
            ->addFieldToFilter('mapping_type', 'attribute_set');
            
            $collection->setOrder('priority', 'ASC');
            if ($priority == 1) {
                // dd($this->nextPriority);
                // die('<pre>'.print_r($collection->toArray()['items'],true));
            }

            if (!$collection->getSize()) {
                // dd($collection->getSelect()->__toString());
                $this->nextPriorityMappingAttributeCode = null;
                $this->nextPriorityMappingAttributeVal = null;

                return;
            }

            if ($collection->getSize()) {
                $mapping = $collection->getFirstItem();

                $data = [];
                $data['attribute_set_id'] = $mapping->getTargetAttributeSetId();
                $data['mapping_group_name'] = $mapping->getGroupName();
                $data['mapping_priority'] = $mapping->getPriority();
                $data['attribute_code'] = $mapping->getAttributeCode();

                if ($priority == 1) {
// dd($data);
                }
                $this->attributeSetData = $data;
                $this->processedAttributeCodes[] = $data['attribute_code'];
            }
        }

        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/PlatformDbHelper.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info('getNextPriorityMapping | attributeSetData | '.print_r($this->attributeSetData,true));
        $logger->info('getNextPriorityMapping | processedAttributeCodes | '.print_r($this->processedAttributeCodes,true));
    }

    public function initMainMapping() {
        if (!is_numeric($this->mainAttrVal)) {
            $this->mainAttrVal = $this->helper->getOptionId($this->mainAttrCode, $this->mainAttrVal);
        }

        // dd($this->mainAttrCode);

        $collection = $this->attributeSetMappingCollectionFactory->create()
        ->addFieldToFilter('attribute_code', $this->mainAttrCode)
        ->addFieldToFilter('attribute_option_id', $this->mainAttrVal)
        ->addFieldToFilter('mapping_type', 'attribute_set')
        ->setOrder('priority', 'ASC');

        // dd($this->categoriesName);

        if ($this->categoriesName) {
            $collection->addFieldToFilter('group_name', ['like' => $this->categoriesName . ' | %']);
        }
        
        // dd($collection->getSelect()->__toString());

        if (!$collection->getSize()) {
            return null;
        }
        
        // dd($collection);

        $mapping = $collection->getFirstItem();

        $this->attributeSetData = [
            'attribute_set_id'   => $mapping->getTargetAttributeSetId(),
            'mapping_group_name' => $mapping->getGroupName(),
            'mapping_priority'   => $mapping->getPriority(),
            'attribute_code'     => $mapping->getAttributeCode(),
        ];

        // dd($this->attributeSetData);

        $this->processedAttributeCodes[] = $mapping->getAttributeCode();

        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/PlatformDbHelper.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info('initMainMapping | attributeSetData | '.print_r($this->attributeSetData,true));
        $logger->info('initMainMapping | processedAttributeCodes | '.print_r($this->processedAttributeCodes,true));

        return $this->attributeSetData;
    }

    public function processNextMappings($isSample = false)
    {
        $x = 1;
        while (true) {
            $this->getNextPriorityMappingAttributeCode();

            if (!$this->nextPriorityMappingAttributeCode) {
                break; // tidak ada lagi mapping
            }

            if ($x++ == 4) {
                break;
            }

            $this->getNextPriorityMapping($isSample);
        }
    }

    protected array $groupCache = [];

    protected function getGroupCollection($groupName)
    {
        if (!isset($this->groupCache[$groupName])) {
            $this->groupCache[$groupName] = $this->attributeSetMappingCollectionFactory->create()
                ->addFieldToFilter('group_name', ['like' => $groupName . '%'])
                ->addFieldToFilter('mapping_type', 'attribute_set')
                ->setOrder('priority', 'ASC');
        }
        return $this->groupCache[$groupName];
    }

    public function getAttributeSetId($isSample = false) 
    {
        if ($this->debug) {
            $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/downloadPlatformDb.log');
            $logger = new \Zend_Log();
            $logger->addWriter($writer);
            $logger->info('PlatformDatabaseHelper.php | getAttributeSetId');
        }

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->attributeSetMappingCollectionFactory = $objectManager->create('\TCGCollective\ProductAttributesLink\Model\ResourceModel\ProductAttributesLink\CollectionFactory');

        $mainAttributeSet = null;
        
        // GET ATTRIBUTE SET BY MASTER ATTRIBUTE
        $this->initMainMapping();
        $this->processNextMappings($isSample);
        
        // die('<pre>'.print_r($this->debugGetNextPriorityMappingAttributeCode,true));

        // dd($this->attributeSetData);

        if (!$this->attributeSetData) {
            if ($this->debug) {
                // dd('DEBUG ONLY | No Attribute Set found.');
            }

            $this->messageManager->addErrorMessage(__('No attribute set mapping found for category %1. Please upload attribute set mapping and try again.', $this->categoriesName));
            return false;
        }

        if ($this->debug) {
            $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/downloadPlatformDb.log');
            $logger = new \Zend_Log();
            $logger->addWriter($writer);
            $logger->info('PlatformDatabaseHelper.php | getAttributeSetId');
            $logger->info('attributeSetData: '.print_r($this->attributeSetData,true));
        }

        // TEST
        if ($this->attributeSetData) {
            $attributeSetId = $this->attributeSetData['attribute_set_id'] ?? null;
            if ($attributeSetId) {
                if ($this->debug) {
                    $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/downloadPlatformDb.log');
                    $logger = new \Zend_Log();
                    $logger->addWriter($writer);
                    $logger->info('attributeSetId: '.print_r($attributeSetId,true));
                }
                
                return $attributeSetId;
            }
        }

        return null;
    }
    
    public function removeCache($cacheKey) {
        $this->cache->remove($cacheKey);
    }

    // CUSTOM TABLE IMPORT  CSV COMPARE WITH ATTRIBUTE SET
    public function compareCustomTableCsvWithAttributeSet($rowData) 
    {
        $this->initCustomTableData($rowData);

        // dd($this->customTableCategories);
        // $this->initImportCustomTableData($rowData);

        // dd($rowData);
        $attributeSetId = $this->getAttributeSetId();
        // dd($attributeSetId);
        if (!$attributeSetId) {
            dd('attribute set not found');
        }
        // dd($this->mainAttrValLabel);

        if (!$this->prefix) {
            $this->messageManager->addError(__('Please add Prefix for "%1"', $this->mainAttrValLabel));
            return false;
        }

        $csvHeader = array_keys($rowData);

        $this->getAttributeSetFieldsToDownload();

        // dd($this->fieldsToDownload);
        $this->fieldsToDownload = array_filter(
            $this->fieldsToDownload, fn($col) => !in_array($col, ['entity_id','attribute_set','price', 'term_and_conditions'])
        );

        $missingFieldsOnCsv = array_diff($this->fieldsToDownload, $csvHeader);

        // dd($missingFieldsOnCsv);

        if ($missingFieldsOnCsv) {
            $error = __('Attribute Set = "%1". Missing fields on CSV -> "%2"', $this->attributeSetName, implode(", ", $missingFieldsOnCsv));
            $this->messageManager->addError($error);
            return false;
        }
        
        return true;
    }

    public function getAttributeSetFieldsToDownload($isCatalog = false, $isSample = false) 
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->attributeSetRepository = $objectManager->create('\Magento\Eav\Api\AttributeSetRepositoryInterface');
        $this->attributeGroup = $objectManager->create('\Magento\Eav\Model\Entity\Attribute\Group');
        $this->attribute = $objectManager->create('\Magento\Eav\Model\Entity\Attribute');

        $attributeSetId = $this->getAttributeSetId($isSample);
        if (!$attributeSetId) {
            return null;
        }

        // dd($attributeSetId);
        
        // Get attribute set data by attribute set id
        // Get attribute set data by id
        $attributeSet = $this->attributeSetRepository->get($attributeSetId);
        if (!$attributeSet) {
            dd('attribute set not found');
        }

        // dd($attributeSet);

        $this->attributeSetName = $attributeSet->getData('attribute_set_name');

        // dd($attributeSet);

        $generalAttrCodes = [
            "category_ids",
            "status",
            // "sku",
            "sku_type",
            "old_id",
            "price_type",
            "tax_class_id",
            "quantity_and_stock_status",
            "weight_type",
            "url_path",
            "visibility",
            "news_from_date",
            "news_to_date",
            "required_options",
            "country_of_manufacture",
            "has_options",
            "image_label",
            "seller_id",
            "small_image_label",
            "approval",
            "thumbnail_label",
            "seller_group",
            "created_at",
            "featured_package",
            "updated_at",
            "membership_order",
            "seller_duration",
            "seller_featured_package",
            "seller_membership_order",
            "auction",
            "sale",
            "cod_available",
            "link_to_other_stores",
            "membership_type",
            "membership_country",
            "links_purchased_separately",
            "samples_title",
            "links_title",
            "links_exist",
            "source",
            "publish_status",
            "sku"
        ];
        if ($this->isDebug()) {
            $this->fieldsToDownload[] = "attribute_set";
        }

        if (!$isCatalog) {
            $this->fieldsToDownload[] = "entity_id";
            $this->fieldsToDownload[] = "images";
        } else {
            $this->fieldsToDownloadForCatalog[] = 'publish_status';
            $this->fieldsToDownloadForCatalog[] = 'listing_country';
            $this->fieldsToDownloadForCatalog[] = 'price';
            $this->fieldsToDownloadForCatalog[] = 'qty';
            $this->fieldsToDownloadForCatalog[] = 'base_image';
            $this->fieldsToDownloadForCatalog[] = 'additional_images';
            $this->fieldsToDownloadForCatalog[] = 'categories';
        }

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
                        if (!in_array($attributeCode, $generalAttrCodes)) {
                            $this->fieldsToDownloadForCatalog[] = $attributeCode;

                            if ($this->prefix && str_contains($attributeCode, $this->prefix)) {
                                $attributeCode = str_replace($this->prefix, "", $attributeCode);
                                // dd($attributeCode);
                            }

                            // dd($this->prefix);
                            
                            $this->fieldsToDownload[] = $attributeCode;
                        }
                    }
                }
            }
        }

        $attributeGroupCollection = $this->attributeGroup->getCollection();
        $attributeGroupCollection->addFieldToFilter('attribute_group_name', 'Product Details');
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
                        if (!in_array($attributeCode, $generalAttrCodes)) {
                            $this->fieldsToDownloadForCatalog[] = $attributeCode;

                            if ($this->prefix && str_contains($attributeCode, $this->prefix)) {
                                $attributeCode = str_replace($this->prefix, "", $attributeCode);
                            }
                            
                            $this->fieldsToDownload[] = $attributeCode;
                        }
                    }
                }
            }
        }

        return $this->fieldsToDownload;
    }

    public function initPrefix($rowData = null) 
    {
        // dd($this->mainAttrCode);
        // dd($this->mainAttrValLabel);
        $this->prefix = $this->helper->getPrefixByCategoryId($this->categoryId, $this->mainAttrValLabel, $this->mainAttrCode);
        // dd($this->prefix);
        return $this->prefix;
    }

    // EXTERNAL AJAX | EDIT SETS/SINGLES FORM | GET ONLY ALLOWED FIELDS FROM ATTRIBUTE SET
    public function getAllowedFieldsFromAttributeSet() 
    {
        $refererUrl = $this->redirect->getRefererUrl();
        // dd($refererUrl);
        if (str_contains($refererUrl, 'edit/entity_id')) {
            $this->initCustomTableData();

            $result = [
            'success' => false,
            'name' => null,
            'file' => null,
            'refererUrl' => null
        ];
        
        $this->initCustomTableModel();
        
        // Get sets data
        $model = $this->customTableModel;
        
        $collection = $model->getCollection();

        $mainAttrColumn = $this->mainAttrCode;

        // Filter sets that only having singles
        // if ($this->customTableType == "sets") {
        //     $collection->getSelect()->join(
        //         ['singles' => $this->singlesTableName],
        //         'main_table.entity_id = singles.set_id',
        //         []
        //     )->group('main_table.entity_id');

        //     $mainAttrColumn = $this->mainAttrCode;
        // }
        
        $connection = $this->customTableModel->getResource()->getConnection();
// dd($this->customTableType);
        // Get sets data
        if ($this->customTableType == "singles") {
            $setsTableName = $this->customTableModel->getResource()->getTable($this->setsTableName);

            // Ambil daftar kolom dari DB
            $setsTableColumns = array_keys($connection->describeTable($setsTableName));

            // dd($setsTableColumns);
            // dd($this->fieldsToDownload);

            // $fieldToSelectFromSets = array_intersect($this->fieldsToDownload, $setsTableColumns);
            
            $fieldToSelectFromSets2 = array_filter(
                $setsTableColumns, fn($col) => !in_array($col, ['entity_id', 'images','name','supplier_sku'])
            );

            $collection->getSelect()->join(
                ['sets' => $this->setsTableName],
                'sets.entity_id = main_table.set_id',
                []
            )->group('main_table.entity_id');
        }

        if ($this->customTableType == "singles") {
            $mainAttrColumn = 'sets.' . $this->mainAttrCode;
        }

        // Filter by main attribute code
        $collection->addFieldToFilter($mainAttrColumn, $this->mainAttrVal);
        

        if ($this->customTableType == "singles") {
            $collection->addFieldToFilter('set_id', $this->setId);

            $model = $this->getCustomTableModel($this->mainAttrCode, 'sets');
            if ($model) {
                // dd($model);
                // dd($this->setId);
                $setscollection = $model->getCollection();
                $setscollection->addFieldToFilter('entity_id', $this->setId);
                $this->currentCustomTableData = $setscollection->getFirstItem()->getData();
            }
        } else {
            $this->currentCustomTableData = $collection->getFirstItem()->getData();
        }

            $this->getAttributeSetFieldsToDownload();
        }

        return [
            'attribute_set' => $this->attributeSetData,
            'data' => $this->fieldsToDownload
        ];
    }

    public function initCustomTableData($rowData = null) {
        $this->initCustomTableType();
// 
        // dd($this->customTableType);
        $customTableData = null;

        if ($this->customTableType == "customattributes") {
            // $entity = $this->request->getParam('categories');
            // // dd($entity);
            // if ($entity) {
            //     $customTableData = $this->getCustomTableDataByKey($entity);
            // } else {
                // $refererUrl = $this->redirect->getRefererUrl();
                $refererUrl = $this->getCurrentUrl();
                // dd($refererUrl);
                if ($refererUrl) {
                    $entity = null;
                    // dd($refererUrl);
                    if (preg_match('#/entity/([^/]+)#', $refererUrl, $matches)) {
                        $entity = $matches[1]; // The first capturing group contains the set_id
                    }
                // dd($refererUrl);
                    $path = parse_url($refererUrl, PHP_URL_PATH);

                    // Pecah URL menjadi array
                    $segments = explode('/', trim($path, '/'));

                    $this->mainAttrCode = $segments[count($segments)-2];

                    $this->mainAttrVal = $segments[count($segments)-1];

                    // $entity = $this->request->getParam('entity');
                    // dd($entity);
                    if ($entity) {
                        $mainCategoriesConfigData = $this->mainCategoriesConfigProvider->getByEntityCode($entity);
                        // dd($mainCategoriesConfigData);
                        if ($mainCategoriesConfigData) {
                            $this->mainCategoriesConfigData = $mainCategoriesConfigData;
                            return;
                        }
                    }

                    // dd($mainAttrCode);
                    if ($this->mainAttrCode) {
                        $mainCategoriesConfigData = $this->mainCategoriesConfigProvider->getByMainAttrCode($this->mainAttrCode);
                        if ($mainCategoriesConfigData) {
                            $this->mainCategoriesConfigData = $mainCategoriesConfigData;
                            return;
                        }
                    }

                    // dd($segments);

                    // Ambil segmen ke-3 (0-based: 0=backoffice, 1=productattributeslink, 2=cardgameattributes)
                    $entity = str_replace("attributes", "", $segments[2]);
                    // dd($entity);
                    $customTableData = $this->getCustomTableDataByKey($entity);

                    // dd($entity);

                    $this->mainCategoriesConfigData = $this->mainCategoriesConfigProvider->getByEntityCode($entity);
                    return;
                }
            // }
        } else {
            $customTableData = $this->getCurrentCustomTableData($rowData);
        }

        // dd($customTableData);
        // if (!$customTableData) {
        //     dd('custom table data not found');
        // }

        $this->customTableData = $customTableData;
        $this->mainAttrCode = $customTableData['mainAttrCode'] ?? null;
        $this->mainSetsAttrCode = $customTableData['mainSetsAttrCode'] ?? null;
        $this->singlesTableName = $customTableData['singlesTableName'] ?? null;

        $this->initMainAttrVal($rowData);
        $this->initMainAttrValLabel($rowData);
        $this->initCategories();
        $this->getCategoryByEntityKey();
        $this->initPrefix($rowData);
    }

    public function initMainAttrValLabel($rowData = null) 
    {
        // dd($this->mainAttrCode);
        // dd($this->mainAttrVal);
        if (is_numeric($this->mainAttrVal)) {
            // dd('int');
            $this->mainAttrValLabel = $this->helper->getOptionLabelById($this->mainAttrCode, $this->mainAttrVal);
            return;
        }

        $this->mainAttrValLabel = $this->mainAttrVal;
        $this->mainAttrVal = $this->helper->getOptionId($this->mainAttrCode, $this->mainAttrValLabel);
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

    public function getSegmentFromUrl($url, $position) {
        $path = parse_url($url, PHP_URL_PATH);
        $segments = explode('/', trim($path, '/'));

        return $segments[$position] ?? null;
    }

    public function isDebug() 
    {
        if ($this->isDebug == null) {
            $this->isDebug = $this->scopeConfig->getValue(
                'categories/debug/is_debug',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
        }
        
        return $this->isDebug ? true : false;
    }
}