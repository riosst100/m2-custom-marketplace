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

class RunImport extends \TCGCollective\ProductAttributesLink\Controller\Adminhtml\Index
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
    protected $cardGamesReleaseFactory;
    protected $railwaySinglesFactory;
    protected $railwaySetsFactory;
    protected $productAttributesArr = [];

    protected $diecastSetsFactory;
    protected $diecastSinglesFactory;

    protected $modelkitsSetsFactory;
    protected $modelkitsSinglesFactory;
    
    protected $gamesWorkshopSetsFactory;
    protected $gamesWorkshopSinglesFactory;

    protected $platformDatabaseHelper;

    protected $mainCategoriesConfigProvider;
    
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
        \TCGCollective\PlatformDatabase\Helper\Data $platformDatabaseHelper,
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
        \TCGCollective\ProductAttributesLink\Model\RailwaySinglesFactory $railwaySinglesFactory,
        \TCGCollective\ProductAttributesLink\Model\RailwaySetsFactory $railwaySetsFactory,
        \TCGCollective\ProductAttributesLink\Model\DiecastSetsFactory $diecastSetsFactory,
        \TCGCollective\ProductAttributesLink\Model\DiecastSinglesFactory $diecastSinglesFactory,
        \TCGCollective\ProductAttributesLink\Model\ModelKitsSetsFactory $modelkitsSetsFactory,
        \TCGCollective\ProductAttributesLink\Model\ModelKitsSinglesFactory $modelkitsSinglesFactory,
        \TCGCollective\ProductAttributesLink\Model\GamesWorkshopSetsFactory $gamesWorkshopSetsFactory,
        \TCGCollective\ProductAttributesLink\Model\GamesWorkshopSinglesFactory $gamesWorkshopSinglesFactory,
        \TCGCollective\PlatformDatabase\Model\MainCategoriesConfigProvider $mainCategoriesConfigProvider
    ) {
        parent::__construct($context, $resultPageFactory, $registry);
        $this->platformDatabaseHelper = $platformDatabaseHelper;
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
        $this->railwaySinglesFactory = $railwaySinglesFactory;
        $this->railwaySetsFactory = $railwaySetsFactory;
        $this->diecastSetsFactory = $diecastSetsFactory;
        $this->diecastSinglesFactory = $diecastSinglesFactory;

        $this->modelkitsSetsFactory = $modelkitsSetsFactory;
        $this->modelkitsSinglesFactory = $modelkitsSinglesFactory;

        $this->gamesWorkshopSetsFactory = $gamesWorkshopSetsFactory;
        $this->gamesWorkshopSinglesFactory = $gamesWorkshopSinglesFactory;

        $this->mainCategoriesConfigProvider = $mainCategoriesConfigProvider;
    }

    // admin - custom attributes upload process
    public function processUploadCustomAttributes($f_object, $columnMapping) 
    {
        $categoryId = $this->getRequest()->getParam('category_id');
        
        $countAdded = 0;
        $countUpdated = 0;
        $totalRow = 0;

        while (($dataColumns = fgetcsv($f_object)) !== FALSE) {
            // Skip empty column
            $allEmpty = count(array_filter($dataColumns, fn($v) => $v !== "")) === 0;
            if ($allEmpty) {
                continue;
            }

            $totalRow++;

            $data = [];

            // Process column data
            foreach($columnMapping as $columnName => $index) {
                if (!isset($dataColumns[$index])) {
                    continue;
                }
                
                $columnName = strtolower($columnName);

                // Process data
                $processedData = $this->processData(
                    $totalRow,
                    $columnName,
                    $dataColumns[$index],
                    $columnMapping
                );

                if ($processedData == 'error') {
                    return $this->_redirect($this->_redirect->getRefererUrl());
                }

                // Update column data
                $data[$columnName] = $processedData;
            }

            if ($data) {
                // $collection = $this->customAttributesFactory->create()->getCollection();
                $mainCategoriesConfigData = $this->mainCategoriesConfigProvider->getByCategoryId($categoryId);
                $customAttributesTableName = $mainCategoriesConfigData['customAttributesTableName'] ?? null;
                $collection = $this->mainCategoriesConfigProvider->getCustomTableCollection($customAttributesTableName);
                
                // dd($collection);
                $mainAttrCode = $mainCategoriesConfigData['mainAttrCode'] ?? null;

                $prefix = null;
                 
                $attrCode = $data['attribute_code'] ?? null;

                $prefixArr = explode("_", $attrCode);
                if (count($prefixArr)) {
                    $prefix = $prefixArr[0] ?? null;
                }
                // Get all prefixes from config
                $allPrefixConfigValueArrRaw = $this->mappingHelper->getAllPrefixes();
                if (!$allPrefixConfigValueArrRaw) {
                    continue;
                }

                $mainAttrOptionLabel = $this->getMainAttrLabelByPrefix($allPrefixConfigValueArrRaw, $prefix);

                $mainAttrOptionId = 0;
                if ($mainAttrOptionLabel) {
                    $mainAttrOptionId = $this->mappingHelper->getOptionId($mainAttrCode, $mainAttrOptionLabel);
                }
                
                $linkedAttrCode = $data['linked_attribute_code'] ?? null;
                $linkedAttrValue = $data['linked_attribute_value'] ?? null;

                $uniqueKey = md5(
                    $mainAttrCode . '_' . 
                    $mainAttrOptionId . '_' . 
                    $data['attribute_code'] . '_' . 
                    $data['attribute_value'] . '_' . 
                    $linkedAttrCode . '_' . 
                    $linkedAttrValue . '_'
                );

                $data['unique_key'] = $uniqueKey;
                $data[$mainAttrCode] = $mainAttrOptionId;
                // $data['category_id'] = $categoryId;

                $collection->addFieldToFilter('unique_key', $data['unique_key']);

                // Updated or add new
                if ($collection->getSize()) {
                    $data['entity_id'] = $collection->getFirstItem()->getId();
                    
                    $countUpdated++;
                } else {
                    unset($data['entity_id']);

                    $countAdded++;
                }

                $model = $collection->getFirstItem();
                
                try {
                    // try {
                    //     $this->mappingHelper->getOrInputAttributeOptionId($data['attribute_code'], $data['attribute_value']);
                    // } catch(\Exception $e) {}

                    $model->setData($data);
                    $model->save();
                } catch (\Exception $e) {   
                    dd($e);
                }
            }
        }

        if ($countAdded > 0) {
            $this->messageManager->addSuccess(__('Added %1 of %2 record(s).', $countAdded, $totalRow));
        }

        if ($countUpdated > 0) {
            $this->messageManager->addSuccess(__('Updated %1 of %2 record(s).', $countUpdated, $totalRow));
        }

        if (!$countAdded && !$countUpdated) {
            $this->messageManager->addSuccess(__('No uploaded data.'));
        }

        return $this->_redirect($this->_redirect->getRefererUrl());
    }

    public function getMainAttrLabelByPrefix(array $data, string $prefix): ?string {
        foreach ($data as $item) {
            if (isset($item['prefix']) && $item['prefix'] === $prefix) {
                return $item['main_attr_label'] ?? null;
            }
        }
        return null;
    }

    // admin - custom attributes upload process
    public function processUpload($f_object, $columnMapping) 
    {
        // dd($this->getRequest()->getParams());
        $categoryId = $this->getRequest()->getParam('category_id');
        $importType = $this->getRequest()->getParam('import_type');
        $paramMainAttrOptionId = $this->getRequest()->getParam('categories');
        
        $countAdded = 0;
        $countUpdated = 0;
        $totalRow = 0;

        $mainCategoriesConfigData = $this->mainCategoriesConfigProvider->getByCategoryId($categoryId);
        if (!$mainCategoriesConfigData) {
            die('mainCategoriesConfigData not found.');
        }
        
        $tableName = null;
        
        if ($importType == "sets") {
            $tableName = $mainCategoriesConfigData['setsTableName'] ?? null;
        }

        if ($importType == "sealed_products") {
            $tableName = $mainCategoriesConfigData['sealedProductsTableName'] ?? null;
        }

        if ($importType == "singles") {
            $tableName = $mainCategoriesConfigData['singlesTableName'] ?? null;
        }

        if (!$tableName) {
            die('tableName not found.');
        }

        $setsTableName = $mainCategoriesConfigData['setsTableName'] ?? null;

        $collection = $this->mainCategoriesConfigProvider->getCustomTableCollection($tableName);
        if (!$collection) {
            die('collection not found.');
        }
        
        $mainAttrCode = $mainCategoriesConfigData['mainAttrCode'] ?? null;
        if (!$mainAttrCode) {
            die('mainAttrCode not found.');
        }

        $mainSetsAttrCode = $mainCategoriesConfigData['mainSetsAttrCode'] ?? null;
        if (!$mainSetsAttrCode) {
            die('mainSetsAttrCode not found.');
        }

        $mainAttrLabel = null;
        $prefix = null;
        $dataSyncToCustomAttributes = [];

        $categoryName = null;
        $category = $this->mappingHelper->getCategoryById($categoryId);
        if ($category) {
            $categoryName = $category->getName();
        }

        // Foreach columns
        while (($dataColumns = fgetcsv($f_object)) !== FALSE) {
            $allEmpty = count(array_filter($dataColumns, fn($v) => $v !== "")) === 0;
            if ($allEmpty) {
                continue;
            }

            // Get main attribute val & label
            if (!$mainAttrLabel) {                                    
                $mainAttrOptionId = $dataColumns[$columnMapping[$mainAttrCode]] ?? null;
                if ($mainAttrOptionId) {
                    if (is_numeric($mainAttrOptionId)) {
                        $mainAttrLabel = $this->mappingHelper->getOptionLabelById(
                            $mainAttrCode,
                            $mainAttrOptionId
                        );
                    } else {
                        $mainAttrLabel = $mainAttrOptionId;
                        $mainAttrOptionId = $this->mappingHelper->getOptionId($mainAttrCode, $mainAttrLabel);
                    }
                } else {
                    $mainAttrLabel = $this->mappingHelper->getOptionLabelById(
                        $mainAttrCode,
                        $paramMainAttrOptionId
                    );
                    $mainAttrOptionId = $paramMainAttrOptionId;
                }
            }

            // Get attribute prefix
            if (!$prefix && $mainAttrLabel) {
                $prefix = $this->mappingHelper->getPrefixByCategoryId(
                    $categoryId,
                    $mainAttrLabel
                );
            }

            $totalRow++;

            $data = [];
            foreach($columnMapping as $columnName => $index) {
                if (!isset($dataColumns[$index])) {
                    continue;
                }

                $columnName = strtolower($columnName);

                // Process data
                $processedData = $this->processData(
                    $totalRow,
                    $columnName,
                    $dataColumns[$index],
                    $columnMapping
                );

                if ($processedData == 'error') {
                    return $this->_redirect($this->_redirect->getRefererUrl());
                }

                // Update column data
                $data[$columnName] = $processedData;

                // Sync data to custom attributes
                if ($processedData) {
                    $attributeCode = $this->getProductAttributeCode(
                        $columnName, 
                        $prefix
                    );

                    if ($attributeCode && $processedData) {
                        $linkedAttrCode = null;
                        $linkedAttrValue = null;

                        $uniqueKey = md5(
                            $mainAttrCode . '_' . 
                            $mainAttrOptionId . '_' . 
                            $attributeCode . '_' . 
                            $processedData . '_' . 
                            $linkedAttrCode . '_' . 
                            $linkedAttrValue . '_'
                        );

                        if (!isset($dataSyncToCustomAttributes[$uniqueKey])) {
                            $customAttributes = [
                                'attribute_code' => $attributeCode, 
                                'attribute_value' => $processedData,
                                'unique_key' => $uniqueKey,
                                $mainAttrCode => str_contains($attributeCode, $prefix) ? $mainAttrOptionId : 0
                            ];

                            $dataSyncToCustomAttributes[$uniqueKey] = $customAttributes;
                        }
                    }
                }
            }

            if ($importType == "singles") {
                $data['card_release'] = null;
            }

            // Set main attr val
            $data[$mainAttrCode] = $mainAttrOptionId;

            // Save process
            $setId = null;

            if ($importType != "sets") {
                $sets = $data[$mainSetsAttrCode] ?? null;
                if ($sets) {
                    $setsCollection = $this->mainCategoriesConfigProvider->getCustomTableCollection($setsTableName);
                    if (!$setsCollection) {
                        die('collection not found.');
                    }

                    $setsCollection->addFieldToFilter($mainSetsAttrCode, $sets);
                    if ($setsCollection->getSize()) {
                        $model = $setsCollection->getFirstItem();
                        if ($model) {
                            $setId = $model->getId();
                        }
                    }
                }
            }

            // COMPARE FIELDS ON CSV WITH FIELDS ON ATTRIBUTE SET
            $dataToCompare = $data;
            // if (!$this->platformDatabaseHelper->compareCustomTableCsvWithAttributeSet($dataToCompare)) {
            //     return $this->_redirect($this->_redirect->getRefererUrl());
            //     break;
            // }

            if (isset($data['entity_id']) && $data['entity_id']) {
                $collection->addFieldToFilter('entity_id', $data['entity_id']);

                $countUpdated++;
            } else {
                $collection->addFieldToFilter($mainAttrCode, $mainAttrOptionId);

                $countAdded++;
            }

            if ($importType == 'singles') {
                $data['set_id'] = $setId;
            }

            $model = $collection->getFirstItem();
            if ($model) {
                $model->setData($data);
                $model->save();

                // Clear Platform Database listing cache
                $this->platformDatabaseHelper->removePlatformDatabaseCache($categoryName, $importType);
            }
        }

        if ($dataSyncToCustomAttributes) {
            $customAttributesTableName = $mainCategoriesConfigData['customAttributesTableName'] ?? null;
            $this->platformDatabaseHelper->syncToCustomAttributes($dataSyncToCustomAttributes, $customAttributesTableName);
        }

        if ($countAdded > 0) {
            $this->messageManager->addSuccess(__('Added %1 of %2 record(s).', $countAdded, $totalRow));
        }

        if ($countUpdated > 0) {
            $this->messageManager->addSuccess(__('Updated %1 of %2 record(s).', $countUpdated, $totalRow));
        }

        if (!$countAdded && !$countUpdated) {
            $this->messageManager->addSuccess(__('No uploaded data.'));
        }

        // // Save history
        // $historyData = [
        //     'upload_type' => $importType,
        //     'categories' => $categoryId,
        //     'file_path' => $filePath,
        //     'total_row' => $totalRow,
        //     'total_row_added' => $countAdded,
        //     'total_row_updated' => $countUpdated,
        //     'messages' => 'success',
        //     'status' => 1,
        //     'user_id' => $this->mappingHelper->getAdminId(),
        //     'entity' => $this->mappingHelper->getOptionLabelById($entity, $categoriesData)
        // ];

        // $history = $this->uploadHistoryFactory->create()
        // ->setData($historyData)
        // ->save();

        return $this->_redirect($this->_redirect->getRefererUrl());
    }

    public function getProductAttributeCode(
        $columnName, 
        $prefix
    ) {
        if ($columnName && $prefix) {
            $productAttributesArr = $this->getProductAttributes();
            if ($productAttributesArr) {
                // dd($prefix . $columnName);
                if (in_array($prefix . $columnName, $productAttributesArr)) {
                    // dd($prefix . $columnName);
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

    public function execute()
    {
        $setId = $this->getRequest()->getParam('set_id');
        $categoriesData = $this->getRequest()->getParam('categories');
        $entity = $this->getRequest()->getParam('entity') ?? $categoriesData;
        $categoryId = $this->getRequest()->getParam('entity') ?? null;
        $importType = $this->getRequest()->getParam('import_type');

        if ((isset($_FILES['data']['name'])) && ($_FILES['data']['name'] != '')) 
        {
            try {
                $uploaderFactory = $this->uploaderFactory->create(['fileId' => 'data']);
                $uploaderFactory->setAllowedExtensions(['csv']);
                $uploaderFactory->setAllowRenameFiles(true);
                $uploaderFactory->setFilesDispersion(true);

                $mediaDirectory = $this->fileSystem->getDirectoryRead(DirectoryList::MEDIA);
                $destinationPath = $mediaDirectory->getAbsolutePath('custom_table/upload/' . $importType);

                $result = $uploaderFactory->save($destinationPath);

                if (!$result) {
                    throw new LocalizedException
                    (
                    __('File cannot be saved to path: $1', $destinationPath)
                    );

                } else {   
                    $filePath = 'custom_table/upload/' . $importType . $result['file'];
                    $mediaDirectory = $this->fileSystem->getDirectoryRead(DirectoryList::MEDIA);
                    $destinationfilePath = $mediaDirectory->getAbsolutePath($filePath);
                    $f_object = fopen($destinationfilePath, "r");
                    $column = fgetcsv($f_object);

                    $columnMapping = [];
                    foreach($column as $index => $columnName) {
                        if ($columnName) {
                            $columnName = strtolower($columnName);
                            $columnName = strtolower(preg_replace('/[^a-zA-Z0-9_]/', '', $columnName));
                            $columnMapping[$columnName] = $index;
                        }
                    }

                    if($f_object) {

                        // Upload custom attributes
                        if ($importType == "attributes") {
                            return $this->processUploadCustomAttributes($f_object, $columnMapping);
                        }

                        // Upload custom attributes
                        return $this->processUpload($f_object, $columnMapping);
                    } else {
                        $this->messageManager->addError(__("File hase been empty"));
                    }
                }              
            } catch (\Exception $e) {
                $this->messageManager->addError(__($e->getMessage()));
            }
        } else {
            $this->messageManager->addError(__("Please try again."));
        }

        // dd('DONE');

        return $this->_redirect($this->_redirect->getRefererUrl());
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

    private function getUploadData() 
    {
        $importType = $this->getRequest()->getParam('import_type');
        if ($importType) {
            // SETS UPLOAD DATA
            if ($importType == "sets") {
                return $this->setsUploadData();
            }
            
            // SINGLES UPLOAD DATA
            if ($importType == "singles") {
                return $this->singlesUploadData();
            }

            // SEALED PRODUCTS UPLOAD DATA
            if ($importType == "sealed_products") {
                return $this->sealedProductsUploadData();
            }
        }

        return [];
    }

    private function setsUploadData() 
    {
        $categoriesData = $this->getRequest()->getParam('categories');
        $entity = $this->getRequest()->getParam('entity');

        $allowedFields = [];
        $mainEntity = null;
        $redirectUrl = null;
        $model = null;

        // Action Toys
        if ($entity == "actiontoys") 
        {
            $allowedFields = [
                'images',
                'toys_brand',
                'toys_categories',
                'toys_product_type',
                'toys_condition',
                'toys_packaging',
                'toys_scale',
                'brands_toys_line',
                'brands_toys_sub_line',
                'brands_set_name',
                'brands_set_item_number',
                'brands_singles_set_name',
                'brands_singles_item_number',
                'action_toys_categories',
                'action_toys_props_replica_categories',
                'toys_type',
                'toys_franchise',
                'toys_series',
                'toys_characters',
                'toys_release_type',
                'release_year',
                'release_date',
                'toys_manufacturer',
                'material',
                'colour',
                'weight',
                'dimension',
                'item_height',
                'bar_code',
                'bar_code_no',
                'toys_feature',
                'includes',
                'edition',
                'edition_size',
                'assembly_required',
                'age_level',
                'sku',
                'supplier_sku',
                'name',
                'description'
            ];

            $mainEntity = 'toys_brand';
            $redirectUrl = '*/actiontoysets/index/'.$mainEntity.'/'.$categoriesData;
        }

        // Action Toys
        if ($entity == "anime") 
        {
            $allowedFields = [
                'images',
                'toys_brand',
                'toys_sub_brand',
                'toys_product_type',
                'toys_line',
                'toys_sub_line',
                'toys_condition',
                'toys_packaging',
                'toys_scale',
                'anime_toys_categories',
                'anime_figures_categories',
                'anime_toys_props_replica_categories',
                'set_name',
                'set_item_number',
                'singles_item_number',
                'singles_set_name',
                'toys_franchise',
                'toys_series',
                'toys_characters',
                'toys_release_type',
                'release_year',
                'release_date',
                'material',
                'anime_toys_type',
                'colour',
                'weight',
                'dimension',
                'item_height',
                'bar_code',
                'bar_code_no',
                'features',
                'includes',
                'edition',
                'edition_size',
                'assembly_required',
                'age_level',
                'number_of_figures',
                'toys_manufacturer',
                'sku',
                'supplier_sku',
                'name',
                'description'
            ];
            $mainEntity = 'toys_brand';
            $redirectUrl = '*/animesets/index/'.$mainEntity.'/'.$categoriesData;
        }

        // Card Game Sets
        if ($entity == "cardgame") 
        {
            $allowedFields = [
                'images',
                'card_game',
                'card_manufacturer',
                'card_product_type',
                'pre_order_card_product_type',
                'card_print_version',
                'card_condition',
                'language',
                'release_date',
                'release_year',
                'age_level',
                'number_of_cards',
                'card_supplies',
                'card_action_point_cost',
                'card_activation_energy',
                'card_affinities',
                'card_affiliation',
                'card_arts_1',
                'card_arts_2',
                'card_arena_type',
                'card_artist',
                'card_aspect',
                'card_attack',
                'card_attack_01',
                'card_attack_02',
                'card_attribute',
                'card_attributes',
                'card_battle_point',
                'card_baton_pass',
                'card_bloom_level',
                'card_category',
                'card_character',
                'card_character_traits',
                'card_class',
                'card_clan',
                'card_color',
                'card_combo_energy',
                'card_combo_power',
                'card_combo_value',
                'card_cost',
                'card_cost_ink',
                'card_counter',
                'card_counter_power',
                'card_critical',
                'card_defence',
                'card_defense_value',
                'card_description',
                'card_die_values',
                'card_digimon_attribute',
                'card_digimon_form',
                'card_digimon_powerdp',
                'card_digimon_type',
                'card_digivolve1color',
                'card_digivolve1cost',
                'card_digivolve1level',
                'card_digivolve2color',
                'card_digivolve2cost',
                'card_digivolve2level',
                'card_digivolve3level',
                'card_element',
                'card_energy_color_cost',
                'card_energy_type',
                'card_epic_action',
                'card_feature',
                'card_flavor',
                'card_flavor_text',
                'card_foil',
                'card_franchise',
                'card_generated_energy',
                'card_grade',
                'card_grow_cost',
                'card_health',
                'card_hit_points',
                'card_hp',
                'card_illustrator',
                'card_ink_type',
                'card_inkwell_icon',
                'card_intellect',
                'card_invade',
                'card_job',
                'card_keyword',
                'card_level',
                'card_limit',
                'card_link_arrows',
                'card_link_condition',
                'card_link_rating',
                'card_life',
                'card_lore_value',
                'card_lvicost',
                'card_lvibp',
                'card_lviicost',
                'card_lviibp',
                'card_lviiicost',
                'card_lviiibp',
                'card_monster_type',
                'card_name',
                'card_name_cn',
                'card_name_jp',
                'card_nation',
                'card_number',
                'card_oshi_skill',
                'card_pitch_value',
                'card_play_cost',
                'card_point_values',
                'card_power',
                'card_race',
                'card_rarity',
                'card_rank',
                'card_reduction_symbols',
                'card_release',
                'card_release_cn',
                'card_release_jp',
                'card_resistance',
                'card_rig_type',
                'card_security_effect',
                'card_series',
                'card_set',
                'card_set_abbreviation',
                'card_set_cn',
                'card_set_jp',
                'card_shield',
                'card_skill',
                'card_skillI',
                'card_soul',
                'card_special_rules',
                'card_special_trait',
                'card_spirit_type',
                'card_stage',
                'card_strenght',
                'card_sub_type',
                'card_symbol',
                'card_symbols',
                'card_tags',
                'card_talent',
                'card_team_name',
                'card_threat_level',
                'card_timing',
                'card_token_ability',
                'card_traits',
                'card_trait',
                'card_trigger',
                'card_toughness',
                'card_type',
                'card_type_B',
                'card_unit',
                'card_uniqueness',
                'card_willpower',
                'card_weakness',
                'category',
                'format',
                'release_type',
                'sealed_products',
                'sku',
                'supplier_sku'
                ];

            $mainEntity = 'card_game';
            $redirectUrl = '*/cardgamessets/index/'.$mainEntity.'/'.$categoriesData;
        }

        // Miniature Game Sets
        if ($entity == "miniaturegame") 
        {
            $allowedFields = [
                'images',
                'miniature_set_name',
                'miniature_set_abbreviation',
                'miniature_set_universe',
                'miniature_set_series',
                'miniature_faction',
                'miniature_product_type',
                'miniature_game',
                'miniature_brand',
                'miniature_scale',
                'miniature_finish',
                'miniature_material',
                'miniature_number_of_figures',
                'miniature_manufacturer',
                'release_year',
                'release_date',
                'colour',
                'weight',
                'dimension',
                'item_height',
                'features',
                'includes',
                'edition',
                'edition_size',
                'assembly_required',
                'age_level',
                'number_of_figures',
                'name',
                'sku',
                'supplier_sku',
                'bar_code',
                'bar_code_no'
            ];

            $mainEntity = 'miniature_game';
            $redirectUrl = '*/miniaturegamesets/index/'.$mainEntity.'/'.$categoriesData;
        }

        // Miniature Game Sets
        // if ($entity == "miniature_game") 
        // {
        //     $allowedFields = [
        //         'images',
        //     ];

        //     $mainEntity = 'miniature_game';
        //     $redirectUrl = '*/miniaturegamesets/index/'.$mainEntity.'/'.$categoriesData;
        // }
        if ($entity == "gamesworkshop") 
        {
            $allowedFields = [
                "images",
                "gw_miniature_categories",
                "miniature_set",
                "miniature_set_item_no",
                "miniature_faction",
                "miniature_sub_faction",
                "miniature_armies",
                "miniature_battlefield_role",
                "miniature_figure_name",
                "miniature_figure_item_no",
                "miniature_accessories",
                "miniature_brand",
                "gw_miniature_game",
                "miniature_finish",
                "miniature_material",
                "miniature_scale",
                "miniature_scenery_type",
                "miniature_dice_type",
                "miniature_dice_colour",
                "miniature_manufacturer",
                "miniature_number_of_figures",
                "release_year",
                "release_date",
                "language",
                "name",
                "sku",
                "supplier_sku",
                "weight",
                "dimension",
                "item_height",
                "bar_code",
                "bar_code_no",
                "features",
                "includes",
                "edition",
                "edition_size",
                "assembly_required",
                "age_level",
                "description"
            ];

            $mainEntity = 'gw_miniature_game';
            $redirectUrl = '*/gamesworkshopsets/index/'.$mainEntity.'/'.$categoriesData;
        }

        // Living Card Game
        if ($entity == "livingcardgame") 
        {
            $allowedFields = [
                'images',
                'living_card_game',
                'living_card_release',
                'living_card_series',
                'living_card_set',
                'living_card_set_abbreviation',
                'living_card_set_type',
                'living_card_name',
                'living_card_number',
                'living_card_rarity',
                'living_card_foil',
                'living_card_description',
                'living_card_flavor_text',
                'living_card_feature',
                'living_card_type',
                'living_card_sub_type',
                'living_card_color',
                'living_card_cost',
                'living_card_affiliation',
                'living_card_uniqueness',
                'living_card_die_values',
                'living_card_point_values',
                'living_card_attribute_type',
                'living_card_attribute_cost',
                'living_card_health',
                'card_product_type',
                'card_game',
                'card_manufacturer',
                'card_print_version',
                'card_condition',
                'language',
                'release_date',
                'release_year',
                'age_level',
                'sku',
                'supplier_sku',
                'name',
                'description'
            ];

            $mainEntity = 'living_card_game';
            $redirectUrl = '*/livingcardgamesets/index/'.$mainEntity.'/'.$categoriesData;
        }

        // Sport Card | Sets Upload
        if ($entity == "sportcard") 
        {
            $allowedFields = [
                "sc_league",
                "sc_release",
                "sc_set_name",
                "sc_set_abbreviation",
                "sc_brand",
                "sc_brand_line",
                "sc_card_name",
                "sc_card_number",
                "sc_players",
                "sc_teams",
                "sc_card_feature",
                "rookie_card",
                "autographed",
                "serial_numbered",
                "sc_season",
                "name",
                "sc_categories",
                "sc_product_type",
                "sc_set_type",
                "sc_brand",
                "sc_card_manufacturer",
                "release_date",
                "release_year",
                "sc_condition",
                "language",
                "age_level",
                "images",
                "supplier_sku"
            ];

            $mainEntity = 'sc_categories';
            $redirectUrl = '*/sportcardssets/index/'.$mainEntity.'/'.$categoriesData;
        }

        // Non Sport Card | Sets
        if ($entity == "nonsportcard") 
        {
            $allowedFields = [
                "name",
                "nsc_categories",
                "nsc_product_type",
                "nsc_release",
                "nsc_set_name",
                "nsc_set_type",
                "nsc_item_number",
                "nsc_card_name",
                "nsc_card_number",
                "nsc_feature",
                "autographed",
                "serial_numbered",
                "graded",
                "nsc_brand",
                "nsc_brand_line",
                "nsc_series",
                "nsc_character",
                "nsc_franchise",
                "sc_card_condition",
                "sc_card_manufacturer",
                "sc_card_grade",
                "sc_card_grader",
                "release_date",
                "release_year",
                "language",
                "bar_code",
                "bar_code_no",
                "age_level",
                "number_of_cards",
                "images",
                "supplier_sku"
            ];
            
            $mainEntity = 'nsc_categories';
            $redirectUrl = '*/nonsportcardssets/index/'.$mainEntity.'/'.$categoriesData;
        }

        // Dolls
        if ($entity == "dolls") 
        {
            $allowedFields = [
                'images',
                'toys_brand',
                'toys_sub_brand',
                'toys_product_type',
                'toys_line',
                'toys_sub_line',
                'toys_condition',
                'dolls_scale',
                'dolls_fashion_dolls_categories',
                'dolls_fashion_categories',
                'dolls_parts_categories',
                'dolls_accessories_categories',
                'dolls_set_name',
                'dolls_set_item_number',
                'dolls_singles_name',
                'dolls_singles_item_number',
                'dolls_franchise',
                'dolls_characters',
                'dolls_series',
                'dolls_type',
                'dolls_complexion',
                'dolls_eye_color',
                'dolls_hair_colour',
                'dolls_hair_type',
                'dolls_feature',
                'release_year',
                'release_date',
                'material',
                'colour',
                'weight',
                'dimension',
                'item_height',
                'bar_code',
                'bar_code_no',
                'features',
                'includes',
                'edition',
                'edition_size',
                'assembly_required',
                'age_level',
                'number_of_figures',
                'manufacturer',
                'sku',
                'supplier_sku',
                'name',
                'description'
            ];            
            
            $mainEntity = 'toys_brand';
            $redirectUrl = '*/dollssets/index/'.$mainEntity.'/'.$categoriesData;
        }

        // Bricks
        if ($entity == "bricks") 
        {
            $allowedFields = [
                'images',
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
            
            $mainEntity = 'bricks_brand';
            $redirectUrl = '*/legosets/index/'.$mainEntity.'/'.$categoriesData;
        }

        // Model Kits
        if ($entity == "diecast") 
        {
            $allowedFields = [
                "images",
                "diecast_brand",
                "diecast_product_type",
                "diecast_line",
                "diecast_sub_line",
                "toys_condition",
                "diecast_scale",
                "vehicle_set_name",
                "vehicle_set_item_number",
                "vehicle_singles_name",
                "vehicle_singles_item_number",
                "bikes_set_name",
                "bikes_set_item_number",
                "bikes_singles_name",
                "bikes_singles_item_number",
                "vehicle_line",
                "vehicle_series",
                "vehicle_make",
                "vehicle_model",
                "vehicle_colour",
                "vehicle_year",
                "vehicle_racing_teams",
                "vehicle_racing_drivers",
                "vehicle_races",
                "vehicle_brands",
                "diecast_manufacturer",
                "aircraft_make",
                "aircraft_model",
                "aircraft_set_name",
                "aircraft_set_item_number",
                "aircraft_singles_name",
                "aircraft_singles_item_number",
                "military_vehicles_set_name",
                "military_vehicles_set_item_number",
                "military_vehicles_singles_name",
                "military_vehicles_singles_item_number",
                "military_aircraft_set_name",
                "military_aircraft_set_item_number",
                "military_aircraft_singles_name",
                "military_aircraft_singles_item_number",
                "military_tanks_set_name",
                "military_tanks_set_item_number",
                "military_tanks_singles_name",
                "military_tanks_singles_item_number",
                "military_ships_set_name",
                "military_ships_set_item_number",
                "military_ships_singles_name",
                "military_ships_singles_item_number",
                "military_artillery_set_name",
                "military_artillery_set_item_number",
                "military_artillery_singles_name",
                "military_artillery_singles_item_number",
                "military_submarines_set_name",
                "military_submarines_set_item_number",
                "military_submarines_singles_name",
                "military_submarines_singles_item_number",
                "vehicle_scale",
                "release_year",
                "release_date",
                "material",
                "weight",
                "dimension",
                "item_height",
                "bar_code",
                "bar_code_no",
                "features",
                "includes",
                "edition",
                "edition_size",
                "assembly_required",
                "age_level",
                "manufacturer"
            ];
            
            $mainEntity = 'diecast_brand';
            $redirectUrl = '*/diecastsets/index/'.$mainEntity.'/'.$categoriesData;
        }

        // Model Kits
        if ($entity == "modelkits") 
        {
            $allowedFields = [
                'images',
                'model_kits_brand',
                'model_kits_product_type',
                'model_kits_line',
                'model_kits_sub_line',
                'toys_condition',
                'model_kits_scale',
                'model_kits_vehicles_categories',
                'model_kits_bikes_categories',
                'model_kits_aircrafts_categories',
                'model_kits_military_vehicles_categories',
                'model_kits_accessories_categories',
                'vehicle_kits_set_name',
                'vehicle_kits_set_item_number',
                'bikes_kits_set_name',
                'bikes_kits_set_item_number',
                'vehicle_line',
                'vehicle_series',
                'vehicle_make',
                'vehicle_model',
                'vehicle_colour',
                'vehicle_year',
                'vehicle_racing_teams',
                'vehicle_racing_drivers',
                'vehicle_races',
                'vehicle_brands',
                'vehicle_manufacturer',
                'aircraft_make',
                'aircraft_model',
                'aircraft_kits_set_name',
                'aircraft_kits_set_item_number',
                'military_vehicles_kits_set_name',
                'military_vehicles_kits_set_item_number',
                'military_aircraft_kits_sets_name',
                'military_aircraft_kits_sets_item_number',
                'military_tanks_kits_sets_name',
                'military_tanks_kits_sets_item_number',
                'military_ships_kits_sets_name',
                'military_ships_kits_sets_item_number',
                'military_artillery_kits_sets_name',
                'military_artillery_kits_sets_item_number',
                'military_submarines_kits_sets_name',
                'military_submarines_kits_sets_item_number',
                'release_year',
                'release_date',
                'material',
                'weight',
                'dimension',
                'item_height',
                'bar_code',
                'bar_code_no',
                'features',
                'includes',
                'edition',
                'edition_size',
                'assembly_required',
                'age_level',
                'manufacturer'
            ];
            
            $mainEntity = 'model_kits_brand';
            $redirectUrl = '*/modelkitssets/index/'.$mainEntity.'/'.$categoriesData;
        }

        // Bricks
        if ($entity == "trains") 
        {
            $allowedFields = [
                'images',
                'railway_brand',
                'railway_toy_line',
                'railway_categories',
                'railway_accessories_categories',
                'railway_product_type',
                'railway_set_name',
                'railway_catalog_number',
                'railway_line',
                'railway_series',
                'railway_guage',
                'railway_era',
                'railway_road_names',
                'railway_dcc_fittings',
                'railway_decorative_details',
                'condition',
                'release_year',
                'release_date',
                'material',
                'colour',
                'weight',
                'dimension',
                'item_height',
                'bar_code',
                'bar_code_no',
                'features',
                'includes',
                'edition',
                'edition_size',
                'assembly_required',
                'age_level',
                'supplier_sku',
                'train_manufacturer'
            ];
            
            $mainEntity = 'railway_brand';
            $redirectUrl = '*/trainssets/index/'.$mainEntity.'/'.$categoriesData;
        }

        return [
            'allowedFields' => $allowedFields,
            'mainEntity' => $mainEntity,
            'redirectUrl' => $redirectUrl,
            'model' => $model
        ];
    }

    public function getCategoryName() {
        $entity = $this->getRequest()->getParam('entity');
        if (!$entity) {
            $entity = $this->getRequest()->getParam('categories');
        }
        $categoryName = null;

        // Action Toys
        if ($entity == "actiontoys") 
        {
            $categoryName = 'Action Toys';
        }

        if ($entity == "actiontoysmodelkits") 
        {
            $categoryName = 'Action Toys Model Kits';
        }

        // Action Toys
        if ($entity == "anime") 
        {
            $categoryName = 'Anime';
        }

        if ($entity == "animemodelkits") 
        {
            $categoryName = 'Anime Model Kits';
        }

        if ($entity == "gundam") 
        {
            $categoryName = 'Gundam';
        }

        if ($entity == "fungoods") 
        {
            $categoryName = 'Fun Goods';
        }

        // Card Game Singles
        if ($entity == "cardgame") 
        {
            $categoryName = 'Collectible Card Games';
        }

        // Miniature Game Singles
        if ($entity == "livingcardgame") 
        {
            $categoryName = 'Living Card Games';
        }

        // Miniature Game Singles
        if ($entity == "miniaturegame") 
        {
            $categoryName = 'Miniature Games';
        }

        if ($entity == "gamesworkshop") 
        {
            $categoryName = 'Games Workshop';
        }
        
        // Sport Card Singles
        if ($entity == "sportcard") 
        {
            $categoryName = 'Sport Cards';
        }

        // Non Sport Card
        if ($entity == "nonsportcard") 
        {
            $categoryName = 'Non Sports Cards';
        }

        // Dolls
        if ($entity == "dolls") 
        {
            $categoryName = 'Dolls';
        }

        // Bricks
        if ($entity == "bricks") 
        {
            $categoryName = 'Bricks';
        }

        // Diecast
        if ($entity == "diecast") 
        {
            $categoryName = 'Diecast';
        }

        // Model Kits
        if ($entity == "modelkits") 
        {
            $categoryName = 'Model Kits';
        }

        // Bricks
        if ($entity == "trains") 
        {
            $categoryName = 'Trains';
        }

        return $categoryName;
    }

    private function sealedProductsUploadData() 
    {
        $setId = $this->getRequest()->getParam('set_id');
        $entity = $this->getRequest()->getParam('entity');
        $categories = $this->getRequest()->getParam('categories');

        $allowedFields = [];
        $mainEntity = null;
        $redirectUrl = null;
        $categoryName = null;

        // Card Game
        if ($entity == "card_game") 
        {
            $categoryName = 'Collectible Card Games';
            $allowedFields = [
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
                $mainEntity = 'card_game';
                $redirectUrl = '*/cardgamessets/index/'.$mainEntity.'/'.$setId;
            } else {
                $mainEntity = 'card_game';
                $redirectUrl = '*/cardgamessealedproducts/index/'.$mainEntity.'/'.$categories;
            }

            // dd($redirectUrl);
        }

        // Sport Card | Sealed Products
        if ($entity == "sport_card") {
            $categoryName = 'Sport Cards';
            $allowedFields = [
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
                $mainEntity = 'sc_categories';
                $redirectUrl = '*/sportcardssets/index/'.$mainEntity.'/'.$setId;
            } else {
                $mainEntity = 'sc_categories';
                $redirectUrl = '*/sportcardssealedproducts/index/'.$mainEntity.'/'.$categories;
            }
        }

        // Non Sport Card Sealed Products
        if ($entity == "non_sport_card") {
            $categoryName = 'Non Sports Cards';
            $allowedFields = [
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
                $mainEntity = 'nsc_categories';
                $redirectUrl = '*/nonsportcardssets/index/'.$mainEntity.'/'.$setId;
            } else {
                $mainEntity = 'nsc_categories';
                $redirectUrl = '*/nonsportcardssealedproducts/index/'.$mainEntity.'/'.$categories;
            }
        }

        return [
            'allowedFields' => $allowedFields,
            'mainEntity' => $mainEntity,
            'redirectUrl' => $redirectUrl,
            'categoryName' => $categoryName
        ];
    }

    private function singlesUploadData() 
    {
        $setId = $this->getRequest()->getParam('set_id');
        $entity = $this->getRequest()->getParam('entity');
        $categories = $this->getRequest()->getParam('categories');

        $allowedFields = [];
        $mainEntity = null;
        $redirectUrl = null;
        $categoryName = null;

        // Action Toys
        if ($entity == "action_toys") 
        {
            $categoryName = 'Action Toys';
            $allowedFields = [
                'toys_brand',
                'images',
                'singles_set_name',
                'singles_item_number'
            ];            

            $mainEntity = 'set_id';
            $redirectUrl = '*/actiontoys/singles';
        }

        // Card Game Singles
        if ($entity == "card_game") 
        {
            $categoryName = 'Collectible Card Games';
            $allowedFields = [
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

            $mainEntity = 'set_id';
            $redirectUrl = '*/cardgamessets/singles/'.$entity.'/'.$categories;
        }

        // Miniature Game Singles
        if ($entity == "miniature_game") 
        {
            $categoryName = 'Miniature Games';
            $allowedFields = [
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

            $mainEntity = 'set_id';
            $redirectUrl = '*/miniaturegamesingles/index/'.$mainEntity.'/'.$setId;
        }
        
        // Sport Card | Singles
        if ($entity == "sport_card") 
        {
            $categoryName = 'Sport Cards';
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

            $mainEntity = 'set_id';
            $redirectUrl = '*/sportcardssetscard/index/'.$mainEntity.'/'.$setId;
        }

        // Non Sport Card | Singles
        if ($entity == "non_sport_card") 
        {
            $categoryName = 'Non Sports Cards';
            $allowedFields = [
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
            
            $mainEntity = 'set_id';
            $redirectUrl = '*/nonsportcardssetscard/index/'.$mainEntity.'/'.$setId;
        }

        // Dolls
        if ($entity == "dolls") 
        {
            $categoryName = 'Fashion Dolls';
            $allowedFields = [
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
            
            $mainEntity = 'set_id';
            $redirectUrl = '*/dollssingles/index/'.$mainEntity.'/'.$setId;
        }

        // Bricks
        if ($entity == "bricks_brand") 
        {
            $categoryName = 'Bricks';
            $allowedFields = [
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
            
            $mainEntity = 'set_id';
            $redirectUrl = '*/legosingles/index/'.$mainEntity.'/'.$setId;
        }

        return [
            'allowedFields' => $allowedFields,
            'mainEntity' => $mainEntity,
            'redirectUrl' => $redirectUrl,
            'categoryName' => $categoryName
        ];
    }

    public function processData($index, $columnName, $columnData, $columnMapping) 
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
                        $this->messageManager->addError(__('Invalid date "%1" at row %2. Date format must be "dd/mm/yyyy".', $columnData, $index+1));
                        return 'error';
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