<?php

namespace TCGCollective\MarketPlace\Model\ResourceModel;

/**
 * Class SellerNotifications
 *
 * @api
 * @since 100.0.2
 */
class SellerNotifications extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Define main table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('marketplace_seller_notifications', 'entity_id');
    }
}
