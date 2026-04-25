<?php

namespace TCGCollective\MarketPlace\Model\ResourceModel\Customer\Grid;

use Magento\Customer\Model\ResourceModel\Grid\Collection as CoreCollection;
use TCGCollective\MarketPlace\Helper\AdminWebsite;

class Collection extends CoreCollection
{
    private AdminWebsite $adminWebsite;

    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        AdminWebsite $adminWebsite,
        $mainTable = 'customer_grid_flat',
        $resourceModel = \Magento\Customer\Model\ResourceModel\Customer::class,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timeZone = null
    ) {
        $this->adminWebsite = $adminWebsite;
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $localeResolver,
            $mainTable,
            $resourceModel,
            $timeZone
        );
    }

    /**
     * Apply website/store restriction EARLY
     */
    protected function _initSelect()
    {
        // dd('test');
        parent::_initSelect();

        // Skip for full-access admin
        if ($this->adminWebsite->isAllWebsitesAllowed()) {
            return $this;
        }

        $websiteIds = $this->adminWebsite->getAllowedWebsiteIds();

        if (!empty($websiteIds)) {
            $this->addFieldToFilter('main_table.website_id', ['in' => $websiteIds]);
        }

        return $this;
    }
}
