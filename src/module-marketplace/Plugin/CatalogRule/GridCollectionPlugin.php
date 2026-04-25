<?php

namespace TCGCollective\MarketPlace\Plugin\CatalogRule;

use Magento\CatalogRule\Model\ResourceModel\Grid\Collection;
use TCGCollective\MarketPlace\Helper\AdminWebsite;

class GridCollectionPlugin
{
    private AdminWebsite $adminWebsite;

    public function __construct(
        AdminWebsite $adminWebsite
    ) {
        $this->adminWebsite = $adminWebsite;
    }

    /**
     * addWebsitesToResult() is PUBLIC → plugin works
     */
    public function afterAddWebsitesToResult(Collection $subject, $result)
    {
        // Skip for full-access admin
        if ($this->adminWebsite->isAllWebsitesAllowed()) {
            return $result;
        }

        // Prevent duplicate where clauses
        if ($subject->getFlag('website_restricted_applied')) {
            return $result;
        }

        $websiteIds = $this->adminWebsite->getAllowedWebsiteIds();

        if (!empty($websiteIds)) {
            $subject->getSelect()->where(
                'catalogrule_website.website_id IN (?)',
                $websiteIds
            );
        }

        $subject->setFlag('website_restricted_applied', true);

        return $result;
    }
}
