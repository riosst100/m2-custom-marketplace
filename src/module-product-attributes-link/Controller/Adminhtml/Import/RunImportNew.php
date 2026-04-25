<?php

namespace TCGCollective\ProductAttributesLink\Controller\Adminhtml\Import;

use Magento\Backend\App\Action;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Framework\Image\AdapterFactory;
use Magento\Store\Model\ScopeInterface; 
use Magento\Framework\App\ResourceConnection;

class RunImportNew extends \TCGCollective\ProductAttributesLink\Controller\Adminhtml\Index
{
    protected $fileSystem;
    protected $uploaderFactory;
    protected $request;
    protected $adapterFactory;
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \TCGCollective\ProductAttributesLink\Model\CardGamesSetsFactory
     */
    protected $cardGamesSetsFactory;
    protected $cardGamesSealedProductsFactory;
    protected $sportCardSealedProductsFactory;

    /**
     * @var \TCGCollective\ProductAttributesLink\Model\CardGamesSetsCardFactory
     */
    protected $cardGamesSetsCardFactory;

    /**
     * @var \TCGCollective\ProductAttributesLink\Model\LegoSetsFactory
     */
    protected $legoSetsFactory;

    /**
     * @var \TCGCollective\ProductAttributesLink\Model\LegoSinglesFactory
     */
    protected $legoSinglesFactory;

    /**
     * @var \TCGCollective\ProductAttributesLink\Model\MiniatureGameSetsFactory
     */
    protected $miniatureGameSetsFactory;

    /**
     * @var \TCGCollective\ProductAttributesLink\Model\MiniatureGameSinglesFactory
     */
    protected $miniatureGameSinglesFactory;

    /**
     * @var \TCGCollective\ProductAttributesLink\Helper\Data
     */
    protected $mappingHelper;

    /**
     * @var \TCGCollective\ProductAttributesLink\Model\LivingCardGameSetsFactory
     */
    protected $livingCardGameSetsFactory;

    /**
     * @var \TCGCollective\ProductAttributesLink\Model\NonSportCardsSetsFactory
     */
    protected $nonSportCardsSetsFactory;

    /**
     * @var \TCGCollective\ProductAttributesLink\Model\NonSportCardsSetsCardFactory
     */
    protected $nonSportCardsSetsCardFactory;

    /**
     * @var \TCGCollective\ProductAttributesLink\Model\SportCardsSetsFactory
     */
    protected $sportCardsSetsFactory;
    protected $customAttributesFactory;

    /**
     * @var \TCGCollective\ProductAttributesLink\Model\SportCardsSetsCardFactory
     */
    protected $sportCardsSetsCardFactory;
    protected $actionToySetsFactory;
    protected $actionToySinglesFactory;
    // protected $actionToyAttributesFactory;
    protected $animeSetsFactory;
    protected $animeSinglesFactory;
    protected $searchAssets;
    protected $searchCriteriaBuilder;
    protected $filterBuilder;
    protected $uploadHistoryFactory;
    protected $dollsSetsFactory;
    protected $dollsSinglesFactory;
    protected $cardGamesDropFactory;
    
    // Custom Attributes
    protected $cardGamesAttributesFactory;
    protected $actionToysAttributesFactory;

    protected $cardGamesReleaseFactory;
    protected $productAttributesArr = [];

