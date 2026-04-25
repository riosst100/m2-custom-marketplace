<?php

namespace TCGCollective\MarketPlace\Model\ResourceModel\SellerWebsite;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Eav\Model\Entity\Attribute\Source\SourceInterface;
use Magento\Framework\Data\OptionSourceInterface;

class Collection extends AbstractSource implements SourceInterface, OptionSourceInterface
{
    protected $websiteCollectionCache;

    public function __construct(
        \TCGCollective\MarketPlace\Model\WebsiteCollectionCache $websiteCollectionCache
    ) {
        $this->websiteCollectionCache = $websiteCollectionCache;
    }
    
    public function getOptionArray() {
        $websites = [];

        $websiteCollection = $this->websiteCollectionCache->getWebsiteCollection();
        if ($websiteCollection) {
            $websitesData = $websiteCollection['items'] ?? [];
            if ($websitesData) {
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $this->sellerHelper = $objectManager->create(\Lof\MarketPlace\Helper\Seller::class);

                $seller = $this->sellerHelper->getSeller();

                $sellerListingWebsiteIds = $this->getSellerListingWebsiteIds($seller);
                
                foreach ($websitesData as $index => $website) {
                    // dd($website);
                    $websiteId = $website['website_id'] ?? '';
                    $websiteName = $website['name'] ?? '';
                    $websiteCode = $website['code'] ?? '';

                    if (in_array($websiteId, $sellerListingWebsiteIds) || $websiteCode == $seller->getDefaultWebsiteCode()) {
                        $websites[$websiteId] = $websiteName;
                    }
                }
            }
        }
        
        return $websites;
    }

    public function getSellerListingWebsiteIds($seller) 
    {
        $listingWebsiteIdsArr = [];

        $currentListingCountry = $seller->getListingWebsiteIds();
        if ($currentListingCountry) {
            $listingWebsiteIdsArr = explode(",", $currentListingCountry);
        }

        return $listingWebsiteIdsArr;
    }
    
    public function getAllOptions() {
        $result = [];
        
        foreach (self::getOptionArray() as $index => $value) {
            $result[] = ['value' => $index, 'label' => $value];
        }
        
        return $result;
    }
}
