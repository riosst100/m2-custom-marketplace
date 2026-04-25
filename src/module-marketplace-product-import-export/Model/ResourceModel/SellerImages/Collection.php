<?php

namespace TCGCollective\MarketplaceProductImportExport\Model\ResourceModel\SellerImages;

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
            \TCGCollective\MarketplaceProductImportExport\Model\SellerImages::class,
            \TCGCollective\MarketplaceProductImportExport\Model\ResourceModel\SellerImages::class
        );
        $this->_map['fields']['entity_id'] = 'main_table.entity_id';
    }
}