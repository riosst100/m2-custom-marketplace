<?php

namespace TCGCollective\ProductAttributesLink\Model\ResourceModel\GundamSingles;

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
            \TCGCollective\ProductAttributesLink\Model\GundamSingles::class,
            \TCGCollective\ProductAttributesLink\Model\ResourceModel\GundamSingles::class
        );
        $this->_map['fields']['entity_id'] = 'main_table.entity_id';
    }
}