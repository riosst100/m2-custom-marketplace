<?php

namespace TCGCollective\MarketPlace\Ui\Component\Listing\Columns;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\ResourceModel\Website\CollectionFactory as WebsiteCollectionFactory;
use Magento\Catalog\Model\ProductRepository;

class PublishActions extends Column
{
    protected $websiteCollectionCache;
    protected $helper;
    protected $sellerDataCache;


    private $urlBuilder;
    private $formKey;
    protected $priceCurrency;
    protected $storeManager;
    protected $websiteCollectionFactory;
    protected $productRepository;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        FormKey $formKey,
        PriceCurrencyInterface $priceCurrency,
        StoreManagerInterface $storeManager,
        WebsiteCollectionFactory $websiteCollectionFactory,
        ProductRepository $productRepository,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->formKey = $formKey;
        $this->priceCurrency = $priceCurrency;
        $this->storeManager = $storeManager;
        $this->websiteCollectionFactory = $websiteCollectionFactory;
        $this->productRepository = $productRepository;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->websiteCollectionCache = $objectManager->create(\TCGCollective\MarketPlace\Model\WebsiteCollectionCache::class);
        $this->helper = $objectManager->create(\Lof\MarketPlace\Helper\Data::class);
        $this->sellerDataCache = $objectManager->create(\TCGCollective\MarketPlace\Model\SellerDataCache::class);

        // $defaultWebsiteId = $this->storeManager->getStore()->getWebsite()->getId();

        // $seller = $this->sellerDataCache->getSellerData();

        // dd($seller);
        // $listingWebsiteIds = isset($seller['listing_website_ids']) && $seller['listing_website_ids'] ? 
        // explode(",", $seller['listing_website_ids']) : [];

        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $name = $this->getData('name');
                if (isset($item['entity_id'])) {
                    // $item[$name . '_html'] = "<button class='button custom-button'><span>".__("Edit Listing Country")."</span></button>";
                    $editUrl = $this->urlBuilder->getUrl(
                        'catalog/product/edit',
                        [
                            'id' => $item['entity_id'],
                            'store' => $this->storeManager->getStore()->getId(),
                            'action' => 'publish'
                        ]
                    );

                    // $listingCountryArr = [];

                    // $websiteCollection = $this->websiteCollectionCache->getWebsiteCollection();
                    // if ($websiteCollection) {
                    //     $websites = $websiteCollection['items'] ?? [];
                    //     if ($websites) {
                    //         foreach ($websites as $website) {
                    //             $websiteId = $website['website_id'] ?? null;
                    //             $websiteName = $website['name'] ?? null;
                    //             if (!$websiteId) {
                    //                 continue;
                    //             }

                    //             $storeId = $this->helper->getWebsiteDefaultStoreId($website);

                    //             $website['publish_link'] = $this->urlBuilder->getUrl('catalog/product/edit', [
                    //                 'id' => $item['entity_id'],
                    //                 'store' => $storeId,
                    //                 'country' => $websiteName,
                    //                 'website_id' => $websiteId,
                    //                 'action' => 'publish'
                    //             ]);

                    //             if ($websiteId == $defaultWebsiteId) {
                    //                 $website['is_default'] = true;
                    //                 $website['name'] = $websiteName . ' (Default Country)';
                    //                 $website['is_enabled'] = true;
                    //             } else {
                    //                 $website['is_default'] = false;
                    //                 if (in_array($websiteId, $listingWebsiteIds)) {
                    //                     $website['is_enabled'] = true;
                    //                 } else {
                    //                     $website['is_enabled'] = false;
                    //                 }
                    //             }

                    //             $listingCountryArr[] = $website;
                    //         }
                    //     }
                    // }

                    // usort($listingCountryArr, fn($a, $b) => $b['is_default'] <=> $a['is_default']);
                    // usort($listingCountryArr, fn($a, $b) => $b['is_enabled'] <=> $a['is_enabled']);
                    // dd($item);
                    // $html = '';
                    // $html = '<button class="button custom-button" onclick="location.href=\''. $editUrl .'\';"><span>Duplicate</span></button>';

                    // $item[$this->getData('name')] = $_action_linkhtml;
                    // $item[$name . '_html'] = "<button title='" . __('Edit Product') . "' class='listing-button button-primary'><span><i class='fa fa-paper-plane' aria-hidden='true' title='" . __('Edit Product') . "'></i></span></button>";
                    $item[$name . '_html'] = "<button class='listing-button button-primary' style='width: 145px;'><span>".__("Publish / Edit Listing")."</span></button>";
                    $item[$name . '_title'] = "Select Listing Country";
                    // $item[$name . '_publishData'] = json_encode($listingCountryArr);
                    // $item[$name . '_currentWebsiteIds'] = json_encode($item['website_ids']);
                    $item[$name . ''] = $editUrl;
                }
            }
        }

        return $dataSource;
    }
}