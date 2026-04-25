<?php

namespace TCGCollective\MarketPlace\Model\Source;

class CountryByWebsiteId implements \Magento\Framework\Option\ArrayInterface
{
    protected $websiteCollectionCache;
    protected $sellerHelper;
    
    public function __construct(
        \TCGCollective\MarketPlace\Model\WebsiteCollectionCache $websiteCollectionCache,
        \Lof\MarketPlace\Helper\Seller $sellerHelper
    ) {
        $this->websiteCollectionCache = $websiteCollectionCache;
        $this->sellerHelper = $sellerHelper;
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];

        $seller = $this->sellerHelper->getSeller();
        $listingSellerWebsiteIds = explode(',', $seller->getListingWebsiteIds());

        $websiteCollection = $this->websiteCollectionCache->getWebsiteCollection();
        if ($websiteCollection) {
            $websites = $websiteCollection['items'] ?? [];
            if ($websites) {
                foreach ($websites as $website) {
                    $websiteId = $website['website_id'] ?? null;
                    $websiteName = $website['name'] ?? null;
                    if (!$websiteId || !in_array($websiteId, $listingSellerWebsiteIds)) {
                        continue;
                    }

                    $options[] = [
                        'label' => $websiteName,
                        'value' => $websiteId
                    ];
                }
            }
        }
        // dd($seller->getListingWebsiteIds());
        return $options;
    }
}
