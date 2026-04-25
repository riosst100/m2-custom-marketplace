<?php

namespace TCGCollective\MarketPlace\Plugin\Config;

use Magento\Store\Model\System\Store;
use TCGCollective\MarketPlace\Helper\AdminWebsite;

class SystemStorePlugin
{
    private AdminWebsite $adminWebsite;

    public function __construct(
        AdminWebsite $adminWebsite
    ) {
        $this->adminWebsite = $adminWebsite;
    }

    /**
     * Filter WEBSITE scope
     */
    public function afterGetWebsiteCollection(Store $subject, $result)
    {
        if ($this->adminWebsite->isAllWebsitesAllowed()) {
            return $result;
        }

        $allowedWebsiteIds = $this->adminWebsite->getAllowedWebsiteIds();

        if (empty($allowedWebsiteIds)) {
            return $result;
        }

        foreach ($result as $key => $website) {
            if (!in_array((int)$website->getId(), $allowedWebsiteIds, true)) {
                unset($result[$key]);
            }
        }

        return $result;
    }

    /**
     * Filter STORE VIEW scope
     */
    public function afterGetStoreCollection(Store $subject, $result)
    {
        if ($this->adminWebsite->isAllWebsitesAllowed()) {
            return $result;
        }

        $allowedStoreIds = $this->adminWebsite->getAllowedStoreIds();

        if (empty($allowedStoreIds)) {
            return $result;
        }

        foreach ($result as $key => $store) {
            if (!in_array((int)$store->getId(), $allowedStoreIds, true)) {
                unset($result[$key]);
            }
        }

        return $result;
    }
}
