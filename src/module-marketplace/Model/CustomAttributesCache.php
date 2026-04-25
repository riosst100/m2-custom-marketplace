<?php

namespace TCGCollective\MarketPlace\Model;

use Magento\Framework\App\CacheInterface;
use TCGCollective\ProductAttributesLink\Model\ResourceModel\CustomAttributes\CollectionFactory;

class CustomAttributesCache
{
    const CACHE_TAG = 'custom_attributes_cache'; // Custom cache identifier

    private $cache;
    private $collectionFactory;

    public function __construct(
        CacheInterface $cache,
        CollectionFactory $collectionFactory
    ) {
        $this->cache = $cache;
        $this->collectionFactory = $collectionFactory;
    }

    public function getCustomAttributesOptions($attributeCode, $mainAttrCode, $mainAttrVal) {
        $options = [];

        $cacheKey = self::CACHE_TAG . '_' . $attributeCode;
        $cachedData = $this->cache->load($cacheKey);

        if ($cachedData) {
            // return unserialize($cachedData); // Return cached collection
        }

        // Fetch collection and cache it permanently
        $customAttributesData = $this->getCustomAttributesData();
        if ($customAttributesData) {
            $result = array_filter($customAttributesData["items"], function($item) use ($attributeCode, $mainAttrCode, $mainAttrVal) {
                return $item["attribute_code"] === $attributeCode
                    && $item[$mainAttrCode] === $mainAttrVal;
            });

            if ($result) {
                $options = array_map(function($item) {
                    return $item['attribute_value'];
                }, $result);
            }
        }

        // Save cache
        $this->cache->save(
            serialize($options), // Serialized data
            $cacheKey,                  // Cache key
            [self::CACHE_TAG]           // Cache tags
        );

        return $options;
    }

    public function getCustomAttributesData() {
        $options = [];

        $cacheKey = self::CACHE_TAG;
        $cachedData = $this->cache->load($cacheKey);

        if ($cachedData) {
            return unserialize($cachedData); // Return cached collection
        }

        // Fetch collection and cache it permanently
        $collection = $this->collectionFactory->create();
        $options = $collection->toArray();

        // Save cache
        $this->cache->save(
            serialize($options), // Serialized data
            $cacheKey,                  // Cache key
            [self::CACHE_TAG]           // Cache tags
        );

        return $options;
    }

    /**
     * Clear cache manually if needed.
     */
    public function clearCache()
    {
        $this->cache->remove(self::CACHE_TAG); // Remove cache entry
    }
}