<?php

namespace TCGCollective\MarketPlace\Model\ResourceModel;

/**
 * Class PlatformFee
 *
 * @api
 * @since 100.0.2
 */
class PlatformFee extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Define main table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('marketplace_platform_fee', 'entity_id');
    }
}
