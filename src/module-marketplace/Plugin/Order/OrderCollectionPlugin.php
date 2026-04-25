<?php

namespace TCGCollective\MarketPlace\Plugin\Order;

use Magento\Sales\Model\ResourceModel\Order\Collection;
use TCGCollective\MarketPlace\Helper\AdminWebsite;

class OrderCollectionPlugin
{
    public function __construct(
        private AdminWebsite $adminWebsite
    ) {}

    public function beforeLoad(Collection $collection)
    {
        $storeIds = $this->adminWebsite->getAllowedStoreIds();

        if (!empty($storeIds)) {
            $collection->addFieldToFilter('store_id', ['in' => $storeIds]);
        }
    }
}
