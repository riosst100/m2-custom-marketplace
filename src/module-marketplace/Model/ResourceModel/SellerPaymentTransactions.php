<?php

namespace TCGCollective\MarketPlace\Model\ResourceModel;

/**
 * Class SellerPaymentTransactions
 *
 * @api
 * @since 100.0.2
 */
class SellerPaymentTransactions extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Define main table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('marketplace_seller_payment_transactions', 'entity_id');
    }
}
