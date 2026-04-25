<?php

namespace TCGCollective\MarketPlace\Plugin\Invoice;

use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;
use TCGCollective\MarketPlace\Helper\AdminWebsite;

class InvoiceGridPlugin
{
    public function __construct(
        private AdminWebsite $adminWebsite
    ) {}

    public function beforeLoad(SearchResult $subject, $printQuery = false, $logQuery = false)
    {   
        // Apply ONLY to in array grid
        $tableNames = array('sales_invoice_grid', 'sales_shipment_grid', 'sales_creditmemo_grid');
        // if ($subject->getMainTable() == $subject->getTable('sales_order_grid')) {
        if (!in_array($subject->getMainTable(), $tableNames)) {
            return [$printQuery, $logQuery];
        }

        // Prevent double execution
        if ($subject->getFlag('admin_website_filter_applied')) {
            return [$printQuery, $logQuery];
        }

        $storeIds = $this->adminWebsite->getAllowedStoreIds();

        if (empty($storeIds)) {
            return [$printQuery, $logQuery];
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
         * Filter by allowed store ids
         */
        $select->where('so.store_id IN (?)', $storeIds);

        $subject->setFlag('admin_website_filter_applied', true);
        // dd($select->__toString());

        return [$printQuery, $logQuery];
    }
}
