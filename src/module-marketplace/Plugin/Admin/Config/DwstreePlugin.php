<?php

namespace TCGCollective\MarketPlace\Plugin\Admin\Config;

use Magento\Config\Block\System\Config\Dwstree;
use TCGCollective\MarketPlace\Helper\AdminWebsite;

class DwstreePlugin
{
    private AdminWebsite $adminWebsite;

    public function __construct(AdminWebsite $adminWebsite)
    {
        $this->adminWebsite = $adminWebsite;
    }

    /**
     * Filter websites & stores BEFORE tabs are rendered
     */
    public function aroundInitTabs(Dwstree $subject, callable $proceed)
    {
        // Full-access admin → no restriction
        if ($this->adminWebsite->isAllWebsitesAllowed()) {
            return $proceed();
        }

        $allowedWebsiteIds = $this->adminWebsite->getAllowedWebsiteIds();
        $allowedStoreIds   = $this->adminWebsite->getAllowedStoreIds();

        /** Temporarily filter StoreManager data */
        $storeManager = $subject->_storeManager;

        $websites = [];
        foreach ($storeManager->getWebsites(true) as $website) {
            if (!in_array($website->getId(), $allowedWebsiteIds)) {
                continue;
            }

            // Filter stores inside website
            $stores = [];
            foreach ($website->getStores() as $store) {
                if (in_array($store->getId(), $allowedStoreIds)) {
                    $stores[$store->getId()] = $store;
                }
            }

            $website->setStores($stores);
            $websites[$website->getId()] = $website;
        }

        /**
         * Hacky but safe:
         * Override storeManager cache JUST for this block execution
         */
        $storeManager->reinitStores();
        $storeManager->setCurrentStore('admin');

        // Monkey-patch websites cache
        $reflection = new \ReflectionClass($storeManager);
        if ($reflection->hasProperty('_websites')) {
            $prop = $reflection->getProperty('_websites');
            $prop->setAccessible(true);
            $prop->setValue($storeManager, $websites);
        }

        return $proceed();
    }
}
