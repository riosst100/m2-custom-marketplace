<?php

namespace TCGCollective\MarketPlace\Model\ResourceModel\SalesRule\Grid;

use Magento\Framework\DB\Select;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Quote\Model\Quote\Address;
use Magento\SalesRule\Api\Data\CouponInterface;
use Magento\SalesRule\Model\Coupon;
use Magento\SalesRule\Model\Rule;
use Magento\SalesRule\Model\ResourceModel\Rule\Quote\Collection as CoreCollection;
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
        AdminWebsite $adminWebsite,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $date,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null,
        Json $serializer = null
    ) {
        $this->adminWebsite = $adminWebsite;
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $date,            
            $connection,
            $resource,
            $serializer
        );
    }

    /**
     * Apply website restriction for GRID DATA
     */
    public function _initSelect()
    {
        parent::_initSelect();

        if ($this->adminWebsite->isAllWebsitesAllowed()) {
            return $this;
        }

        if ($this->getFlag('website_restricted_applied')) {
            return $this;
        }

        $websiteIds = $this->adminWebsite->getAllowedWebsiteIds();

        if (!empty($websiteIds)) {
            $this->ensureWebsiteJoin($this->getSelect());

            $this->getSelect()
                ->where('srw.website_id IN (?)', $websiteIds)
                ->group('main_table.rule_id');
        }

        $this->setFlag('website_restricted_applied', true);

        return $this;
    }

    /**
     * Fix COUNT query
     */
    public function getSelectCountSql()
    {
        $countSelect = parent::getSelectCountSql();

        if ($this->adminWebsite->isAllWebsitesAllowed()) {
            return $countSelect;
        }

        $websiteIds = $this->adminWebsite->getAllowedWebsiteIds();

        if (!empty($websiteIds)) {
            $this->ensureWebsiteJoin($countSelect);

            $countSelect
                ->where('srw.website_id IN (?)', $websiteIds)
                ->group('main_table.rule_id');
        }

        return $countSelect;
    }

    private function ensureWebsiteJoin(Select $select): void
    {
        $from = $select->getPart(Select::FROM);

        if (!isset($from['srw'])) {
            $select->joinLeft(
                ['srw' => $this->getTable('salesrule_website')],
                'main_table.rule_id = srw.rule_id',
                []
            );
        }
    }
}
