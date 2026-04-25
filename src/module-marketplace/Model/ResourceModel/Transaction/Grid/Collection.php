<?php

namespace TCGCollective\MarketPlace\Model\ResourceModel\Transaction\Grid;

use Magento\Sales\Model\ResourceModel\Transaction\Grid\Collection as CoreCollection;
use TCGCollective\MarketPlace\Helper\AdminWebsite;

class Collection extends CoreCollection
{
    protected AdminWebsite $adminWebsite;

    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot $entitySnapshot,
        \Magento\Framework\Registry $registryManager,
        AdminWebsite $adminWebsite,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        $this->adminWebsite = $adminWebsite;
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $entitySnapshot,
            $registryManager,
            $connection,
            $resource
        );
    }

    protected function _initSelect()
    {
        parent::_initSelect();

        $storeIds = $this->adminWebsite->getAllowedStoreIds();

        /**
         * Admin = All Websites → no filter
         */
        if (empty($storeIds)) {
            return $this;
        }

        /**
         * IMPORTANT:
         * - sales_order is ALREADY joined as alias "so"
         * - just filter, DO NOT join again
         */
        $this->addFieldToFilter('so.store_id', ['in' => $storeIds]);

        return $this;
    }
}
