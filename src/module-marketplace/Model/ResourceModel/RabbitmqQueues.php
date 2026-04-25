<?php

namespace TCGCollective\MarketPlace\Model\ResourceModel;

/**
 * Class RabbitmqQueues
 *
 * @api
 * @since 100.0.2
 */
class RabbitmqQueues extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Define main table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('marketplace_rabbitmq_queue', 'entity_id');
    }
}
