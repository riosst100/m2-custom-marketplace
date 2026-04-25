<?php

namespace TCGCollective\MarketPlace\Model;

use Magento\Framework\App\CacheInterface;
use Magento\Store\Model\ResourceModel\Store\CollectionFactory;

class AttributeOptionsCache
{
    const CACHE_TAG = 'store_collection_cache'; // Custom cache identifier

    private $cache;
    private $collectionFactory;

    public function __construct(
        CacheInterface $cache,
        CollectionFactory $collectionFactory
    ) {
        $this->cache = $cache;
        $this->collectionFactory = $collectionFactory;
    }

    public function getRow($field, $value) 
    {
        $dataArr = $this->getData();
        $dataArr = $dataArr['items'] ?? [];
        if ($dataArr) {
            $filteredItem = array_filter($dataArr, function($item) use ($value,$field) {
                return $item[$field] === $value;
            });

            $result = array_values($filteredItem)[0] ?? null;
            return $result;
        }

        return null;
    }

    public function getData()
    {
        $cacheKey = self::CACHE_TAG;
        $cachedData = $this->cache->load($cacheKey);

        if ($cachedData) {
            return unserialize($cachedData); // Return cached collection
        }

        // Fetch collection and cache it permanently
        $collection = $this->collectionFactory->create();
        $collectionData = $collection->toArray();
        $this->cache->save(
            serialize($collectionData), // Serialized data
            $cacheKey,                  // Cache key
            [self::CACHE_TAG]           // Cache tags
        );

        return $collectionData;
    }

    /**
     * Clear cache manually if needed.
     */
    public function clearCache()
    {
        $this->cache->remove(self::CACHE_TAG); // Remove cache entry
    }
}
