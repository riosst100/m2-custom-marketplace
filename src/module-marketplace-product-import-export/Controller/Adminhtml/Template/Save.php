<?php

namespace TCGCollective\MarketplaceProductImportExport\Controller\Adminhtml\Template;

use Magento\Backend\App\Action;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Framework\Image\AdapterFactory;
use Magento\Store\Model\ScopeInterface; 

class Save extends \TCGCollective\MarketplaceProductImportExport\Controller\Adminhtml\Index
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
     * @var \TCGCollective\ProductAttributesLink\Helper\Data
     */
    protected $mappingHelper;

    protected $importExportTemplateFactory;
    
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Filesystem $fileSystem,
        \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        AdapterFactory $adapterFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry,
        \TCGCollective\ProductAttributesLink\Helper\Data $mappingHelper,
        \TCGCollective\MarketplaceProductImportExport\Model\ImportExportTemplateFactory $importExportTemplateFactory
    ) {
        parent::__construct($context, $resultPageFactory, $registry);
        $this->fileSystem = $fileSystem;
        $this->request = $request;
        $this->scopeConfig = $scopeConfig;
        $this->adapterFactory = $adapterFactory;
        $this->uploaderFactory = $uploaderFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->mappingHelper = $mappingHelper;
        $this->importExportTemplateFactory = $importExportTemplateFactory;
    }

    public function execute()
    {
        $entityType = $this->getRequest()->getParam('entity_type');
        $data = $this->getRequest()->getParams();

        try {
            // INPUT DATA TO DB
            $model = null;
            
            $collection = $this->importExportTemplateFactory->create()
            ->getCollection();

            $categoryId = $this->getRequest()->getParam('category_id');
            $attributeCode = $this->getRequest()->getParam('attribute_code');
            $attributeOptionId = $this->getRequest()->getParam('attribute_option_id');

            $entityType = $this->getRequest()->getParam('entity_type');
            if ($entityType == "category") {
                $collection->addFieldToFilter('category_id', $categoryId);
            }

            if ($entityType == "attribute") {
                $collection->addFieldToFilter('attribute_code', $attributeCode);
                $collection->addFieldToFilter('attribute_option_id', $attributeOptionId);
            }

            if ($collection->getSize()) {
                $model = $collection->getFirstItem();
                $data['entity_id'] = $model->getId();
            } else {
                $model = $this->importExportTemplateFactory->create();
            }

            // SAVE FILE
            $templateFileName = null;

            if ((isset($_FILES['data']['name'])) && ($_FILES['data']['name'] != '')) 
            {
                $uploaderFactory = $this->uploaderFactory->create(['fileId' => 'data']);
                $uploaderFactory->setAllowedExtensions(['csv']);
                $uploaderFactory->setAllowRenameFiles(true);
                $uploaderFactory->setFilesDispersion(true);

                $mediaDirectory = $this->fileSystem->getDirectoryRead(DirectoryList::MEDIA);
                $destinationPath = $mediaDirectory->getAbsolutePath('importexport_template');

                $result = $uploaderFactory->save($destinationPath);

                $templateFileName = $result['file'] ?? null;
            }

            if ($templateFileName) {
                $data['template'] = $templateFileName;
            } else {
                unset($data['template']);
            }

            // dd($data);
            // $data['group_id'] = $this->processGroupId($data['group']);
            
            if ($model) {
                $model->setData($data);
                $model->save();
            }
            
            $this->messageManager->addSuccess(__('Template saved successfully.'));

            return $this->_redirect('*/*/index');
        } catch (\Exception $e) {   
            $this->messageManager->addError(__($e->getMessage()));
            $this->_redirect($this->_redirect->getRefererUrl());
        }
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
}