<?php

namespace TCGCollective\MarketPlace\Helper;

use Magento\Backend\Model\Auth\Session as AdminSession;
use Magento\Store\Model\StoreManagerInterface;

class AdminWebsite
{
    public function __construct(
        private AdminSession $adminSession,
        private StoreManagerInterface $storeManager
    ) {}

    public function getAllowedWebsiteIds(): array
    {
        $user = $this->adminSession->getUser();

        if (!$user || !$user->getWebsiteId()) {
            return array_keys($this->storeManager->getWebsites());
        }

        return [(int)$user->getWebsiteId()];
    }

    public function getAllowedStoreIds(): array
    {
        $storeIds = [];

        foreach ($this->getAllowedWebsiteIds() as $websiteId) {
            $website = $this->storeManager->getWebsite($websiteId);
            foreach ($website->getStores() as $store) {
                $storeIds[] = (int)$store->getId();
            }
        }

        return $storeIds;
    }

    public function isAllWebsitesAllowed(): bool
    {
        $allowed = $this->getAllowedWebsiteIds();
        $all = array_keys($this->storeManager->getWebsites());

        sort($allowed);
        sort($all);

        return $allowed === $all;
    }

}
