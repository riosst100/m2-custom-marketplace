<?php

namespace TCGCollective\ProductAttributesLink\Controller\Adminhtml\ConfigurableAttributes;

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
     * @var \TCGCollective\MarketplaceConfigurableProduct\Model\ProductConfigurableAttributesFactory
     */
    protected $configurableAttributesFactory;

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
     * @var \TCGCollective\MarketplaceConfigurableProduct\Model\ProductConfigurableAttributesGroupFactory
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
        \TCGCollective\MarketplaceConfigurableProduct\Model\VariationsAttributesMappingFactory $configurableAttributesFactory,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollFactory,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $attrSetCollFactory,
        \Magento\Catalog\Model\ResourceModel\ProductFactory $prdResourceFactory,
        \TCGCollective\MarketplaceConfigurableProduct\Model\ProductConfigurableAttributesGroupFactory $mappingGroupFactory,
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
        $this->configurableAttributesFactory = $configurableAttributesFactory;
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
        if ($columnName == "attribute_set") {
            return "attribute_set_id";
        }
        
        return $columnName;
    }

    public function processAttributeSetId($attributeSetName) 
    {
        // die($attributeSetName);
        if ($attributeSetName) {
            $collection = $this->attrSetCollFactory->create()
            ->addFieldToFilter('attribute_set_name', $attributeSetName);
            if ($collection->getSize()) {
                return $collection->getFirstItem()->getAttributeSetId();
            }
        }

        return null;
    }

    public function processData($columnName, $columnData) 
    {
        // if ($columnName == "attribute_code") {
        //     return $this->processAttributeId($columnData);
        // }

        // if ($columnName == "target_attribute_code") {
        //     return $this->processAttributeCode($columnData);
        // }

        if ($columnName == "attribute_set_id") {
            return $this->processAttributeSetId($columnData);
        }

        if ($columnName == "variations_attribute_codes") {
            return str_replace(";", ",", $columnData);
        }

        return $columnData;
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
                $destinationPath = $mediaDirectory->getAbsolutePath('productattributelink/variations_attributes');

                $result = $uploaderFactory->save($destinationPath);

                if (!$result) {
                    throw new LocalizedException
                    (
                    __('File cannot be saved to path: $1', $destinationPath)
                    );

                } else {   
                    $imagePath = 'productattributelink/variations_attributes'.$result['file'];

                    $mediaDirectory = $this->fileSystem->getDirectoryRead(DirectoryList::MEDIA);
                    
                    $destinationfilePath = $mediaDirectory->getAbsolutePath($imagePath);

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

                                $data = [];
                                foreach($columnMapping as $columnName => $index) {
                                    $columnData = $this->processData($columnName, $dataColumns[$index]);
                                    $data[$columnName] = $columnData;
                                }

                                // die(print_r($data,true));

                                // if ($data['category_id']) {
                                //     $collection = $this->configurableAttributesFactory->create()
                                //     ->getCollection()
                                //     ->addFieldToFilter('category_id', $data['category_id']);
                                // } else {
                                    $collection = $this->configurableAttributesFactory->create()
                                    ->getCollection()
                                    ->addFieldToFilter('attribute_set_id', $data['attribute_set_id']);

                                    // die('code:'. $data['attribute_code'].'|group:'.$data['group_id'].'|option:'.$data['attribute_option_id'].'|count: '.$collection->getSize());
                                // }

                                if ($collection->getSize()) {
                                    $prdAttrLink = $collection->getFirstItem();
                                    
                                    $data['entity_id'] = $prdAttrLink->getId();
                                } else {
                                    $prdAttrLink = $this->configurableAttributesFactory->create();
                                }

                                $prdAttrLink->setData($data);
                                $prdAttrLink->save();
                            } 
                                
                            $this->messageManager->addSuccess(__('A total of %1 record(s) have been Added.', $count));
                            $this->_redirect('productattributeslink/configurableattributes/index');
                            // } else {
                            //     $this->messageManager->addError(__("Invalid Formated File"));
                            //     $this->_redirect('productattributeslink/configurableattributes/import');
                            // }

                        } 
                        else
                        {
                            $this->messageManager->addError(__("File hase been empty"));
                            $this->_redirect('productattributeslink/configurableattributes/import');
                        }
                
                    }                   
            } catch (\Exception $e) {   
                $this->messageManager->addError(__($e->getMessage()));
                $this->_redirect('productattributeslink/configurableattributes/import');
            }
        } else {
            $this->messageManager->addError(__("Please try again."));
            $this->_redirect('productattributeslink/configurableattributes/import');
        }
    }
}