<?php

namespace TCGCollective\SellerMembership\Model\Config\Source;

class MembershipCountry extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    protected $websiteCollectionCache;

    /**
     * @param \TCGCollective\MarketPlace\Model\WebsiteCollectionCache
     */
    public function __construct(
        \TCGCollective\MarketPlace\Model\WebsiteCollectionCache $websiteCollectionCache
    ) {
        $this->websiteCollectionCache = $websiteCollectionCache;
    }

    public function getAllOptions()
    {
        $options = [];

        $options[] = [
            'label' => __('Not Listing Country Membership'),
            'value' => ''
        ];

        $websiteCollection = $this->websiteCollectionCache->getWebsiteCollection();
        if ($websiteCollection) {
            $websites = $websiteCollection['items'] ?? [];
            if ($websites) {
                foreach ($websites as $website) {
                    $options[] = [
                        'label' => $website['name'],
                        'value' => $website['website_id']
                    ];
                }
            }
        }

        return $options;
    }
}
