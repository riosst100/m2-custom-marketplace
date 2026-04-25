<?php

namespace TCGCollective\MarketPlace\Model\ResourceModel\CatalogRule\Grid;

use Magento\CatalogRule\Model\ResourceModel\Grid\Collection as CoreCollection;
use TCGCollective\MarketPlace\Helper\AdminWebsite;

class Collection extends CoreCollection
{
    private AdminWebsite $adminWebsite;

    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        AdminWebsite $adminWebsite,
        $connection = null,
        $resource = null
    ) {
        $this->adminWebsite = $adminWebsite;
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $connection,
            $resource
        );
    }

    protected function _initSelect()
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
            $this->getSelect()->join(
                ['crw' => $this->getTable('catalogrule_website')],
                'main_table.rule_id = crw.rule_id',
                []
            )->where(
                'crw.website_id IN (?)',
                $websiteIds
            )->group(
                'main_table.rule_id'
            );
        }

        $this->setFlag('website_restricted_applied', true);

        return $this;
    }

    /**
     * 🔑 Fix COUNT(*) query
     */
    public function getSelectCountSql()
    {
        $countSelect = parent::getSelectCountSql();

        if ($this->adminWebsite->isAllWebsitesAllowed()) {
            return $countSelect;
        }

        $websiteIds = $this->adminWebsite->getAllowedWebsiteIds();

        if (!empty($websiteIds)) {
            $countSelect->join(
                ['crws' => $this->getTable('catalogrule_website')],
                'main_table.rule_id = crws.rule_id',
                []
            )->where(
                'crws.website_id IN (?)',
                $websiteIds
            );
        }

        return $countSelect;
    }
}
