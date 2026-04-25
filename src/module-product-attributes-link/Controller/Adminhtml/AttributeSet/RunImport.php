<?php

namespace TCGCollective\ProductAttributesLink\Controller\Adminhtml\AttributeSet;

use Magento\Backend\App\Action;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Framework\Image\AdapterFactory;
use Magento\Store\Model\ScopeInterface; 

class RunImport extends \TCGCollective\ProductAttributesLink\Controller\Adminhtml\Index
{
    protected $scopeConfig;
    protected $mappingHelper;


    protected $fileSystem;
    protected $uploaderFactory;
    protected $request;
    protected $adapterFactory;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \TCGCollective\ProductAttributesLink\Model\ProductAttributesLinkFactory
     */
    protected $prdAttrLinkFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    protected $categoryCollFactory;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory
     */
    protected $attrSetCollFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\ProductFactory
     */
    protected $prdResourceFactory;

    /**
     * @var \TCGCollective\ProductAttributesLink\Model\MappingGroupFactory
     */
    protected $mappingGroupFactory;

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

    protected $uploadHistoryFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Filesystem $fileSystem,
        \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        AdapterFactory $adapterFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry,
        \TCGCollective\ProductAttributesLink\Model\ProductAttributesLinkFactory $prdAttrLinkFactory,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollFactory,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $attrSetCollFactory,
        \Magento\Catalog\Model\ResourceModel\ProductFactory $prdResourceFactory,
        \TCGCollective\ProductAttributesLink\Model\MappingGroupFactory $mappingGroupFactory,
        \Magento\Catalog\Api\ProductAttributeRepositoryInterface $attributeRepository,
        \Magento\Eav\Model\Entity\Attribute\Source\TableFactory $tableFactory,
        \Magento\Eav\Api\AttributeOptionManagementInterface $attributeOptionManagement,
        \Magento\Eav\Api\Data\AttributeOptionLabelInterfaceFactory $optionLabelFactory,
        \Magento\Eav\Api\Data\AttributeOptionInterfaceFactory $optionFactory,
        \TCGCollective\ProductAttributesLink\Model\UploadHistoryFactory $uploadHistoryFactory
    ) {
        parent::__construct($context, $resultPageFactory, $registry);
        $this->fileSystem = $fileSystem;
        $this->request = $request;
        $this->scopeConfig = $scopeConfig;
        $this->adapterFactory = $adapterFactory;
        $this->uploaderFactory = $uploaderFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->prdAttrLinkFactory = $prdAttrLinkFactory;
        $this->categoryCollFactory = $categoryCollFactory;
        $this->attrSetCollFactory = $attrSetCollFactory;
        $this->prdResourceFactory = $prdResourceFactory;
        $this->mappingGroupFactory = $mappingGroupFactory;
        $this->attributeRepository = $attributeRepository;
        $this->tableFactory = $tableFactory;
        $this->attributeOptionManagement = $attributeOptionManagement;
        $this->optionLabelFactory = $optionLabelFactory;
        $this->optionFactory = $optionFactory;
        $this->uploadHistoryFactory = $uploadHistoryFactory;
    }

    public function getColumnNameAlias($columnName) 
    {
        if ($columnName == "category") {
            return "category_id";
        }
        
        if ($columnName == "attribute_option") {
            return "attribute_option_id";
        }
        
        if ($columnName == "target_attribute_set") {
            return "target_attribute_set_id";
        }

        if ($columnName == "target_attribute_code") {
            return "target_attribute_code";
        }

        if ($columnName == "group") {
            return "group_name";
        }
        
        return $columnName;
    }

    public function processCategoryId($categoryName) 
    {
        $categoryId = null;
        if ($categoryName) {
            $collection = $this->categoryCollFactory->create()
            ->addAttributeToFilter('name', $categoryName)
            ->addFieldToFilter('level', ['neq' => 2])
            ->addFieldToFilter('level', ['neq' => 1]);
            if ($collection->getSize()) {
                return $collection->getFirstItem()->getId();
            }
        }

        return $categoryId;
    }

    public function processAttributeSetId($attributeSetName) 
    {
        // die($attributeSetName);
        if ($attributeSetName) {
            $collection = $this->attrSetCollFactory->create()
            ->addFieldToFilter('attribute_set_name', $attributeSetName);
            // if ($attributeSetName == "Anime Toys | Brands | Anime Toys | Anime Figures | Complete Figures") {
            // dd($collection->getSelect()->__toString());
            // }
            if ($collection->getSize()) {
                return $collection->getFirstItem()->getAttributeSetId();
            } else {
                $error = [
                    'attributeSetName' => $attributeSetName,
                    'query' => $collection->getSelect()->__toString()
                ];

                $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/ERROREERRORE.log');
                $logger = new \Zend_Log();
                $logger->addWriter($writer);
                $logger->info('ERRORE: '.print_r($error,true));
            }
        }

        return null;
    }

    public function processAttributeOptionId($attributeCode, $optionLabel) 
    {
        if ($optionLabel && $attributeCode) {
            return $this->getAttributeOptionId($attributeCode, $optionLabel);
        }

        return null;
    }

    public function processAttributeOptions($targetAttributeCode, $optionLabels) 
    {
        if ($optionLabels && $targetAttributeCode) {
            $optionLabelsArr = explode(",", $optionLabels);
            if ($optionLabelsArr) {
                $optionIds = [];
                foreach($optionLabelsArr as $optionLabel) {
                    // if ($targetAttributeCode == "miniature_game") {
                    //     dd($optionLabelsArr);
                    // }
                    // if ($optionLabel != "Game" && $optionLabel != "Brands") {
                        $optionIds[] = $this->getAttributeOptionId($targetAttributeCode, $optionLabel);
                    // }
                }

                return implode(",", $optionIds);
            }
        }

        return null;
    }

    public function normalizeSpacing($input) {
        // Step 1: Replace multiple spaces with a single space throughout the string
        $normalized = preg_replace('/\s+/', ' ', $input);

        // Step 2: Ensure proper spacing around " | "
        $normalized = preg_replace('/\s*\|\s*/', ' | ', $normalized);

        // Step 3: Trim leading and trailing spaces to clean up the string
        return trim($normalized);
    }

    public function processData($columnName, $columnData, $attributeCode = null, $targetAttributeCode = null) 
    {
        $columnData = $this->normalizeSpacing($columnData);
        // if ($columnName == "attribute_code") {
        //     return $this->processAttributeId($columnData);
        // }

        // if ($columnName == "target_attribute_code") {
        //     return $this->processAttributeCode($columnData);
        // }


        // if ($columnName == "group_id") {
        //     return $this->processGroupId($columnData);
        // }

        if ($columnName == "category_id") {
            return $this->processCategoryId($columnData);
        }

        if ($columnName == "attribute_option_id") {
            return $this->processAttributeOptionId($attributeCode, $columnData);
        }

        if ($columnName == "target_attribute_options") {
            return $this->processAttributeOptions($targetAttributeCode, $columnData);
        }

        if ($columnName == "target_attribute_set_id") {
            return $this->processAttributeSetId($columnData);
        }

        return $columnData;
    }

    public function processGroupId($groupName) 
    {
        if ($groupName) {
            $collection = $this->mappingGroupFactory->create()
            ->getCollection()
            ->addFieldToFilter('name', $groupName);
            if ($collection->getSize()) {
                return $collection->getFirstItem()->getId();
            } else {
                $mappingGroup = $this->mappingGroupFactory->create()
                ->setName($groupName)
                ->save();
                if ($mappingGroup) {
                    return $mappingGroup->getId();
                }
            }
        }

        return 0;
    }

    public function getAttributeOptionId($attributeCode, $label)
    {
        $label = trim($label);

        if ($label) {
            if ($label == "Game" || $label == "Brands" || $label == "Games") {
                return 0;
            }
            $optionId = $this->getOptionId($attributeCode, $label, true);
            if ($optionId) {
                return $optionId;
            }
        }

        return null;
    }

    /**
     * Get attribute by code.
     *
     * @param string $attributeCode
     * @return \Magento\Catalog\Api\Data\ProductAttributeInterface
     */
    public function getAttribute($attributeCode)
    {
        return $this->attributeRepository->get($attributeCode);
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
        /** @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute */
        $attribute = $this->getAttribute($attributeCode);

        // Build option array if necessary
        if ($force === true || !isset($this->attributeValues[ $attribute->getAttributeId() ])) {
            $this->attributeValues[ $attribute->getAttributeId() ] = [];

            // We have to generate a new sourceModel instance each time through to prevent it from
            // referencing its _options cache. No other way to get it to pick up newly-added values.

            /** @var \Magento\Eav\Model\Entity\Attribute\Source\Table $sourceModel */
            $sourceModel = $this->tableFactory->create();
            $sourceModel->setAttribute($attribute);

            foreach ($sourceModel->getAllOptions() as $option) {
                $this->attributeValues[ $attribute->getAttributeId() ][ $option['label'] ] = $option['value'];
            }
        }

        // Return option ID if exists
        if (isset($this->attributeValues[ $attribute->getAttributeId() ][ $label ])) {
            return $this->attributeValues[ $attribute->getAttributeId() ][ $label ];
        }

        // Return false if does not exist
        return false;
    }

    public function execute()
    {
        if ((isset($_FILES['importdata']['name'])) && ($_FILES['importdata']['name'] != '')) 
        {
            try {
                $uploaderFactory = $this->uploaderFactory->create(['fileId' => 'importdata']);
                $uploaderFactory->setAllowedExtensions(['csv', 'xls']);
                $uploaderFactory->setAllowRenameFiles(true);
                $uploaderFactory->setFilesDispersion(true);

                $mediaDirectory = $this->fileSystem->getDirectoryRead(DirectoryList::MEDIA);
                $destinationPath = $mediaDirectory->getAbsolutePath('custom_table/upload/attributeset');

                $result = $uploaderFactory->save($destinationPath);

                if (!$result) {
                    throw new LocalizedException
                    (
                    __('File cannot be saved to path: $1', $destinationPath)
                    );

                } else {   
                    $filePath = 'custom_table/upload/attributeset'.$result['file'];

                    $mediaDirectory = $this->fileSystem->getDirectoryRead(DirectoryList::MEDIA);
                    
                    $destinationfilePath = $mediaDirectory->getAbsolutePath($filePath);

                    /* file read operation */

                    $f_object = fopen($destinationfilePath, "r");
                    
                    $column = fgetcsv($f_object);

                    

                    // This mapping column name
                    $columnMapping = [];
                    foreach($column as $index => $columnName) {
                        $columnName = strtolower($columnName);
                        $columnName = strtolower(preg_replace('/[^a-zA-Z0-9_]/', '', $columnName));
                        $columnName = $this->getColumnNameAlias($columnName);
                        $columnMapping[$columnName] = $index;
                    }

                    // dd($columnMapping);
                    
                    // column name must be the same as the Sample filename 

                        if($f_object)
                        {
                            // if( ($column[0] == 'Col_name_1') && ($column[1] == 'Col_name_2') && ($column[2] == 'Col_name_3') && ($column[3] == 'Col_name_4') && ($column[4] == 'Col_name_5') )
                            // {   

                            $countUpdated = 0;
                            $countAdded = 0;
                            $totalRow = 0;
                            $rawData = [];


                            $TESTFULL = [];
                            $ACTUALDATA = [];
                            $ADDED = [];
                            $UPDATED = [];
                            $NOT_UPDATED = [];
                            $COLUMNMAPPINGS = [];
                            $PROCESS = [];
                            $errorAttributeSet = [];
                            $errorAttributeOption = [];
                            $errorCategory = [];
                            
                            while (($dataColumns = fgetcsv($f_object)) !== FALSE) 
                            {
                                // dd($dataColumns);
                                $rawData[] = $dataColumns;
                                $totalRow++;

                                $attributeCode = null;
                                if (isset($columnMapping['attribute_code'])) {
                                    $attributeCodeIndex = $columnMapping['attribute_code'];
                                    $attributeCode = $dataColumns[$attributeCodeIndex];
                                }

                                $targetAttributeCode = null;
                                if (isset($columnMapping['target_attribute_code'])) {
                                    $targetAttributeCodeIndex = $columnMapping['target_attribute_code'];
                                    $targetAttributeCode = $dataColumns[$targetAttributeCodeIndex];
                                }

                                $data = [];
                                $TEST = [];
                                
                                foreach($columnMapping as $columnName => $index) {
                                    $TEST[$columnName] = $dataColumns[$index];
                                    $columnData = $this->processData($columnName, $dataColumns[$index], $attributeCode, $targetAttributeCode);
                                    // if ($columnName == "target_attribute_set_id") {
                                        $PROCESS[$totalRow][] = [
                                            "columnName" => $columnName,
                                            "dataColumns" => $dataColumns[$index],
                                            "columnData" => $columnData,
                                            "attributeCode" => $attributeCode,
                                            "targetAttributeCode" => $targetAttributeCode
                                        ];
                                    // }
                                    $data[$columnName] = $columnData;
                                }

                                $TESTFULL[] = $TEST;
                                $ACTUALDATA[] = $data;

                                $currentRow = $totalRow+1;

                                if (!$data['target_attribute_set_id']) {
                                    $NOT_UPDATED[] = $TEST;
                                    $NOT_UPDATED[] = $data;

                                    // if (!in_array($TEST['target_attribute_set_id'], $errorAttributeSet)) {
                                        // $errorAttributeSet[] = $TEST['target_attribute_set_id'];
                                        if ($TEST['target_attribute_set_id']) {
                                        $this->messageManager->addWarning(__('Row %1 : <b>%2</b> attribute set is not found.', 
                                        $currentRow, $TEST['target_attribute_set_id']));
                                        }
                                    // }
                                    // $this->messageManager->addWarning(__('<b>%1</b> attribute set is not found.', $data['target_attribute_set_id']));

                                    continue;
                                }

                                if ($TEST['attribute_option_id'] && $data['attribute_option_id'] != 0 && $data['attribute_option_id'] == null) {
                                    $NOT_UPDATED[] = $TEST;
                                    $NOT_UPDATED[] = $data;
                                    // if (!in_array($TEST['attribute_option_id'], $errorAttributeOption)) {
                                        // $errorAttributeOption[] = $TEST['attribute_option_id'];
                                        $this->messageManager->addWarning(__('Row %1 : <b>%2</b> option is not found on attribute <b>%3</b>.', $currentRow, $TEST['attribute_option_id'], $TEST['attribute_code']));
                                    // }
                                    continue;
                                }

                                if ($TEST['category_id'] && !$data['category_id']) {
                                    $NOT_UPDATED[] = $TEST;
                                    $NOT_UPDATED[] = $data;

                                    // if (!in_array($TEST['category_id'], $errorCategory)) {
                                        // $errorCategory[] = $TEST['category_id'];
                                        $this->messageManager->addWarning(__('Row %1 : <b>%2</b> category is not found.', $currentRow, $TEST['category_id']));
                                    // }
                                    continue;
                                }

                                $data['mapping_type'] = 'attribute_set';

                                // die(print_r($data,true));

                                if ($data['category_id'] && !$data['attribute_code']) {
                                    $collection = $this->prdAttrLinkFactory->create()
                                    ->getCollection()
                                    ->addFieldToFilter('category_id', $data['category_id']);
                                } else {
                                    $collection = $this->prdAttrLinkFactory->create()
                                    ->getCollection()
                                    ->addFieldToFilter('attribute_code', $data['attribute_code'])
                                    ->addFieldToFilter('group_name', $data['group_name'])
                                    ->addFieldToFilter('target_attribute_set_id', $data['target_attribute_set_id'])
                                    ->addFieldToFilter('attribute_option_id', $data['attribute_option_id']);

                                    // die('code:'. $data['attribute_code'].'|group:'.$data['group_id'].'|option:'.$data['attribute_option_id'].'|count: '.$collection->getSize());
                                }

                                $collection->addFieldToFilter('mapping_type', 'attribute_set');

                                if ($collection->getSize()) {
                                    $prdAttrLink = $collection->getFirstItem();
                                    
                                    $data['entity_id'] = $prdAttrLink->getId();

                                    $UPDATED[] = $TEST;
                                    $UPDATED[] = $data;

                                    $countUpdated++;
                                } else {
                                    $prdAttrLink = $this->prdAttrLinkFactory->create();
                                    $countAdded++;

                                    $ADDED[] = $TEST;
                                    $ADDED[] = $data;
                                }

                                // dd($data);

                                $prdAttrLink->setData($data);
                                $prdAttrLink->save();
                            }

                            // dd($TESTFULL);

                            // die('<pre>'.print_r($COLUMNMAPPINGS,true));
                            
                            // die('<pre>'.print_r($PROCESS,true));
                            // die('<pre>'.print_r($ADDED,true));
                            // die('<pre>'.print_r($UPDATED,true));
                            // die('<pre>'.print_r($ACTUALDATA,true));
                            // die('<pre>'.print_r($TESTFULL,true));

                            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                            $this->mappingHelper = $objectManager->create(\TCGCollective\ProductAttributesLink\Helper\Data::class);

                            // Save history
                            $historyData = [
                                'upload_type' => 'attributeset',
                                'categories' => 'attributeset',
                                'file_path' => $filePath,
                                'total_row' => $totalRow,
                                'total_row_added' => $countAdded,
                                'total_row_updated' => $countUpdated,
                                'messages' => 'success',
                                'status' => 1,
                                'user_id' => $this->mappingHelper->getAdminId(),
                                'entity' => ''
                            ];

                            $history = $this->uploadHistoryFactory->create()
                            ->setData($historyData)
                            ->save();
                            
                            if ($countAdded > 0) {
                                $this->messageManager->addSuccess(__('Added %1 of %2 record(s).', $countAdded, $totalRow));
                            }

                            if ($countUpdated > 0) {
                                $this->messageManager->addSuccess(__('Updated %1 of %2 record(s).', $countUpdated, $totalRow));
                            }

                            if (!$countAdded && !$countUpdated) {
                                $this->messageManager->addSuccess(__('No uploaded data.'));
                            }
                                
                            $this->_redirect('productattributeslink/attributeset/import');
                            // } else {
                            //     $this->messageManager->addError(__("Invalid Formated File"));
                            //     $this->_redirect('productattributeslink/attributeset/import');
                            // }

                        } 
                        else
                        {
                            $this->messageManager->addError(__("File hase been empty"));
                            $this->_redirect('productattributeslink/attributeset/import');
                        }
                
                    }                   
            } catch (\Exception $e) {   
                $this->messageManager->addError(__($e->getMessage()));
                $this->_redirect('productattributeslink/attributeset/import');
            }
        } else {
            $this->messageManager->addError(__("Please try again."));
            $this->_redirect('productattributeslink/attributeset/import');
        }
    }
}