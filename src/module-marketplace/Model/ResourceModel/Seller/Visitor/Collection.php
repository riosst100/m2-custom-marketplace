<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace TCGCollective\MarketPlace\Model\ResourceModel\Seller\Visitor;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\TCGCollective\MarketPlace\Model\Seller\Visitor::class, \TCGCollective\MarketPlace\Model\ResourceModel\Seller\Visitor::class);
    }
}
