<?php

namespace TCGCollective\MarketPlace\Ui\Component\Listing\Columns;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Store\Model\ResourceModel\Website\CollectionFactory as WebsiteCollectionFactory;
use Magento\Catalog\Model\ProductRepository;

/**
 * @api
 * @since 100.0.2
 */
class Price extends \Magento\Ui\Component\Listing\Columns\Column
{
    protected $productRepository;


    /**
     * Column name
     */
    public const NAME = 'column.price';

    /**
     * @var \Magento\Framework\Locale\CurrencyInterface
     */
    protected $localeCurrency;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    protected $websiteCollectionFactory;
    protected $helper;
    protected $websiteCollectionCache;
    protected $configurableType;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        \Magento\Framework\Locale\CurrencyInterface $localeCurrency,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        WebsiteCollectionFactory $websiteCollectionFactory,
        ProductRepository $productRepository,
        \Lof\MarketPlace\Helper\Data $helper,
        \TCGCollective\MarketPlace\Model\WebsiteCollectionCache $websiteCollectionCache,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurableType,
        array $components = [],
        array $data = [],
        ?PriceCurrencyInterface $priceCurrency = null
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->localeCurrency = $localeCurrency;
        $this->storeManager = $storeManager;
        $this->websiteCollectionFactory = $websiteCollectionFactory;
        $this->productRepository = $productRepository;
        $this->priceCurrency = $priceCurrency ?? ObjectManager::getInstance()->get(PriceCurrencyInterface::class);
        $this->helper = $helper;
        $this->websiteCollectionCache = $websiteCollectionCache;
        $this->configurableType = $configurableType;
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as & $item) {
                $resultPrice = '-';

                $typeId = $item['type_id'];
                $countListingCountry = count($item['website_ids']);

                $isPublished = $item['is_published'] ?? 2;
                $publishStatus = $isPublished == 1 ? true : false;
                if ($publishStatus) {
                    $priceHtml = '';

                    $websiteCollection = $this->websiteCollectionCache->getWebsiteCollection();
                    if ($websiteCollection) {
                        $websites = $websiteCollection['items'] ?? [];
                        if ($websites) {
                            foreach ($websites as $index => $website) {
                                $websiteName = $website['name'] ?? '';
                                if ($this->helper->checkPublishStatusByWebsiteData($website, $item['entity_id'])) {
                                    $price = $this->getProductPriceByWebsiteId($item['entity_id'], $website, $typeId);

                                    if ($price) {
                                        if ($countListingCountry > 1) {
                                            $priceHtml .= $websiteName.': <br/>';
                                        }
                                        $priceHtml .= '<b>'.$price.'</b>';
                                        if ($countListingCountry > 1 && $index+1 != count($websites)) {
                                            $priceHtml .= '<br/>';
                                            $priceHtml .= '<br/>';
                                        }
                                    }
                                    
                                }
                            }
                        }
                    }

                    if ($priceHtml != '') {
                        $resultPrice = $priceHtml;
                    }
                }

                $item[$fieldName] = $resultPrice ?? '-';
            }
        }

        return $dataSource;
    }

    public function getProductPriceByWebsiteId($productId, $website, $typeId) 
    {
        $priceFormatted = 0;

        $defaultStoreId = $this->helper->getWebsiteDefaultStoreId($website);
        $store = $this->storeManager->getStore(
            $this->context->getFilterParam('store_id', $defaultStoreId)
        );

        $product = $this->productRepository->getById($productId, false, $defaultStoreId);
        if ($typeId == "configurable") {
            $usedProducts = $this->configurableType->getUsedProducts($product);
            $prices = [];

            foreach ($usedProducts as $child) {
                $prices[] = $child->getFinalPrice();
            }
            
            if ($prices) {
                $minPriceFormatted = $this->priceCurrency->format(
                    sprintf("%F", min($prices)),
                    false,
                    PriceCurrencyInterface::DEFAULT_PRECISION,
                    $store
                );

                $maxPriceFormatted = $this->priceCurrency->format(
                    sprintf("%F", max($prices)),
                    false,
                    PriceCurrencyInterface::DEFAULT_PRECISION,
                    $store
                );

                $priceFormatted = $minPriceFormatted . ' - ' . $maxPriceFormatted;
            }
        } else {
            $price = $product->getFinalPrice();
            $priceFormatted = $this->priceCurrency->format(
                sprintf("%F", $price),
                false,
                PriceCurrencyInterface::DEFAULT_PRECISION,
                $store
            );
        }

        return $priceFormatted;
    }
}
