<?php

namespace TCGCollective\MarketPlace\Model\ResourceModel\ProductListingTab;

use \TCGCollective\MarketPlace\Model\ProductListingTab;

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
            \TCGCollective\MarketPlace\Model\ProductListingTab::class,
            \TCGCollective\MarketPlace\Model\ResourceModel\ProductListingTab::class
        );
    }

    /**
     * Init select
     *
     * @return \TCGCollective\MarketPlace\Model\ResourceModel\ProductListingTab\Collection
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        return $this;
    }
}
