<?php

namespace TCGCollective\MarketPlace\Model\ResourceModel\Seller\Grid;

use Lof\MarketPlace\Model\ResourceModel\Seller\Collection as SellerCollection;
use TCGCollective\MarketPlace\Helper\AdminWebsite;

class Collection extends SellerCollection
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

        if (!$this->adminWebsite->isAllWebsitesAllowed()) {
            $storeIds = $this->adminWebsite->getAllowedStoreIds();

            if (!empty($storeIds)) {
                // dd($storeIds);             
                $this->addFieldToFilter('main_table.store_id', ['in' => $storeIds]);
                // $this->load();
                // dd((string) $this->getSelect());
            }
        }
        
        return $this;
    }
}
