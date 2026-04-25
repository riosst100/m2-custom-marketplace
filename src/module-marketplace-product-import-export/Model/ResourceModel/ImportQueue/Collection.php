<?php

namespace TCGCollective\MarketplaceProductImportExport\Model\ResourceModel\ImportQueue;

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
            \TCGCollective\MarketplaceProductImportExport\Model\ImportQueue::class,
            \TCGCollective\MarketplaceProductImportExport\Model\ResourceModel\ImportQueue::class
        );
        $this->_map['fields']['entity_id'] = 'main_table.entity_id';
    }
}