<?php

namespace TCGCollective\PlatformDatabase\Model\ResourceModel\MainCategoriesConfig;

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
            \TCGCollective\PlatformDatabase\Model\MainCategoriesConfig::class,
            \TCGCollective\PlatformDatabase\Model\ResourceModel\MainCategoriesConfig::class
        );
        $this->_map['fields']['entity_id'] = 'main_table.entity_id';
    }
}