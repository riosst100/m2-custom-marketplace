<?php

namespace TCGCollective\ProductAttributesLink\Model\ResourceModel;

class DiecastMilitaryAttributes extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Dependency Initilization
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init("marketplace_diecast_military_attributes", "entity_id");
    }

    public function getFields()
    {
        $fields = $this->getConnection()->describeTable($this->getMainTable());
        return $fields;
    }
}