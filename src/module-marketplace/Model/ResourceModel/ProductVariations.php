<?php

namespace TCGCollective\MarketPlace\Model\ResourceModel;

/**
 * Class ProductVariations
 *
 * @api
 * @since 100.0.2
 */
class ProductVariations extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Define main table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('marketplace_product_variations', 'entity_id');
    }
}
