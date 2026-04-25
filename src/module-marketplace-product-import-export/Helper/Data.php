<?php

namespace TCGCollective\MarketplaceProductImportExport\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $importExportTemplateFactory;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \TCGCollective\MarketplaceProductImportExport\Model\ImportExportTemplateFactory $importExportTemplateFactory
    ) {
        parent::__construct(
            $context
        );
        $this->importExportTemplateFactory = $importExportTemplateFactory;
    }

    public function getTemplateUrl($attributeCode, $selectedOptionId, $categoryId) 
    {
        if ($attributeCode && $selectedOptionId || $categoryId) {
            $collection = $this->importExportTemplateFactory->create()->getCollection();

            if ($attributeCode && $selectedOptionId) {
                $collection->addFieldToFilter('attribute_code', $attributeCode);
                $collection->addFieldToFilter('attribute_option_id', $selectedOptionId);
            } elseif ($categoryId) {
                $collection->addFieldToFilter('category_id', $categoryId);
            }

            // dd($collection->getSelect()->__toString());
            
            if ($collection->getSize()) {
                $templatePath = $collection->getFirstItem()->getTemplate();
                if ($templatePath) {
                    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                    $storeManager = $objectManager->get(\Magento\Store\Model\StoreManagerInterface::class);
                    $mediaUrl = $storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
                    return $mediaUrl.'importexport_template'.$templatePath;
                }
            }
        }

        return null;
    }
}