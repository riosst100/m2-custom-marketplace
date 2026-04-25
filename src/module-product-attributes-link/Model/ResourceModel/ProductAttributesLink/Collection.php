<?php

namespace TCGCollective\ProductAttributesLink\Model\ResourceModel\ProductAttributesLink;

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
            \TCGCollective\ProductAttributesLink\Model\ProductAttributesLink::class,
            \TCGCollective\ProductAttributesLink\Model\ResourceModel\ProductAttributesLink::class
        );
        $this->_map['fields']['entity_id'] = 'main_table.entity_id';
    }

    public function _initSelect() 
    {
        $this->addFilterToMap('attribute_code', 'main_table.attribute_code');
        parent::_initSelect();
    }
}