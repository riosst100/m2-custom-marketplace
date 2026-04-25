<?php
namespace TCGCollective\MarketPlace\Controller\Marketplace\Product;

use Magento\Framework\App\Action\Context;
use Magento\Framework\UrlInterface;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\ProductRepository;

class GetProductDataByCountry extends \Magento\Framework\App\Action\Action
{
    const CACHE_TAG = 'product_data_country_data_cache';

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $jsonFactory;

    /**
     * @var \TCGCollective\ProductAttributesLink\Helper\Data
     */
    protected $mappingHelper;

    protected $urlBuilder;
    protected $formKey;
    protected $priceCurrency;
    protected $storeManager;
    protected $productRepository;
    protected $helper;
    protected $websiteCollectionCache;
    protected $cache;

    /**
     * Save constructor.
     * @param Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
     * @param \TCGCollective\ProductAttributesLink\Helper\Data $mappingHelper
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \TCGCollective\ProductAttributesLink\Helper\Data $mappingHelper,
        UrlInterface $urlBuilder,
        FormKey $formKey,
        PriceCurrencyInterface $priceCurrency,
        StoreManagerInterface $storeManager,
        ProductRepository $productRepository,
        \Lof\MarketPlace\Helper\Data $helper,
        \TCGCollective\MarketPlace\Model\WebsiteCollectionCache $websiteCollectionCache,
        \Magento\Framework\App\CacheInterface $cache
    ) {
        $this->jsonFactory = $jsonFactory;
        $this->mappingHelper = $mappingHelper;
        $this->urlBuilder = $urlBuilder;
        $this->formKey = $formKey;
        $this->priceCurrency = $priceCurrency;
        $this->storeManager = $storeManager;
        $this->productRepository = $productRepository;
        $this->helper = $helper;
        $this->websiteCollectionCache = $websiteCollectionCache;
        $this->cache = $cache;
        parent::__construct(
            $context
        );
    }
    
	public function execute()
	{
        $resultJson = $this->jsonFactory->create();
        $resultJson->setHttpResponseCode(200);

        $data = [];

        $productId = $this->getRequest()->getParam('product_id');
        $storeId = $this->getRequest()->getParam('store_id');
        if ($productId) {
            $product = $this->getProductByStoreId($productId, $storeId);
            if ($product) {
                $data = $product->getData();
            }
        }

        $resultJson->setData([
            'success' => true,
            'data' => $data
        ]);

        return $resultJson;
    }

    public function getProductByStoreId($productId, $storeId) 
    {
        $product = $this->productRepository->getById($productId, false, $storeId);
        return $product;
    }
}