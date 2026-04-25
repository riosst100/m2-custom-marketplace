<?php

namespace TCGCollective\MarketPlace\Model\ResourceModel;

/**
 * Class SellerPaymentAccounts
 *
 * @api
 * @since 100.0.2
 */
class SellerPaymentAccounts extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Define main table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('marketplace_seller_payment_accounts', 'entity_id');
    }
}
