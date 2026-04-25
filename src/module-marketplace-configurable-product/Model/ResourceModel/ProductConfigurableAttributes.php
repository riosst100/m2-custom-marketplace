<?php

namespace TCGCollective\MarketplaceConfigurableProduct\Model\ResourceModel;

class ProductConfigurableAttributes extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Dependency Initilization
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init("marketplace_product_configurable_attributes", "entity_id");
    }
}