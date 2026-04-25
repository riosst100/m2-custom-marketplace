<?php

namespace TCGCollective\MarketplaceProductImportExport\Model\ResourceModel\MediaGalleryAsset;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';
    
    /**
     * Dependency Initilization
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init(
            \TCGCollective\MarketplaceProductImportExport\Model\MediaGalleryAsset::class,
            \TCGCollective\MarketplaceProductImportExport\Model\ResourceModel\MediaGalleryAsset::class
        );
        $this->_map['fields']['entity_id'] = 'main_table.entity_id';
    }
}