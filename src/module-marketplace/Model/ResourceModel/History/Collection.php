<?php

namespace TCGCollective\MarketPlace\Model\ResourceModel\History;

use \TCGCollective\MarketPlace\Model\History;

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
            \TCGCollective\MarketPlace\Model\History::class,
            \TCGCollective\MarketPlace\Model\ResourceModel\History::class
        );
    }

    /**
     * Init select
     *
     * @return \TCGCollective\MarketPlace\Model\ResourceModel\History\Collection
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        return $this;
    }
}