    protected $prefix = null;
    protected $mainAttrLabel = null;
    protected $categoryName = null;
    protected $redirectUrl = null;
    protected $model = null;
    protected $mainEntity = null;
    protected $importType = null;
    protected $filePath = null;
    protected $categoriesData = null;
    protected $allowedFields = [];
    
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Filesystem $fileSystem,
        \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        AdapterFactory $adapterFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry,
        \TCGCollective\ProductAttributesLink\Model\CardGamesSetsFactory $cardGamesSetsFactory,
        \TCGCollective\ProductAttributesLink\Model\CardGamesSealedProductsFactory $cardGamesSealedProductsFactory,
        \TCGCollective\ProductAttributesLink\Model\SportCardsSealedProductsFactory $sportCardSealedProductsFactory,
        \TCGCollective\ProductAttributesLink\Model\CardGamesDropFactory $cardGamesDropFactory,
        \TCGCollective\ProductAttributesLink\Model\CardGamesReleaseFactory $cardGamesReleaseFactory,
        \TCGCollective\ProductAttributesLink\Model\CardGamesSetsCardFactory $cardGamesSetsCardFactory,
        \TCGCollective\ProductAttributesLink\Model\LegoSetsFactory $legoSetsFactory,
        \TCGCollective\ProductAttributesLink\Model\LegoSinglesFactory $legoSinglesFactory,
        \TCGCollective\ProductAttributesLink\Model\MiniatureGameSetsFactory $miniatureGameSetsFactory,
        \TCGCollective\ProductAttributesLink\Model\MiniatureGameSinglesFactory $miniatureGameSinglesFactory,
        \TCGCollective\ProductAttributesLink\Helper\Data $mappingHelper,
        \TCGCollective\ProductAttributesLink\Model\LivingCardGameSetsFactory $livingCardGameSetsFactory,
        \TCGCollective\ProductAttributesLink\Model\NonSportCardsSetsFactory $nonSportCardsSetsFactory,
        \TCGCollective\ProductAttributesLink\Model\NonSportCardsSetsCardFactory $nonSportCardsSetsCardFactory,
        \TCGCollective\ProductAttributesLink\Model\SportCardsSetsFactory $sportCardsSetsFactory,
        \TCGCollective\ProductAttributesLink\Model\SportCardsSetsCardFactory $sportCardsSetsCardFactory,
        \TCGCollective\ProductAttributesLink\Model\ActionToySetsFactory $actionToySetsFactory,
        \TCGCollective\ProductAttributesLink\Model\ActionToySinglesFactory $actionToySinglesFactory,
        // \TCGCollective\ProductAttributesLink\Model\ActionToysAttributesFactory $actionToyAttributesFactory,
        \TCGCollective\ProductAttributesLink\Model\AnimeSetsFactory $animeSetsFactory,
        \TCGCollective\ProductAttributesLink\Model\AnimeSinglesFactory $animeSinglesFactory,
        \Magento\MediaGallery\Model\SearchAssets $searchAssets,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Api\FilterBuilder $filterBuilder,
        \TCGCollective\ProductAttributesLink\Model\CustomAttributesFactory $customAttributesFactory,
        \TCGCollective\ProductAttributesLink\Model\UploadHistoryFactory $uploadHistoryFactory,
        \TCGCollective\ProductAttributesLink\Model\DollsSetsFactory $dollsSetsFactory,
        \TCGCollective\ProductAttributesLink\Model\DollsSinglesFactory $dollsSinglesFactory,
        ResourceConnection $resource,
        \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $attributeCollectionFactory,
        \TCGCollective\ProductAttributesLink\Model\CardGamesAttributesFactory $cardGamesAttributesFactory,
        \TCGCollective\ProductAttributesLink\Model\ActionToysAttributesFactory $actionToysAttributesFactory
    ) {
        parent::__construct($context, $resultPageFactory, $registry);
        $this->fileSystem = $fileSystem;
        $this->request = $request;
        $this->scopeConfig = $scopeConfig;
        $this->adapterFactory = $adapterFactory;
        $this->uploaderFactory = $uploaderFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->cardGamesSetsFactory = $cardGamesSetsFactory;
        $this->sportCardSealedProductsFactory = $sportCardSealedProductsFactory;
        $this->cardGamesSealedProductsFactory = $cardGamesSealedProductsFactory;
        $this->cardGamesDropFactory = $cardGamesDropFactory;
        $this->cardGamesReleaseFactory = $cardGamesReleaseFactory;
        $this->cardGamesSetsCardFactory = $cardGamesSetsCardFactory;
        $this->legoSetsFactory = $legoSetsFactory;
        $this->legoSinglesFactory = $legoSinglesFactory;
        $this->miniatureGameSetsFactory = $miniatureGameSetsFactory;
        $this->miniatureGameSinglesFactory = $miniatureGameSinglesFactory;
        $this->mappingHelper = $mappingHelper;
        $this->livingCardGameSetsFactory = $livingCardGameSetsFactory;
        $this->nonSportCardsSetsFactory = $nonSportCardsSetsFactory;
        $this->nonSportCardsSetsCardFactory = $nonSportCardsSetsCardFactory;
        $this->sportCardsSetsFactory = $sportCardsSetsFactory;
        $this->sportCardsSetsCardFactory = $sportCardsSetsCardFactory;
        $this->actionToySetsFactory = $actionToySetsFactory;
        $this->actionToySinglesFactory = $actionToySinglesFactory;
        // $this->actionToyAttributesFactory = $actionToyAttributesFactory;
        $this->animeSetsFactory = $animeSetsFactory;
        $this->animeSinglesFactory = $animeSinglesFactory;
        $this->searchAssets = $searchAssets;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->customAttributesFactory = $customAttributesFactory;
        $this->uploadHistoryFactory = $uploadHistoryFactory;
        $this->dollsSetsFactory = $dollsSetsFactory;
        $this->dollsSinglesFactory = $dollsSinglesFactory;
        $this->resource = $resource;
        $this->attributeCollectionFactory = $attributeCollectionFactory;
        $this->cardGamesAttributesFactory = $cardGamesAttributesFactory;
        $this->actionToysAttributesFactory = $actionToysAttributesFactory;
    }

    public function execute()
    {
        $refererUrl = $this->_redirect->getRefererUrl();

        // Params
        $this->importType = $this->getRequest()->getParam('import_type');
        $this->categoriesData = $this->getRequest()->getParam('categories');

        $entity = $this->getRequest()->getParam('entity');
        if (!$entity) {
            $entity = $this->getRequest()->getParam('categories');
        }

        // dd($this->getRequest()->getParams());

        // CSV Upload process
        try {
            $result = $this->processUpload($entity);
            if ($result) {
                $this->getRedirectUrl();

                // dd($this->redirectUrl);

                return $this->_redirect($this->redirectUrl);
            }
        } catch (\Exception $e) {   
            $this->messageManager->addError(__($e->getMessage()));
        }

        // Custom Attributes Import Process
        if ($this->importType == "attributes") {
            $this->importCustomAttributes();
        }

        // $setId = $this->getRequest()->getParam('set_id');
        // $this->categoriesData = $this->getRequest()->getParam('categories');
        // $entity = $this->getRequest()->getParam('entity') ?? $this->categoriesData;
        
        return $this->_redirect($refererUrl);
    }

    public function processUpload($entity) 
    {
        if (
            !isset($_FILES['data']['name']) 
            || isset($_FILES['data']['name']) 
            && !$_FILES['data']['name']
        ) {
            $this->messageManager->addError(__("Please try again."));
            return false;
        }

        $uploaderFactory = $this->uploaderFactory->create(['fileId' => 'data']);
        $uploaderFactory->setAllowedExtensions(['csv']);
        $uploaderFactory->setAllowRenameFiles(true);
        $uploaderFactory->setFilesDispersion(true);

        $mediaDirectory = $this->fileSystem->getDirectoryRead(DirectoryList::MEDIA);
        $destinationPath = $mediaDirectory->getAbsolutePath(
            'custom_table/upload/' . $this->importType
        );

        $result = $uploaderFactory->save($destinationPath);
        if (!$result) {
            throw new LocalizedException(__('File cannot be saved to path: $1', $destinationPath));
        }

        $this->filePath = 'custom_table/upload/' . $this->importType . $result['file'];
        
        $destinationfilePath = $mediaDirectory->getAbsolutePath($this->filePath);
        
        $f_object = fopen($destinationfilePath, "r");
        if (!$f_object) {
            $this->messageManager->addError(__("File has been empty"));
            return false;
        }

        // Mapping column
        $columnMapping = $this->getColumnMapping(fgetcsv($f_object));

        // Get upload data
        $uploadData = $this->getUploadData();
        if (!$uploadData) {
            $this->messageManager->addError(__("File has been empty"));
            return false;
        }

        // Process Upload
        $this->redirectUrl = $uploadData['redirectUrl'] ?? null;
        $this->allowedFields = $uploadData['allowedFields'] ?? [];
        $this->mainEntity = $uploadData['mainEntity'] ?? null;

        // Get category name
        $categoryName = $this->getCategoryName($entity);

        // Get custom table metadata
        $customTableMetadata = $this->mappingHelper->getCustomTableMetadata(
            null,
            $categoryName
        );
        
        if (!$customTableMetadata) {
            $this->messageManager->addError(__("Custom Table Metadata not found."));
            return false;
        }

        // dd($customTableMetadata);
        
        $mainAttrCode = $customTableMetadata['mainAttrCode'] ?? null;
        $mainSetsAttrCode = $customTableMetadata['mainSetsAttrCode'] ?? null;
        $setsCollection = $customTableMetadata['setsCollection'] ?? null;

        $count = 0;
        $updated = 0;
        $totalRow = 0;
        
        // Foreach CSV data columns
        while (($dataColumns = fgetcsv($f_object)) !== FALSE) {
            $allEmpty = count(array_filter($dataColumns, fn($v) => $v !== "")) === 0;
            if ($allEmpty) {
                continue;
            }

            // Get main attribute val & label
            $mainAttrVal = $dataColumns[$columnMapping[$mainAttrCode]] ?? null;
            $mainAttrLabel = $this->getMainAttrLabel($mainAttrCode, $mainAttrVal);
            
            // Get attribute prefix
            $prefix = $this->getPrefix($mainAttrLabel, $mainAttrCode);

            $dataSyncToCustomAttributes = [];
            $data = [];

            foreach($columnMapping as $columnName => $index) {
                $columnName = strtolower($columnName);
                
                // Process data
                $processedData = $this->processData(
                    $totalRow,
                    $columnName,
                    $dataColumns[$index],
                    $columnMapping,
                    $entity
                );

                if ($this->importType == "attributes" && $columnName == $mainAttrCode) {
                    $mainAttrLabel = $dataColumns[$columnMapping[$mainAttrCode]] ?? null;
                    $mainAttrVal = $this->mappingHelper->getOptionId($mainAttrCode, $mainAttrLabel);
                    $processedData = $mainAttrVal;
                }

                // Update column data
                $data[$columnName] = $processedData;
                
                // $attributeCode = $this->getProductAttributeCode(
                //     $columnName, 
                //     $prefix
                // );

                // $uniqueKey = md5(
                //     $attributeCode . $processedData . $mainAttrLabel
                // );
                
                // // Sync data to custom attributes
                // if ($this->importType != "attributes") {
                //     $dataSyncToCustomAttributes[] = [
                //         $mainAttrCode => $mainAttrLabel,
                //         'categories' => 'card_game',
                //         'attribute_code' => $attributeCode, 
                //         'attribute_value' => $processedData,
                //         'unique_key' => $uniqueKey
                //     ];
                // }
            }

            if ($this->importType == "attributes") {
                $uniqueKey = md5(
                    $data['attribute_code'] . $data['attribute_value'] . $data[$mainAttrCode]
                );

                $data['unique_key'] = $uniqueKey;
                $data['categories'] = $entity;
            }

            // dd($data);

            // Validate with attribute set
            if ($this->importType != "attributes") {
                $this->validateWithAttributeSet();
            }

            // Save process
            $saveResult = $this->saveEntity($data);
            if ($saveResult) {
                $isUpdated = $saveResult['is_updated'] ?? false;
                if ($isUpdated) {
                    $updated++;
                }

                $isAdded = $saveResult['is_added'] ?? false;
                if ($isAdded) {
                    $count++;
                }
            }

            $totalRow++;
        }

        // Sync to custom table
        if ($dataSyncToCustomAttributes) {
            $this->syncToCustomAttributes($dataSyncToCustomAttributes);
        }

        // Set messages
        if ($count > 0) {
            $this->messageManager->addSuccess(__('Added %1 of %2 record(s).', $count, $totalRow));
        }

        if ($updated > 0) {
            $this->messageManager->addSuccess(__('Updated %1 of %2 record(s).', $updated, $totalRow));
        }
        
        if (!$count && !$updated) {
            $this->messageManager->addSuccess(__('No uploaded data.'));
        }

        // Save history
        $historyData = [
            'upload_type' => $this->importType,
            'categories' => $entity,
            'file_path' => $this->filePath,
            'total_row' => $totalRow,
            'total_row_added' => $count,
            'total_row_updated' => $updated,
            'messages' => 'success',
            'status' => 1,
            'user_id' => $this->mappingHelper->getAdminId(),
            'entity' => $this->mappingHelper->getOptionLabelById($entity, $this->categoriesData)
        ];

        $this->saveUploadHistory($historyData);

        return true;
    }

    public function getRedirectUrl() {
        $categories = $this->getRequest()->getParam('categories');
        if ($categories == "cardgame") {
            $this->redirectUrl = 'productattributeslink/cardgameattributes/index';
        }

        if ($categories == "action_toys") {
            $this->redirectUrl = 'productattributeslink/actiontoysattributes/index';
        }

        return $this->redirectUrl;
    }

    public function getColumnMapping($columns) {
        $columnMapping = [];
        
        foreach($columns as $index => $columnName) {
            if ($columnName) {
                $columnName = strtolower($columnName);
                $columnName = strtolower(preg_replace('/[^a-zA-Z0-9_]/', '', $columnName));
                $columnMapping[$columnName] = $index;
            }
        }
        
        return $columnMapping;
    }

    public function getMainAttrLabel($mainAttrCode, $mainAttrVal) {
        if (!$this->mainAttrLabel && $mainAttrVal) {
            if (is_numeric($mainAttrVal)) {
                $this->mainAttrLabel = $this->mappingHelper->getOptionLabelById(
                    $mainAttrCode,
                    $mainAttrVal
                );
            } else {
                $this->mainAttrLabel = $mainAttrVal;
            }
        }

        return $this->mainAttrLabel;
    }

    public function getPrefix($mainAttrLabel, $mainAttrCode) {
        if (!$this->prefix) {
            $this->prefix = $this->mappingHelper->getAttributePrefix(
                $mainAttrLabel,
                $mainAttrCode
            );
        }

        return $this->prefix;
    }

    public function saveUploadHistory($historyData) {
        $history = $this->uploadHistoryFactory->create()
        ->setData($historyData)
        ->save();

        return true;
    }

    public function saveEntity($data) 
    {
        $result = [];

        $this->categoriesData = $this->getRequest()->getParam('categories');

        // Get entity model
        $model = $this->getModel();

        // Prepare collection and filters
        $collection = $model->getCollection();

        // Filter collection / to check existing  or new record
        if (isset($data['entity_id']) && $data['entity_id']) {
            $collection->addFieldToFilter('entity_id', $data['entity_id']);
        } else {
            if ($data && $this->allowedFields) {
                foreach($data as $key => $value) {
                    if (in_array($key, $this->allowedFields)) {
                        $collection->addFieldToFilter($key, $value);
                    }
                }
            }

            if ($this->mainEntity == "set_id") {
                $setId = null;
                if ($mainSetsAttrCode) {
                    $sets = $data[$mainSetsAttrCode] ?? null;
                    if ($sets) {
                        $setsCollection->addFieldToFilter($mainSetsAttrCode, $sets);
                        if ($setsCollection->getSize()) {
                            $model = $setsCollection->getFirstItem();
                            if ($model) {
                                $setId = $model->getId();
                            }
                        }
                    }
                }

                $collection->addFieldToFilter('set_id', $setId);
            }
        }

        // Updated or add new
        if ($collection->getSize()) {
            $model = $collection->getFirstItem();
            $data['entity_id'] = $model->getId();
            
            $result['is_updated'] = true;
        } else {
            $result['is_added'] = true;
        }

        // Set the main entity
        if ($this->mainEntity == "set_id") {
            $data['set_id'] = $setId;
        }

        // Set and save data
        $model->setData($data);
        $model->save();

        if ($this->importType == "attributes") {
            try {
                $this->mappingHelper->getOrInputAttributeOptionId($data['attribute_code'], $data['attribute_value']);
            } catch(\Exception $e) {}
        }

        return $result;
    }

    public function validateWithAttributeSet() 
    {
        $mainSetsAttrCode = null;
        if ($this->importType == "singles") {
            // VALIDATE HEADERS AND ATTRIBUTE SET
            if ($data) {
                $mainAttrVal = $data[$mainAttrCode] ?? null;

                $cardProductType = null;

                if ($this->importType == "sealed_products") {
                    $cardProductType = 'Sealed Products';
                }

                if ($this->importType == "singles") {
                    $cardProductType = 'Singles';
                }

                if (!$mainAttrVal) {
                    $data[$mainAttrCode] = $this->getRequest()->getParam('categories');
                    $data['card_game_mtg'] = 'Magic: The Gathering';
                }

                $data['card_product_type'] = $cardProductType;
                $data['sc_product_type'] = $cardProductType;

                if ($this->importType == "singles") {
                    $data['card_drop'] = null;
                    $data['card_release'] = null;
                }

                $attributeSetData = $this->mappingHelper->getAttributeSetDataByCategories(
                    $data, 
                    $categoryName, 
                    $customTableMetadata
                );
                
                $attributeSetId = null;
                $attributeSetName = null;

                if ($attributeSetData) {
                    $attributeSetId = $attributeSetData['attribute_set_id'] ?? '';
                    $attributeSetName = $attributeSetData['attribute_set_name'] ?? '';
                }

                $mainAttrVal = $data[$mainAttrCode] ?? null;
                if (is_numeric($mainAttrVal)) {
                    $mainAttrLabel = $this->mappingHelper->getOptionLabelById($mainAttrCode, $mainAttrVal);
                } else {
                    $mainAttrLabel = $mainAttrVal;
                }

                // dd($mainAttrVal);

                $prefix = $this->mappingHelper->getAttributePrefix($mainAttrLabel, $mainAttrCode);
                if (!$prefix) {
                    $this->messageManager->addError(__('Please add Prefix for "%1"', $mainAttrLabel));
                    return $this->_redirect($this->_redirect->getRefererUrl());
                }

                if (!$attributeSetName) {
                    $this->messageManager->addError(__('Please create Attribute Set for "%1"', $mainAttrLabel));
                    return $this->_redirect($this->_redirect->getRefererUrl());
                }
                    // COMPARE HEADERS FIELDS WITH ATTRIBUTE SET
                    if ($attributeSetId && $attributeSetName && $prefix) {
                        $result = $this->compareFieldsWithAttributeSet($attributeSetId, array_keys($data), $prefix);
                    // }

                    if ($result['missing_fields']) {
                        $this->messageManager->addError(__('Attribute Set = "%1". Missing fields on CSV -> "%2"', $attributeSetName, implode(", ", $result['missing_fields'])));
                        return $this->_redirect($this->_redirect->getRefererUrl());
                    }

                    if ($result['missing_fields_on_attribute_set']) {
                        $this->messageManager->addError(__('Attribute Set = "%1". Missing fields on Attribute Set -> "%2"', $attributeSetName, implode(", ", $result['missing_fields_on_attribute_set'])));
                        return $this->_redirect($this->_redirect->getRefererUrl());
                    }
                }
            }
        }
    }

    public function importCustomAttributes() 
    {
        return false;
    }

    

    public function syncToCustomAttributes($dataToSync) 
    {
        $connection = $this->resource->getConnection();
        $tableName = $this->resource->getTableName('marketplace_custom_attributes');

        $connection->insertOnDuplicate(
            $tableName,
            $dataToSync,
            ['attribute_value']
        );
    }

    public function getProductAttributeCode(
        $columnName, 
        $prefix
    ) {
        if ($columnName && $prefix) {
            $productAttributesArr = $this->getProductAttributes();
            if ($productAttributesArr) {
                if (in_array($prefix . $columnName, $productAttributesArr)) {
                    return $prefix . $columnName;
                }

                if (in_array($columnName, $productAttributesArr)) {
                    return $columnName;
                }
            }
        }

        return null;
    }

    public function getProductAttributes() {
        $productAttributesArr = $this->productAttributesArr;
        if (!$productAttributesArr) {
            $collection = $this->attributeCollectionFactory->create();

            $collection->addFieldToFilter(
                'frontend_input',
                ['in' => ['select', 'multiselect']]
            );

            $productAttributesArr = $collection->getColumnValues('attribute_code');
            if ($productAttributesArr) {
                $this->productAttributesArr = $productAttributesArr;
            }
        }

        return $productAttributesArr;
    }

    

    public function compareFieldsWithAttributeSet($attributeSetId, $header, $prefix) 
    {
        $attrSetFields= [];

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $attributeGroupCollection = $objectManager->get('Magento\Eav\Model\Entity\Attribute\Group')->getCollection();
        // $attributeGroupCollection->addFieldToFilter('attribute_group_name', $attribute_set_name);
        $attributeGroupCollection->addFieldToFilter('attribute_set_id', $attributeSetId);
        $attributeGroupId = '';
        foreach ( $attributeGroupCollection as $attributeGroup ) {
            $attrGroupName = $attributeGroup->getAttributeGroupName();
            if ($attrGroupName == "Product Details" || $attrGroupName == "Category Details" || $attrGroupName == "Content") {
                $attributeGroupIds[] = $attributeGroup->getId();
            }

            if ($attrGroupName == "Category Details") {
                $categoryDetailsAttributeGroupId = $attributeGroup->getId();
            }
        }
        if ($attributeGroupIds) {
            foreach($attributeGroupIds as $attributeGroupId) {
                $attributeCollection = $objectManager->get('Magento\Eav\Model\Entity\Attribute')->getCollection();
                $attributeCollection->setAttributeSetFilter($attributeSetId);
                $attributeCollection->setAttributeGroupFilter($attributeGroupId);
                if ($attributeCollection->getSize() > 0) {
                    foreach ($attributeCollection as $attribute) {
                        $attrSetFields[] = $attribute->getAttributeCode();
                    }
                }
            }
        }

        // dd($attrSetFields);

        // $attrSetFields = array_map(function ($item) {
        //     return str_starts_with($item, $prefix) ? substr($item, 4) : $item;
        // }, $attrSetFields);

        // dd($prefix);

        $excludeFields = [
            "sc_set_type",
            "sc_condition",
            "country_of_manufacture",
            "card_game_mtg",
            "images",
            "sealed_products_type",
            "membership_type",
            "membership_country",
            // "card_product_type",
            "category_ids", "status", "supplier_sku", "old_id", "url_path",
            "required_options", "has_options", "name", "image_label", "sku", "small_image_label", 
            "thumbnail_label", "sku_type", "created_at", "price", "price_type", "updated_at", 
            "tax_class_id", "quantity_and_stock_status", "weight", "weight_type", "visibility", 
            "news_from_date", "news_to_date", "seller_id", "approval", "lof_preorder", 
            "preorder_availability", "preorder_msg", "product_rma", "seller_group", 
            "featured_package", "membership_order", "seller_duration", "seller_featured_package", 
            "seller_membership_order", "term_and_conditions", "auction", "sale", "cod_available", 
            "link_to_other_stores", "links_purchased_separately", "samples_title", "links_title", 
            "links_exist", "publish_status", "source", "short_description", "description", "card_condition"
        ];

        if ($prefix == "secret_lair_") {
            $excludeFields = array_merge($excludeFields, [
                'mtg_card_foil',
                'mtg_card_set',
                'mtg_card_set_abbreviation',
                'mtg_release_type'
            ]);
        }

        

        $attrSetFields = array_filter($attrSetFields, function ($item) use ($excludeFields) {
            return !in_array($item, $excludeFields);
        });

        $header = array_filter($header, function ($item) use ($excludeFields) {
            return !in_array($item, $excludeFields);
        });

        // dd($attrSetFields);

        $attrSetFields = array_map(function ($item) use ($prefix) {
            return str_starts_with($item, $prefix) ? substr($item, strlen($prefix)) : $item;
        }, $attrSetFields);

        // dd($attrSetFields);

        // $missingFromBase = array_diff($attrSetFields, $header);

        // Elemen yang ada di $attrSetFields tapi tidak ada di $header
        $missingFromHeader = array_diff($attrSetFields, $header);

        // Elemen yang ada di $header tapi tidak ada di $attrSetFields
        $missingFromAttrSet = [];

        // dd($missingFromBase);

        return [
            'missing_fields' => $missingFromHeader,
            'missing_fields_on_attribute_set' => $missingFromAttrSet
        ];
    }

    private function getModel() 
    {
        $model = null;

        $this->importType = $this->getRequest()->getParam('import_type');
        if ($this->importType) {
            $entity = $this->getRequest()->getParam('entity');

            // CUSTOM ATTRIBUTES MODEL
            if ($this->importType == "attributes") {
                // $entity = $this->getRequest()->getParam('categories');
                // if ($entity == "card_game") {
                    $model = $this->customAttributesFactory->create();
                // }
                
                // if ($entity == "action_toys") {
                    // $model = $this->actionToysAttributesFactory->create();
                // }
            }

            // SEALED PRODUCTS MODEL
            if ($this->importType == "sealed_products") {
                if ($entity == "card_game") {
                    $model = $this->cardGamesSealedProductsFactory->create();
                }

                if ($entity == "sport_card") {
                    $model = $this->sportCardSealedProductsFactory->create();
                }
            }

            // DROP MODEL
            if ($this->importType == "drop") {
                if ($entity == "card_game") {
                    $model = $this->cardGamesDropFactory->create();
                }
            }

            if ($this->importType == "release") {
                if ($entity == "card_game") {
                    $model = $this->cardGamesReleaseFactory->create();
                }
            }
            
            // SETS MODEL
            if ($this->importType == "sets") {
                if ($entity == "card_game") {
                    $model = $this->cardGamesSetsFactory->create();
                }

                if ($entity == "miniature_game") {
                    $model = $this->miniatureGameSetsFactory->create();
                }

                if ($entity == "living_card_game") {
                    $model = $this->livingCardGameSetsFactory->create();
                }

                if ($entity == "action_toys") {
                    $model = $this->actionToySetsFactory->create();
                }

                if ($entity == "bricks_brand") {
                    $model = $this->legoSetsFactory->create();
                }

                if ($entity == "dolls") {
                    $model = $this->dollsSetsFactory->create();
                }

                if ($entity == "sport_card") {
                    $model = $this->sportCardsSetsFactory->create();
                }

                if ($entity == "non_sport_card") {
                    $model = $this->nonSportCardsSetsFactory->create();
                }
            }

            // SINGLES MODEL
            if ($this->importType == "singles") {
                if ($entity == "card_game") {
                    $model = $this->cardGamesSetsCardFactory->create();
                }

                if ($entity == "miniature_game") {
                    $model = $this->miniatureGameSinglesFactory->create();
                }

                if ($entity == "action_toys") {
                    $model = $this->actionToySinglesFactory->create();
                }

                if ($entity == "dolls") {
                    $model = $this->dollsSinglesFactory->create();
                }

                if ($entity == "bricks_brand") {
                    $model = $this->legoSinglesFactory->create();
                }

                if ($entity == "sport_card") {
                    $model = $this->sportCardsSetsCardFactory->create();
                }

                if ($entity == "non_sport_card") {
                    $model = $this->nonSportCardsSetsCardFactory->create();
                }
            }
        }

        return $model;
    }

    private function getUploadData() 
    {
        $this->importType = $this->getRequest()->getParam('import_type');
        if ($this->importType) {
            // SETS UPLOAD DATA
            if ($this->importType == "sets") {
                return $this->setsUploadData();
            }
            
            // SINGLES UPLOAD DATA
            if ($this->importType == "singles") {
                return $this->singlesUploadData();
            }

            // SEALED PRODUCTS UPLOAD DATA
            if ($this->importType == "sealed_products") {
                return $this->sealedProductsUploadData();
            }

            // DROP UPLOAD DATA
            if ($this->importType == "drop") {
                return $this->dropUploadData();
            }

            // RELEASE UPLOAD DATA
            if ($this->importType == "release") {
                return $this->releaseUploadData();
            }

            // CUSTOM ATTRIBUTES UPLOAD DATA
            if ($this->importType == "attributes") {
                return $this->customAttributesUploadData();
            }
        }

        return [];
    }

    private function customAttributesUploadData() 
    {
        $this->categoriesData = $this->getRequest()->getParam('categories');
        $entity = $this->getRequest()->getParam('entity');

        $this->mainEntity = 'categories';
        // $redirectUrl = null;
        $redirectUrl = '*/customattributes/index/categories/'.$this->categoriesData;

        if ($this->categoriesData == "action_toys") {
            $redirectUrl = '*/actiontoysattributes/index';

            $this->allowedFields = [
                'attribute_code',
                'attribute_value',
                'toys_brand'
            ];
        }

        if ($this->categoriesData == "card_game") {
            $redirectUrl = '*/cardgameattributes/index';

            $this->allowedFields = [
                'attribute_code',
                'attribute_value',
                'card_game'
            ];
        }

        return [
            'allowedFields' => $this->allowedFields,
            'mainEntity' => $this->mainEntity,
            'redirectUrl' => $redirectUrl
        ];
    }

    private function setsUploadData() 
    {
        $this->categoriesData = $this->getRequest()->getParam('categories');
        $entity = $this->getRequest()->getParam('entity');

        $this->allowedFields = [];
        $this->mainEntity = null;
        $redirectUrl = null;
        $model = null;

        // Action Toys
        if ($entity == "action_toys") 
        {
            $this->allowedFields = [
                'action_toys_set_name',
                'action_toys_set_item_number',
                'action_toys_release_type',
                'toys_line',
                'toys_subline',
                'action_toys_type',
                'toys_brand',
                'toys_character',
                'toys_franchise',
                'toys_series',
                'toys_scale',
                'toys_condition',
                'toys_feature',
                'toys_product_type',
                'toys_product_type_2',
                'toys_artist',
                'toys_categories',
                'categories',
                'sub_categories',
                'release_year',
                'release_date',
                'material',
                'colour',
                'weight',
                'dimension',
                'height',
                'includes',
                'assembly_required',
                'age_level',
                'number_of_figures',
                'dimensions',
                'bar_code',
                'bar_code_no',
                'bar_code_2',
                'bar_code_no_2'
            ];

            $this->mainEntity = 'toys_brand';
            $redirectUrl = '*/actiontoysets/index/'.$this->mainEntity.'/'.$this->categoriesData;
        }

        // Card Game Sets
        if ($entity == "card_game") 
        {
            $this->allowedFields = [
                'images',
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
                'card_release_jp'                
            ];

            $this->mainEntity = 'card_game';
            $redirectUrl = '*/cardgamessets/index/'.$this->mainEntity.'/'.$this->categoriesData;

            $gameName = $this->mappingHelper->getOptionLabelById($this->mainEntity, $this->categoriesData);
            if (str_contains($gameName, "Secret Lair")) {
                $redirectUrl = '*/cardgamesdrop/index/'.$this->mainEntity.'/'.$this->categoriesData;
            }

            // dd($redirectUrl);
        }

        // Miniature Game Sets
        if ($entity == "miniature_game") 
        {
            $this->allowedFields = [
                'miniature_set',
                'miniature_set_abbreviation',
                'miniature_set_type',
                'miniature_faction',
                'miniature_universe',
                'release_year',
                'release_date',
                'miniature_manufacturer',
                'language',
                'miniature_sealed_products',
                'miniature_supplies',
                'number_of_minis',
                'height',
                'material',
                'age_level',
                'miniature_set_item_no',
                'bar_code',
                'bar_code_no',
                'miniature_brand',
                'miniature_toy_line',
                'miniature_franchise',
                'miniature_characters',
                'miniature_dice_colour',
                'miniature_dice_type',
                'miniature_scenary_type',
                'miniature_scale',
                'miniature_material',
                'miniature_finish',
                'miniature_figures',
                'miniature_battlefield_role',
                'miniature_armies'
            ];

            $this->mainEntity = 'miniature_game';
            $redirectUrl = '*/miniaturegamesets/index/'.$this->mainEntity.'/'.$this->categoriesData;
        }

        // Living Card Game
        if ($entity == "living_card_game") 
        {
            $this->allowedFields = [
                'living_card_set',
                'living_card_set_abbreviation',
                'living_card_set_type',
                'living_card_set_series',
                'release_year',
                'release_date',
                'card_manufacturer',
                'language',
                'item_number',
                'living_card_sealed_products',
                'living_card_supplies',
                'age_level',
                'living_card_series',
                'living_card_brand',
                'bar_code',
                'bar_code_no'
            ];

            $this->mainEntity = 'living_card_game';
            $redirectUrl = '*/livingcardgamesets/index/'.$this->mainEntity.'/'.$this->categoriesData;
        }

        // Sport Card | Sets Upload
        if ($entity == "sport_card") 
        {
            $this->allowedFields = [
                'sc_categories',
                'sc_product_type',
                'sc_league',
                'sc_release',
                'sc_set_type',
                'sc_release_jp',
                'sc_set_name',
                'name',
                'supplier_sku',
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
                'images',
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

            $this->mainEntity = 'sc_categories';
            $redirectUrl = '*/sportcardssets/index/'.$this->mainEntity.'/'.$this->categoriesData;
        }

        // Non Sport Card | Sets
        if ($entity == "non_sport_card") 
        {
            $this->allowedFields = [
                'nsc_categories',
                'nsc_release',
                'nsc_set_type',
                'nsc_release_jp',
                'nsc_set_name',
                'name',
                'supplier_sku',
                'nsc_feature',
                'nsc_brand',
                'nsc_series',
                'nsc_character',
                'nsc_franchise',
                'release_date',
                'release_year',
                'nsc_card_manufacturer',
                'language',
                'bar_code',
                'bar_code_no',
                'age_level',
                'number_of_cards',
                'images',
            ];
            
            $this->mainEntity = 'nsc_categories';
            $redirectUrl = '*/nonsportcardssets/index/'.$this->mainEntity.'/'.$this->categoriesData;
        }

        // Dolls
        if ($entity == "dolls") 
        {
            $this->allowedFields = [
                'dolls_set_name',
                'dolls_set_number',
                'release_year',
                'release_date',
                'age_level',
                'manufacturer',
                'dolls_brand',
                'material',
                'condition',
                'dolls_toy_line',
                'dolls_character',
                'dolls_franchise',
                'dolls_series',
                'dolls_scale',
                'dolls_type',
                'dolls_feature',
                'dolls_product_type'
            ];            
            
            $this->mainEntity = 'dolls_brand';
            $redirectUrl = '*/dollssets/index/'.$this->mainEntity.'/'.$this->categoriesData;
        }

        // Bricks
        if ($entity == "bricks_brand") 
        {
            $this->allowedFields = [
                'bricks_set_name',
                'bricks_set_number',
                'bricks_theme',
                'bricks_subtheme',
                'number_of_pieces',
                'number_of_minifigures',
                'release_year',
                'release_date',
                'age_level',
                'bricks_interest',
                'bricks_packaging',
                'bricks_features',
                'manufacturer',
                'brand',
                'material',
                'condition',
                'bricks_complete_sets_and_packs',
                'bricks_color',
                'bricks_minifigures',
                'bricks_product_type'          
            ];
            
            $this->mainEntity = 'bricks_brand';
            $redirectUrl = '*/legosets/index/'.$this->mainEntity.'/'.$this->categoriesData;
        }

        return [
            'allowedFields' => $this->allowedFields,
            'mainEntity' => $this->mainEntity,
            'redirectUrl' => $redirectUrl,
            'model' => $model
        ];
    }

    public function getCategoryName($entity) {
        if (!$this->categoryName) {
            // Action Toys
            if ($entity == "action_toys") {
                $this->categoryName = 'Action Toys';
            }

            // Card Game Singles
            if ($entity == "card_game") {
                $this->categoryName = 'Collectible Card Games';
            }

            // Miniature Game Singles
            if ($entity == "living_card_game") {
                $this->categoryName = 'Living Card Games';
            }

            // Miniature Game Singles
            if ($entity == "miniature_game") {
                $this->categoryName = 'Miniature Games';
            }
            
            // Sport Card Singles
            if ($entity == "sport_card") {
                $this->categoryName = 'Sport Cards';
            }

            // Non Sport Card
            if ($entity == "non_sport_card") {
                $this->categoryName = 'Non Sports Cards';
            }

            // Dolls
            if ($entity == "dolls") {
                $this->categoryName = 'Fashion Dolls';
            }

            // Bricks
            if ($entity == "bricks_brand") {
                $this->categoryName = 'Bricks';
            }
        }

        return $this->categoryName;
    }

    private function dropUploadData() 
    {
        $entity = $this->getRequest()->getParam('entity');
        $categories = $this->getRequest()->getParam('categories');

        // dd($categories);

        $this->allowedFields = [];
        $this->mainEntity = null;
        $redirectUrl = null;
        $categoryName = null;

        // Card Game
        if ($entity == "card_game") 
        {
            $categoryName = 'Collectible Card Games';
            $this->allowedFields = [
                'card_drop',
                'language',
                'card_manufacturer',
                'release_year'
            ];

            $this->mainEntity = 'card_game';
            $redirectUrl = '*/cardgamesdrop/index/'.$this->mainEntity.'/'.$categories;
        }

        return [
            'allowedFields' => $this->allowedFields,
            'mainEntity' => $this->mainEntity,
            'redirectUrl' => $redirectUrl,
            'categoryName' => $categoryName
        ];
    }

    private function releaseUploadData() 
    {
        $entity = $this->getRequest()->getParam('entity');
        $categories = $this->getRequest()->getParam('categories');

        // dd($categories);

        $this->allowedFields = [];
        $this->mainEntity = null;
        $redirectUrl = null;
        $categoryName = null;

        // Card Game
        if ($entity == "card_game") 
        {
            $categoryName = 'Collectible Card Games';
            $this->allowedFields = [
                'card_release',
                'language',
                'card_manufacturer',
                'release_year'
            ];

            $this->mainEntity = 'card_game';
            $redirectUrl = '*/cardgamesrelease/index/'.$this->mainEntity.'/'.$categories;
        }

        return [
            'allowedFields' => $this->allowedFields,
            'mainEntity' => $this->mainEntity,
            'redirectUrl' => $redirectUrl,
            'categoryName' => $categoryName
        ];
    }

    private function sealedProductsUploadData() 
    {
        $setId = $this->getRequest()->getParam('set_id');
        $entity = $this->getRequest()->getParam('entity');
        $categories = $this->getRequest()->getParam('categories');

        $this->allowedFields = [];
        $this->mainEntity = null;
        $redirectUrl = null;
        $categoryName = null;

        // Card Game
        if ($entity == "card_game") 
        {
            $categoryName = 'Collectible Card Games';
            $this->allowedFields = [
                "images",
                "card_release",
                "card_release_jp",
                "sealed_products",
                "name",
                "supplier_sku",
                "release_type",
                "release_date",
                "release_year",
                "card_print_version",
                "bar_code",
                "bar_code_no",
                "language"
            ];                
            
            if ($setId) {
                $this->mainEntity = 'card_game';
                $redirectUrl = '*/cardgamessets/index/'.$this->mainEntity.'/'.$setId;
            } else {
                $this->mainEntity = 'card_game';
                $redirectUrl = '*/cardgamessealedproducts/index/'.$this->mainEntity.'/'.$categories;

                // $this->mainEntity = 'card_drop';
                // $redirectUrl = '*/cardgamesdrop/index/card_game/' . $categories;
            }

            // dd($redirectUrl);
        }

        // Sport Card | Sealed Products
        if ($entity == "sport_card") {
            $categoryName = 'Sport Cards';
            $this->allowedFields = [
                'sc_categories',
                'sc_brand',
                'sc_brand_line',
                'sc_league',
                'sc_product_type',
                'sc_sealed_products',
                'sc_release',
                'sc_release_jp',
                'images',
                'name',
                'supplier_sku',
                'release_date',
                'release_year',
                'sc_card_manufacturer',
                'language',
                'bar_code',
                'bar_code_no',
                'age_level'
            ];       
            
            if ($setId) {
                $this->mainEntity = 'sc_categories';
                $redirectUrl = '*/sportcardssets/index/'.$this->mainEntity.'/'.$setId;
            } else {
                $this->mainEntity = 'sc_categories';
                $redirectUrl = '*/sportcardssealedproducts/index/'.$this->mainEntity.'/'.$categories;
            }
        }

        // Non Sport Card Sealed Products
        if ($entity == "non_sport_card") {
            $categoryName = 'Non Sports Cards';
            $this->allowedFields = [
                'nsc_categories',
                'nsc_release',
                'nsc_release_jp',
                'nsc_set_type',
                'nsc_set_name',
                'name',
                'supplier_sku',
                'nsc_feature',
                'nsc_brand',
                'release_date',
                'release_year',
                'nsc_card_manufacturer',
                'language',
                'bar_code',
                'bar_code_no',
                'age_level'
            ];       
            
            if ($setId) {
                $this->mainEntity = 'nsc_categories';
                $redirectUrl = '*/nonsportcardssets/index/'.$this->mainEntity.'/'.$setId;
            } else {
                $this->mainEntity = 'nsc_categories';
                $redirectUrl = '*/nonsportcardssealedproducts/index/'.$this->mainEntity.'/'.$categories;
            }
        }

        return [
            'allowedFields' => $this->allowedFields,
            'mainEntity' => $this->mainEntity,
            'redirectUrl' => $redirectUrl,
            'categoryName' => $categoryName
        ];
    }

    private function singlesUploadData() 
    {
        $setId = $this->getRequest()->getParam('set_id');
        $entity = $this->getRequest()->getParam('entity');
        $categories = $this->getRequest()->getParam('categories');

        $this->allowedFields = [];
        $this->mainEntity = null;
        $redirectUrl = null;
        $categoryName = null;

        // Action Toys
        if ($entity == "action_toys") 
        {
            $categoryName = 'Action Toys';
            $this->allowedFields = [
                'action_toys_set_name',
                'action_toys_set_item_number',
                'action_toys_singles_name',
                'action_toys_singles_item_number',
                'action_toys_release_type',
                'toys_line',
                'toys_subline',
                'action_toys_type',
                'toys_brand',
                'toys_character',
                'toys_franchise',
                'toys_series',
                'toys_scale',
                'toys_condition',
                'toys_feature',
                'toys_product_type',
                'toys_product_type_2',
                'toys_artist',
                'categories',
                'sub_categories',
                'release_year',
                'release_date',
                'material',
                'colour',
                'weight',
                'dimension',
                'height',
                'includes',
                'assembly_required',
                'age_level',
                'number_of_figures',
                'dimensions',
                'bar_code',
                'bar_code_no',
                'bar_code_2',
                'bar_code_no_2'
            ];            

            $this->mainEntity = 'set_id';
            $redirectUrl = '*/actiontoysingles/index/'.$this->mainEntity.'/'.$setId;
        }

        // Card Game Singles
        if ($entity == "card_game") 
        {
            $categoryName = 'Collectible Card Games';
            $this->allowedFields = [
                "images",
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

            $this->mainEntity = 'set_id';
            $redirectUrl = '*/cardgamessets/singles/'.$entity.'/'.$categories;
        }

        // Miniature Game Singles
        if ($entity == "miniature_game") 
        {
            $categoryName = 'Miniature Games';
            $this->allowedFields = [
                'figure_name',
                'figure_number',
                'character',
                'item_number',
                'release_year',
                'release_date',
                'miniature_manufacturer',
                'language',
                'miniature_sealed_products',
                'miniature_supplies',
                'height',
                'material',
                'age_level',
                'miniature_set_name',
                'miniature_set',
                'miniature_set_item_no',
                'miniature_figure_name',
                'miniature_figure_number',
                'miniature_toy_line',
                'miniature_franchise',
                'miniature_characters',
                'miniature_brand',
                'miniature_finish',
                'miniature_material',
                'miniature_scale',
                'miniature_faction',
            ];                

            $this->mainEntity = 'set_id';
            $redirectUrl = '*/miniaturegamesingles/index/'.$this->mainEntity.'/'.$setId;
        }
        
        // Sport Card | Singles
        if ($entity == "sport_card") 
        {
            $categoryName = 'Sport Cards';
            $this->allowedFields = [
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

            $this->mainEntity = 'set_id';
            $redirectUrl = '*/sportcardssetscard/index/'.$this->mainEntity.'/'.$setId;
        }

        // Non Sport Card | Singles
        if ($entity == "non_sport_card") 
        {
            $categoryName = 'Non Sports Cards';
            $this->allowedFields = [
                'images',
                'name',
                'supplier_sku',
                'nsc_card_name',
                'nsc_card_number',
                'sc_players',
                'sc_teams',
                'nsc_card_feature',
                'autographed',
                'serial_numbered',
                'graded',
                'nsc_card_condition',
                'item_number',
                'nsc_card_grade',
                'nsc_card_grader'
            ];              
            
            $this->mainEntity = 'set_id';
            $redirectUrl = '*/nonsportcardssetscard/index/'.$this->mainEntity.'/'.$setId;
        }

        // Dolls
        if ($entity == "dolls") 
        {
            $categoryName = 'Fashion Dolls';
            $this->allowedFields = [
                'dolls_set_name',
                'dolls_set_number',
                'release_year',
                'release_date',
                'age_level',
                'manufacturer',
                'dolls_brand',
                'material',
                'condition',
                'dolls_toy_line',
                'dolls_figures_name',
                'dolls_figures_number',
                'dolls_character',
                'dolls_franchise',
                'dolls_type',
                'dolls_feature',
                'dolls_series',
                'dolls_scale',
                'dolls_product_type'
            ];                            
            
            $this->mainEntity = 'set_id';
            $redirectUrl = '*/dollssingles/index/'.$this->mainEntity.'/'.$setId;
        }

        // Bricks
        if ($entity == "bricks_brand") 
        {
            $categoryName = 'Bricks';
            $this->allowedFields = [
                'release_year',
                'release_date',
                'age_level',
                'bricks_interest',
                'bricks_packaging',
                'bricks_features',
                'manufacturer',
                'brand',
                'material',
                'condition',
                'bricks_color',
                'bricks_complete_sets_and_packs',
                'bricks_minifigures',
                'bricks_minifigures_name',
                'bricks_minifigures_number',
                'bricks_character',
                'bricks_theme',
            ];                
            
            $this->mainEntity = 'set_id';
            $redirectUrl = '*/legosingles/index/'.$this->mainEntity.'/'.$setId;
        }

        return [
            'allowedFields' => $this->allowedFields,
            'mainEntity' => $this->mainEntity,
            'redirectUrl' => $redirectUrl,
            'categoryName' => $categoryName
        ];
    }

    public function processData($index, $columnName, $columnData, $columnMapping, $entity) 
    {
        $columnName = strtolower($columnName);
        $columnData = trim($columnData);

        if ($columnName == "images") {
            $newImagesArr = [];

            $imagesArr = explode(";", $columnData);
            if ($imagesArr) {
                // dd($newImagesArr);
                foreach($imagesArr as $imageNameWithExt) {
                    $imageNameWithExtArr = explode(".", $imageNameWithExt);
                    if ($imageNameWithExtArr) {
                        $imageName = $imageNameWithExtArr[0];
                        if ($imageName) {
                            $image = $this->getImageAssetByName($imageName);
                            if ($image) {
                                $newImagesArr[] = $image->getPath();
                            }
                        }
                    }
                }
            }

            return $newImagesArr ? implode(",", $newImagesArr) : '';
        }

        if ($columnName == "release_date") {
            $newformat = null;
            
            if ($columnData) {
                // $time = strtotime($columnData);
                // $newformat = date('d/m/Y', $time);
                
                $format = "d/m/Y";
                $d = \DateTime::createFromFormat($format, $columnData);
                if ($d && $d->format($format) != "01/01/1970") {
                    $newformat = $d->format($format);
                } else {
                    $dateObj = $this->parseDate($columnData);
                    if ($dateObj) {
                        $newformat = $dateObj->format('d/m/Y');
                    } else {
                        $this->messageManager->addWarning(__('Invalid date "%1" at row %2. Date format must be "dd/mm/yyyy".', $columnData, $index+1));
                    }
                }
            }

            return $newformat ?? '';
        }

        return $columnData;
    }

    public function parseDate($dateStr) {
            $parts = explode('/', $dateStr);
            if (count($parts) !== 3) return false;
        
            list($day, $month, $year) = $parts;
        
            // Kalau tahun 2 digit, anggap 2000-2099
            if (strlen($year) === 2) {
                $year = 2000 + (int)$year;
            }
        
            // Gabungkan kembali
            $normalized = sprintf('%02d/%02d/%04d', $day, $month, $year);
            $dateObj = \DateTime::createFromFormat('d/m/Y', $normalized);
        
            return $dateObj ?: false;        
    }

    public function getImageAssetByName($name) 
    {
        $filters[] = $this->filterBuilder
        ->setField('title')
        ->setConditionType('eq')
        ->setValue($name)
        ->create();

        $this->searchCriteriaBuilder->addFilters($filters);
        $searchCriteria = $this->searchCriteriaBuilder->create();
        $result = $this->searchAssets->execute($searchCriteria);

        return count($result) ? $result[0] : null;
    }
}