<?php

namespace TCGCollective\SellerMembership\Model\ResourceModel\MembershipAddon;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';
    
    /**
     * Dependency Initilization
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init(
            \TCGCollective\SellerMembership\Model\MembershipAddon::class,
            \TCGCollective\SellerMembership\Model\ResourceModel\MembershipAddon::class
        );
        $this->_map['fields']['entity_id'] = 'main_table.entity_id';
    }
}