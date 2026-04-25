<?php

namespace TCGCollective\MarketPlace\Helper;

use Magento\Framework\App\CacheInterface;

class Stores extends \Magento\Framework\App\Helper\AbstractHelper
{
    const CACHE_TAG = 'marketplace_stores_helper_cache';

    protected $cache;
    protected $websiteCollectionCache;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        CacheInterface $cache,
        \TCGCollective\MarketPlace\Model\WebsiteCollectionCache $websiteCollectionCache,
    ) {
        $this->cache = $cache;
        $this->websiteCollectionCache = $websiteCollectionCache;
        parent::__construct(
            $context
        );
    }

    public function getStoresGridOptionsArray()
    {
        $result = [];
        $arr = $this->getStoresOptionsArray();
        foreach ($arr as $item) {
            $result[$item['value']] = $item['label'];
        }

        return $result;
    }

    public function getStoresOptionsArray() 
    {
        $cacheKey = self::CACHE_TAG . '_stores_options_array';
        $cachedData = $this->cache->load($cacheKey);
        if ($cachedData) {
            return unserialize($cachedData);
        }

        $options = [];

        $websiteCollection = $this->websiteCollectionCache->getWebsiteCollection();
        if ($websiteCollection) {
            $websites = $websiteCollection['items'] ?? [];
            if ($websites) {
                foreach ($websites as $website) {
                    $websiteId = $website['website_id'] ?? null;
                    $websiteName = $website['name'] ?? null;
                    if (!$websiteId) {
                        continue;
                    }

                    $options[] = [
                        'label' => $websiteName,
                        'value' => $websiteId
                    ];
                }
            }
        }

        usort($options, fn($b, $a) => $b['label'] <=> $a['label']);
        
        $this->cache->save(
            serialize($options),
            $cacheKey,
            [self::CACHE_TAG]
        );

        return $options;
    }
}