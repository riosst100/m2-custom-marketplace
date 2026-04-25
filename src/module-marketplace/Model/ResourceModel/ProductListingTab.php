<?php

namespace TCGCollective\MarketPlace\Model\ResourceModel;

class ProductListingTab extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Define main table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('marketplace_product_listing_category_tab', 'entity_id');
    }
}
