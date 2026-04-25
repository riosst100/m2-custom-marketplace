<?php

namespace TCGCollective\ProductAttributesLink\Controller\Adminhtml\AttributeOption;

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
        \Magento\Eav\Api\Data\AttributeOptionInterfaceFactory $optionFactory
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
            return "group_id";
        }
        
        return $columnName;
    }

    public function processCategoryId($categoryName) 
    {
        $categoryId = null;
        if ($categoryName) {
            $collection = $this->categoryCollFactory->create()
            ->addAttributeToFilter('name', $categoryName);
            // ->addFieldToFilter('level', ['neq' => 2])
            // ->addFieldToFilter('level', ['neq' => 1]);
            if ($collection->getSize()) {
                return $collection->getFirstItem()->getId();
            }
        }

        return $categoryId;
    }

    public function processAttributeSetId($attributeSetName) 
    {
        if ($attributeSetName) {
            $collection = $this->attrSetCollFactory->create()
            ->addFieldToFilter('attribute_set_name', $attributeSetName);
            if ($collection->getSize()) {
                return $collection->getFirstItem()->getAttributeSetId();
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
            $optionLabelsArr = explode("|", $optionLabels);
            if ($optionLabelsArr) {
                $optionIds = [];
                foreach($optionLabelsArr as $optionLabel) {
                    $optionId = $this->getAttributeOptionId($targetAttributeCode, $optionLabel);
                    if ($optionId) {
                        $optionIds[] = $optionId;
                    }
                }

                return implode(",", $optionIds);
            }
        }

        return null;
    }

    public function processData($columnName, $columnData, $attributeCode = null, $targetAttributeCode = null) 
    {
        // if ($columnName == "attribute_code") {
        //     return $this->processAttributeId($columnData);
        // }

        // if ($columnName == "target_attribute_code") {
        //     return $this->processAttributeCode($columnData);
        // }


        if ($columnName == "group_id") {
            return $this->processGroupId($columnData);
        }

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
                $destinationPath = $mediaDirectory->getAbsolutePath('productattributelink/import');

                $result = $uploaderFactory->save($destinationPath);

                if (!$result) {
                    throw new LocalizedException
                    (
                    __('File cannot be saved to path: $1', $destinationPath)
                    );

                } else {   
                    $imagePath = 'productattributelink/import'.$result['file'];

                    $mediaDirectory = $this->fileSystem->getDirectoryRead(DirectoryList::MEDIA);
                    
                    $destinationfilePath = $mediaDirectory->getAbsolutePath($imagePath);

                    /* file read operation */

                    $f_object = fopen($destinationfilePath, "r");
                    
                    $column = fgetcsv($f_object);

                    // This mapping column name
                    $columnMapping = [];
                    foreach($column as $index => $columnName) {
                        $columnName = $this->getColumnNameAlias($columnName);
                        $columnMapping[$columnName] = $index;
                    }

                    // die(print_r($columnMapping,true));
                    
                    // column name must be the same as the Sample filename 

                        if($f_object)
                        {
                            // if( ($column[0] == 'Col_name_1') && ($column[1] == 'Col_name_2') && ($column[2] == 'Col_name_3') && ($column[3] == 'Col_name_4') && ($column[4] == 'Col_name_5') )
                            // {   

                            $count = 0;
                            while (($dataColumns = fgetcsv($f_object)) !== FALSE) 
                            {
                                $count++;

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
                                foreach($columnMapping as $columnName => $index) {
                                    $columnData = $this->processData($columnName, $dataColumns[$index], $attributeCode, $targetAttributeCode);
                                    $data[$columnName] = $columnData;
                                }

                                if (isset($data['target_attribute_set_id']) && $data['target_attribute_set_id'] != null) {
                                    $data['mapping_type'] = 'attribute_set';
                                } else {
                                    $data['mapping_type'] = 'attribute_option';
                                }
                        
                                if ($data['category_id']) {
                                    $collection = $this->prdAttrLinkFactory->create()
                                    ->getCollection()
                                    ->addFieldToFilter('category_id', $data['category_id'])
                                    ->addFieldToFilter('target_attribute_code', $data['target_attribute_code']);
                                } else {
                                    $collection = $this->prdAttrLinkFactory->create()
                                    ->getCollection()
                                    ->addFieldToFilter('attribute_code', $data['attribute_code'])
                                    ->addFieldToFilter('group_id', $data['group_id'])
                                    ->addFieldToFilter('target_attribute_code', $data['target_attribute_code'])
                                    ->addFieldToFilter('attribute_option_id', $data['attribute_option_id']);
                                }

                                $collection->addFieldToFilter('mapping_type', 'attribute_option');

                                if ($collection->getSize()) {
                                    $prdAttrLink = $collection->getFirstItem();

                                    // die('p');

                                    $data['entity_id'] = $prdAttrLink->getId();
                                } else {
                                    $prdAttrLink = $this->prdAttrLinkFactory->create();
                                }
                                
                                $prdAttrLink->setData($data);
                                $prdAttrLink->save();
                            } 
                                
                            $this->messageManager->addSuccess(__('A total of %1 record(s) have been Added.', $count));
                            $this->_redirect('productattributeslink/attributeoption/import');
                            // } else {
                            //     $this->messageManager->addError(__("Invalid Formated File"));
                            //     $this->_redirect('productattributeslink/index/import');
                            // }

                        } 
                        else
                        {
                            $this->messageManager->addError(__("File hase been empty"));
                            $this->_redirect('productattributeslink/attributeoption/import');
                        }
                
                    }                   
            } catch (\Exception $e) {   
                $this->messageManager->addError(__($e->getMessage()));
                $this->_redirect('productattributeslink/attributeoption/import');
            }
        } else {
            $this->messageManager->addError(__("Please try again."));
            $this->_redirect('productattributeslink/attributeoption/import');
        }
    }
}