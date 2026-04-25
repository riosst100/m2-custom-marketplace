<?php

namespace TCGCollective\MarketPlace\Plugin\Adminhtml\Store;

use Magento\Backend\Block\Store\Switcher;
use TCGCollective\MarketPlace\Helper\AdminWebsite;
use Magento\Store\Api\Data\WebsiteInterface;
use Magento\Store\Api\Data\StoreInterface;

class RestrictSwitcher
{
    public function __construct(
        private AdminWebsite $adminWebsite
    ) {}

    /**
     * Filter websites in scope switcher
     */
    public function afterGetWebsites(
        Switcher $subject,
        array $websites
    ): array {
        // Super admin → show all
        if ($this->adminWebsite->isAllWebsitesAllowed()) {
            return $websites;
        }

        $allowed = $this->adminWebsite->getAllowedWebsiteIds();
        // dd($allowed);
        return array_filter(
            $websites,
            fn (WebsiteInterface $website)
                => in_array((int)$website->getId(), $allowed)
        );
    }

    /**
     * Filter stores per website
     */
    public function afterGetStores(
        Switcher $subject,
        array $stores
    ): array {
        // Super admin → show all
        if ($this->adminWebsite->isAllWebsitesAllowed()) {
            return $stores;
        }

        $allowedStoreIds = $this->adminWebsite->getAllowedStoreIds();

        return array_filter(
            $stores,
            fn (StoreInterface $store)
                => in_array((int)$store->getId(), $allowedStoreIds)
        );
    }

    /**
     * Hide "Default Config"
     */
    public function afterHasDefaultOption(
        Switcher $subject,
        bool $result
    ): bool {
        if ($this->adminWebsite->isAllWebsitesAllowed()) {
            return $result;
        }

        return false;
    }
}
