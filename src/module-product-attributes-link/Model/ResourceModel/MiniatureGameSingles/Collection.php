<?php

namespace TCGCollective\ProductAttributesLink\Model\ResourceModel\MiniatureGameSingles;

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
            \TCGCollective\ProductAttributesLink\Model\MiniatureGameSingles::class,
            \TCGCollective\ProductAttributesLink\Model\ResourceModel\MiniatureGameSingles::class
        );
        $this->_map['fields']['entity_id'] = 'main_table.entity_id';
    }
}