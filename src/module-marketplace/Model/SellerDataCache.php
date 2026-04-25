<?php

namespace TCGCollective\MarketPlace\Model;

use Magento\Framework\App\CacheInterface;
use Lof\MarketPlace\Model\ResourceModel\Seller\CollectionFactory as SellerCollectionFactory;

class SellerDataCache
{
    const CACHE_TAG = 'seller_data_cache'; // Custom cache identifier

    private $cache;
    private $sellerCollectionFactory;
    private $customerSession;
    protected $_seller_by_customers = [];

    public function __construct(
        CacheInterface $cache,
        SellerCollectionFactory $sellerCollectionFactory,
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->cache = $cache;
        $this->sellerCollectionFactory = $sellerCollectionFactory;
        $this->customerSession = $customerSession;
    }

    /**
     * Get cached website collection or load and cache it.
     *
     * @return array
     */
    public function getSellerData()
    {
        $cacheKey = self::CACHE_TAG;
        $cachedData = $this->cache->load($cacheKey);

        if ($cachedData) {
            return unserialize($cachedData); // Return cached collection
        }

        // Fetch collection and cache it permanently
        $seller = $this->getSellerByCustomerId($this->getCustomerId());
        
        $this->cache->save(
            serialize($seller->getData()), // Serialized data
            $cacheKey,                  // Cache key
            [self::CACHE_TAG]           // Cache tags
        );

        return $seller->getData();
    }

    public function getCustomerId()
    {
        $customer = $this->customerSession->getCustomer();

        return $customer->getId();
    }

    public function getSellerByCustomerId($customer_id)
    {
        if (!isset($this->_seller_by_customers[$customer_id])) {
            $this->_seller_by_customers[$customer_id] = $this->sellerCollectionFactory->create()
            ->addFieldToFilter('customer_id', $customer_id)->getFirstItem();
        }
        
        return $this->_seller_by_customers[$customer_id];
    }

    /**
     * Get cached website collection or load and cache it.
     *
     * @return array
     */
    public function getSellerById($sellerId = null)
    {
        if ($sellerId) {
            $cacheKey = self::CACHE_TAG . '_' . $sellerId;
            $cachedData = $this->cache->load($cacheKey);

            if ($cachedData) {
                return unserialize($cachedData); // Return cached collection
            }

            // Fetch collection and cache it permanently
            $sellerCollection = $this->sellerCollectionFactory->create()
            ->addFieldToFilter('seller_id', $sellerId);
            // ->addFieldToSelect('name');

            // dd($sellerCollection->getSelect()->__toString());

            if ($sellerCollection->getSize()) {
                $sellerData = $sellerCollection->getFirstItem()->getData();
                $this->cache->save(
                    serialize($sellerData), // Serialized data
                    $cacheKey,                  // Cache key
                    [$cacheKey]           // Cache tags
                );

                return $sellerData;
            }
        }

        return null;
    }

    /**
     * Clear cache manually if needed.
     */
    public function clearCache()
    {
        $this->cache->remove(self::CACHE_TAG); // Remove cache entry
    }
}
