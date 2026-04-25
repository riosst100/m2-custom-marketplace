<?php
namespace TCGCollective\MarketplaceProductPreview\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $flushCacheByIds;


    /**
     * @var \TCGCollective\MarketplaceProductPreview\Model\ProductPreviewTokenFactory
     */
    protected $productPreviewTokenFactory;

    protected $sellerProductFactory;

    protected $urlRewriteFactory;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \TCGCollective\MarketplaceProductPreview\Model\ProductPreviewTokenFactory $productPreviewTokenFactory,
        \Lof\MarketPlace\Model\SellerProductFactory $sellerProductFactory,
        \Magento\UrlRewrite\Model\UrlRewriteFactory $urlRewriteFactory
    ) {
        parent::__construct(
            $context
        );
        $this->productPreviewTokenFactory = $productPreviewTokenFactory;
        $this->sellerProductFactory = $sellerProductFactory;
        $this->urlRewriteFactory = $urlRewriteFactory;
    }

    public function removeProductPreviewToken($productPreviewToken) 
    {
        $collection = $this->productPreviewTokenFactory->create()
        ->getCollection()
        ->addFieldToFilter('token', $productPreviewToken);
        if ($collection->getSize()) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $this->flushCacheByIds = $objectManager ->create('\Magento\InventoryCache\Model\FlushCacheByProductIds');
            // $product = $productR->get('product_sku');
            // $product->cleanCache();
            // $this->_eventManager->dispatch('clean_cache_by_tags', ['object' => $product]);
            $this->flushCacheByIds->execute([$collection->getFirstItem()->getProductId()]);
            
            $collection->getFirstItem()->delete();
        }
    }

    public function getProductIdByUrlKey($urlKey) 
    {
        // url key must include .html
        $collection = $this->urlRewriteFactory->create()
        ->getCollection()
        ->addFieldToFilter('request_path', $urlKey);
        if ($collection->getSize()) {
            return $collection->getFirstItem()->getEntityId();
        }

        return null;
    }

    public function isValidProductPreviewTokenByUrlKey($productPreviewToken, $urlKey) 
    {
        $urlKey .= '.html';
        $productId = $this->getProductIdByUrlKey($urlKey);
        if ($productId) {
            return $this->isValidProductPreviewToken($productPreviewToken, $productId);
        }

        return false;
    }

    public function isValidProductPreviewToken($productPreviewToken, $productId)
    {
        if ($productPreviewToken && $productId) {
            $sellerId = $this->getSellerIdByProductId($productId);
            if ($sellerId) {
                $collection = $this->productPreviewTokenFactory->create()
                ->getCollection()
                ->addFieldToFilter('token', $productPreviewToken)
                ->addFieldToFilter('product_id', $productId)
                ->addFieldToFilter('seller_id', $sellerId);
                if ($collection->getSize()) {
                    return true;
                }
            }
        }

        return false;
    }

    public function getSellerIdByProductId($productId)
    {
        if ($productId) {
            $collection = $this->sellerProductFactory->create()
            ->getCollection()
            ->addFieldToFilter('product_id', $productId);
            if ($collection->getSize()) {
                return $collection->getFirstItem()->getData('seller_id');
            }
        }

        return null;
    }
}