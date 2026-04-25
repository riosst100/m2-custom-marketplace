<?php

namespace TCGCollective\MarketPlace\Plugin\CatalogRule;

use Magento\CatalogRule\Model\ResourceModel\Rule\Collection;
use TCGCollective\MarketPlace\Helper\AdminWebsite;

class RuleCollectionPlugin
{
    private AdminWebsite $adminWebsite;

    public function __construct(
        AdminWebsite $adminWebsite
    ) {
        $this->adminWebsite = $adminWebsite;
    }

    public function beforeLoad(Collection $subject, $printQuery = false, $logQuery = false)
    {
        // Full access → no restriction
        if ($this->adminWebsite->isAllWebsitesAllowed()) {
            return [$printQuery, $logQuery];
        }

        // Prevent duplicate join
        if ($subject->getFlag('website_restricted_applied')) {
            return [$printQuery, $logQuery];
        }

        $websiteIds = $this->adminWebsite->getAllowedWebsiteIds();

        if (!empty($websiteIds)) {
            $connection = $subject->getConnection();
            $select = $subject->getSelect();

            $select->join(
                ['crw' => $subject->getTable('catalogrule_website')],
                'main_table.rule_id = crw.rule_id',
                []
            )->where(
                $connection->quoteInto('crw.website_id IN (?)', $websiteIds)
            );
        }

        $subject->setFlag('website_restricted_applied', true);

        return [$printQuery, $logQuery];
    }

    public function beforeRenderFiltersBefore(Collection $subject)
    {
        // Full access → do nothing
        if ($this->adminWebsite->isAllWebsitesAllowed()) {
            return;
        }

        // Prevent duplicate join (VERY important)
        if ($subject->getFlag('website_restricted_applied')) {
            return;
        }

        $websiteIds = $this->adminWebsite->getAllowedWebsiteIds();
        if (empty($websiteIds)) {
            return;
        }

        $connection = $subject->getConnection();

        $subject->getSelect()->join(
            ['crw' => $subject->getTable('catalogrule_website')],
            'main_table.rule_id = crw.rule_id',
            []
        )->where(
            $connection->quoteInto('crw.website_id IN (?)', $websiteIds)
        );

        $subject->setFlag('website_restricted_applied', true);
    }
}
