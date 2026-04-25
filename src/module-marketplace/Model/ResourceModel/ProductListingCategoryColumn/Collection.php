<?php

namespace TCGCollective\MarketPlace\Model\ResourceModel\ProductListingCategoryColumn;

use \TCGCollective\MarketPlace\Model\ProductListingCategoryColumn;

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
            \TCGCollective\MarketPlace\Model\ProductListingCategoryColumn::class,
            \TCGCollective\MarketPlace\Model\ResourceModel\ProductListingCategoryColumn::class
        );
    }

    /**
     * Init select
     *
     * @return \TCGCollective\MarketPlace\Model\ResourceModel\ProductListingCategoryColumn\Collection
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        return $this;
    }
}
