<?php

namespace TCGCollective\MarketPlace\Model\ResourceModel\Rma;

use Lofmp\Rma\Model\ResourceModel\Rma\Collection as CoreCollection;
use TCGCollective\MarketPlace\Helper\AdminWebsite;

class Collection extends CoreCollection
{
    protected AdminWebsite $adminWebsite;

    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Lofmp\Rma\Model\ResourceModel\Status\CollectionFactory $statusCollectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        AdminWebsite $adminWebsite,
        $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        $this->adminWebsite = $adminWebsite;

        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $localeDate,
            $statusCollectionFactory,
            $storeManager,
            $connection,
            $resource
        );
    }

    /**
     * Apply store restriction
     */
    protected function _initSelect()
    {
        parent::_initSelect();

        // Full access admin → no restriction
        if ($this->adminWebsite->isAllWebsitesAllowed()) {
            return $this;
        }

        // Prevent duplicate filter
        if ($this->getFlag('store_restricted_applied')) {
            return $this;
        }

        $storeIds = $this->adminWebsite->getAllowedStoreIds();

        if (!empty($storeIds)) {
            $this->getSelect()->where(
                'main_table.store_id IN (?)',
                $storeIds
            );
        }

        $this->setFlag('store_restricted_applied', true);

        return $this;
    }
}
