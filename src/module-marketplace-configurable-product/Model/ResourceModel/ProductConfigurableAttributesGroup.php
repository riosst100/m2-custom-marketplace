<?php

namespace TCGCollective\MarketplaceConfigurableProduct\Model\ResourceModel;

class ProductConfigurableAttributesGroup extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Dependency Initilization
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init("marketplace_product_configurable_attributes_group", "entity_id");
    }
}