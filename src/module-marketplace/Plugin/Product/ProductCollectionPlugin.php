<?php

namespace TCGCollective\MarketPlace\Plugin\Product;

use Magento\Catalog\Model\ResourceModel\Product\Collection;
use TCGCollective\MarketPlace\Helper\AdminWebsite;

class ProductCollectionPlugin
{
    private AdminWebsite $adminWebsite;

    public function __construct(
        AdminWebsite $adminWebsite
    ) {
        $this->adminWebsite = $adminWebsite;
    }

    public function beforeAddWebsiteFilter(
        Collection $subject,
        $websites = null
    ) {
        // Skip if user can access all websites
        if ($this->adminWebsite->isAllWebsitesAllowed()) {
            return [$websites];
        }

        $allowedWebsiteIds = $this->adminWebsite->getAllowedWebsiteIds();

        if ($websites !== null) {
            $websites = array_intersect((array)$websites, $allowedWebsiteIds);
        } else {
            $websites = $allowedWebsiteIds;
        }

        return [$websites];
    }

    public function beforeLoad(Collection $subject, $printQuery = false, $logQuery = false)
    {
        // Skip entirely for full-access admin
        if ($this->adminWebsite->isAllWebsitesAllowed()) {
            return [$printQuery, $logQuery];
        }

        if ($subject->getFlag('website_restricted_applied')) {
            return [$printQuery, $logQuery];
        }

        $websiteIds = $this->adminWebsite->getAllowedWebsiteIds();
        if (empty($websiteIds)) {
            return [$printQuery, $logQuery];
        }

        $subject->setFlag('website_restricted_applied', true);
        $subject->addWebsiteFilter($websiteIds);

        return [$printQuery, $logQuery];
    }
}
