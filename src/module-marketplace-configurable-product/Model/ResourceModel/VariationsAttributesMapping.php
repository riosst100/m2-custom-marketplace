<?php

namespace TCGCollective\MarketplaceConfigurableProduct\Model\ResourceModel;

class VariationsAttributesMapping extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Dependency Initilization
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init("marketplace_variations_attributes_mapping", "entity_id");
    }
}