<?php

namespace TCGCollective\MarketPlace\Model\ResourceModel\Commission\Grid;

use Lof\MarketPlace\Model\ResourceModel\Commission\Collection as CommissionCollection;
use TCGCollective\MarketPlace\Helper\AdminWebsite;

class Collection extends CommissionCollection
{
    protected AdminWebsite $adminWebsite;

    public function __construct(
        AdminWebsite $adminWebsite,
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\EntityManager\MetadataPool $metadataPool,
        $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        $this->adminWebsite = $adminWebsite;
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $storeManager,
            $metadataPool,
            $connection,
            $resource
        );
    }

    protected function _initSelect()
    {
        parent::_initSelect();

        // Restrict by admin website
        if (!$this->adminWebsite->isAllWebsitesAllowed()) {
            $storeIds = $this->adminWebsite->getAllowedStoreIds();

            if (!empty($storeIds)) {
                $this->addStoreFilter($storeIds);
            }
        }

        return $this;
    }
}
