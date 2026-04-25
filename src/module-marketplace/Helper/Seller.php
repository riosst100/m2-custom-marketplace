<?php

namespace TCGCollective\MarketPlace\Helper;

use Magento\Framework\App\CacheInterface;

class Seller extends \Magento\Framework\App\Helper\AbstractHelper
{
    const CACHE_TAG = 'marketplace_seller_helper_cache';

    protected $_sellers = [];

    protected $cache;
    protected $sellerDataCache;
    protected $storesHelper;
    protected $sellerFactory;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        CacheInterface $cache,
        \TCGCollective\MarketPlace\Model\SellerDataCache $sellerDataCache,
        \TCGCollective\MarketPlace\Helper\Stores $storesHelper,
        \Lof\MarketPlace\Model\SellerFactory $sellerFactory
    ) {
        $this->cache = $cache;
        $this->sellerDataCache = $sellerDataCache;
        $this->storesHelper = $storesHelper;
        $this->sellerFactory = $sellerFactory;
        parent::__construct(
            $context
        );
    }

    public function getSellerStoresOptionsArray() 
    {
        $cacheKey = self::CACHE_TAG . '_seller_stores_options_array';
        $cachedData = $this->cache->load($cacheKey);
        if ($cachedData) {
            return unserialize($cachedData);
        }

        $options = $this->storesHelper->getStoresOptionsArray();
        if ($options) {

            // Filter stores based on seller listing stores only
            $seller = $this->getCurrentSeller();
            if ($seller) {
                $sellerListingWebsiteIds = isset($seller['listing_website_ids']) && $seller['listing_website_ids'] ? 
                explode(",", $seller['listing_website_ids']) : [];

                $options = array_filter($options, function ($item) use ($sellerListingWebsiteIds) {
                    return in_array((int)$item['value'], $sellerListingWebsiteIds);
                });
            }
        }

        return $options;
    }

    public function getSellerNameById($sellerId) 
    {
        $sellerModel = $this->getSellerById($sellerId);
        if ($sellerModel) {
            return $sellerModel->getName();
        }

        return null;
    }

    public function getSellerById($sellerId) 
    {
        if (!isset($this->_sellers[$sellerId])) {
            $this->_sellers[$sellerId] = $this->sellerFactory->create()->load($sellerId);
        }

        return $this->_sellers[$sellerId];
    }

    public function getCurrentSeller() 
    {
        return $this->sellerDataCache->getSellerData();
    }
}