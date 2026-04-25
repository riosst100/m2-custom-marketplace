<?php

namespace TCGCollective\MarketPlace\Model\ResourceModel;

/**
 * Class History
 *
 * @api
 * @since 100.0.2
 */
class History extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Define main table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('import_history', 'history_id');
    }
}
