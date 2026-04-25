<?php

namespace TCGCollective\MarketPlace\Model\ResourceModel\ProductListingCategoryConfig;

use \TCGCollective\MarketPlace\Model\ProductListingCategoryConfig;

/**
 * Import history collection
 *
 * @api
 * @since 100.0.2
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Define resource model and assign link table name
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \TCGCollective\MarketPlace\Model\ProductListingCategoryConfig::class,
            \TCGCollective\MarketPlace\Model\ResourceModel\ProductListingCategoryConfig::class
        );
    }

    /**
     * Init select
     *
     * @return \TCGCollective\MarketPlace\Model\ResourceModel\ProductListingCategoryConfig\Collection
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        return $this;
    }
}
