<?php

namespace TCGCollective\MarketPlace\Model\ResourceModel\Order;

use Magento\Sales\Model\ResourceModel\Order\Collection as CoreCollection;
use TCGCollective\MarketPlace\Helper\AdminWebsite;

class Collection extends CoreCollection
{
    private AdminWebsite $adminWebsite;

    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot $entitySnapshot,
        \Magento\Framework\DB\Helper $coreResourceHelper,
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
            $coreResourceHelper,
            $connection,
            $resource
        );
    }

    /**
     * Apply website restriction EARLY
     */
    protected function _initSelect()
    {
        parent::_initSelect();

        $storeIds = $this->adminWebsite->getAllowedStoreIds();
        
        if (!empty($storeIds)) {
            $this->addFieldToFilter('main_table.store_id', ['in' => $storeIds]);
        }
        // dd($this->getSelectSql(true));
        return $this;
    }
}
