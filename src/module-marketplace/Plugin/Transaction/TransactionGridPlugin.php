<?php

namespace TCGCollective\MarketPlace\Plugin\Transaction;

use Magento\Sales\Model\ResourceModel\Transaction\Grid\Collection;
use TCGCollective\MarketPlace\Helper\AdminWebsite;

class TransactionGridPlugin
{
    public function __construct(
        private AdminWebsite $adminWebsite
    ) {}

    /**
     * Apply website filter after select initialized
     */
    public function afterInitSelect(Collection $subject, $result)
    {
        dd('test');
        /**
         * Prevent duplicate joins
         */
        if ($subject->getFlag('admin_website_filter_applied')) {
            return $result;
        }

        $storeIds = $this->adminWebsite->getAllowedStoreIds();

        if (empty($storeIds)) {
            return $result;
        }

        $select = $subject->getSelect();

        /**
         * Join sales_order to get store_id
         */
        $select->join(
            ['so' => $subject->getTable('sales_order')],
            'main_table.order_id = so.entity_id',
            []
        );

        /**
         * Filter by store
         */
        $select->where('so.store_id IN (?)', $storeIds);

        $subject->setFlag('admin_website_filter_applied', true);

        return $result;
    }
}
