<?php

namespace TCGCollective\MarketPlace\Plugin\Email;

use Magento\Email\Model\ResourceModel\Template\Collection;
use TCGCollective\MarketPlace\Helper\AdminWebsite;

class TemplateCollectionPlugin
{
    private AdminWebsite $adminWebsite;

    public function __construct(
        AdminWebsite $adminWebsite
    ) {
        $this->adminWebsite = $adminWebsite;
    }

    public function beforeLoad(Collection $subject, $printQuery = false, $logQuery = false)
    {
        // Full-access admin → skip
        if ($this->adminWebsite->isAllWebsitesAllowed()) {
            return [$printQuery, $logQuery];
        }

        // Prevent double filter
        if ($subject->getFlag('website_restricted_applied')) {
            return [$printQuery, $logQuery];
        }

        $websiteIds = $this->adminWebsite->getAllowedWebsiteIds();

        if (!empty($websiteIds)) {
            $subject->addFieldToFilter(
                'main_table.website_id',
                ['in' => $websiteIds]
            );
        }

        $subject->setFlag('website_restricted_applied', true);

        return [$printQuery, $logQuery];
    }
}
