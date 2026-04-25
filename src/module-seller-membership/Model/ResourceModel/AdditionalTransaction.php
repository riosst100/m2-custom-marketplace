<?php

namespace TCGCollective\SellerMembership\Model\ResourceModel;

class AdditionalTransaction extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Dependency Initilization
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init("marketplace_membership_addon_transaction", "transaction_id");
    }

    public function getFields()
    {
        $fields = $this->getConnection()->describeTable($this->getMainTable());
        return $fields;
    }
}