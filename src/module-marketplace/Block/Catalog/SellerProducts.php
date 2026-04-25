<?php

namespace TCGCollective\MarketPlace\Block\Catalog;

class SellerProducts extends \Magento\Framework\View\Element\Template
{
    protected $productListingConfig = null;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Lof\MarketPlace\Helper\Data
     */
    protected $_sellerHelper;

    /**
     * @var \Lof\MarketPlace\Model\Seller
     */
    protected $_group;

    protected $urlBuilder;

    protected $rabbitmqQueuesFactory;

    protected $catalogHelper;
    protected $mainCategoriesHelper;
    protected $productsHelper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Lof\MarketPlace\Helper\Data $sellerHelper
     * @param \Lof\MarketPlace\Model\Group $group
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Lof\MarketPlace\Helper\Data $sellerHelper,
        \Lof\MarketPlace\Model\Group $group,
        \TCGCollective\MarketPlace\Model\RabbitmqQueuesFactory $rabbitmqQueuesFactory,
        \TCGCollective\MarketPlace\Helper\Catalog $catalogHelper,
        \TCGCollective\MarketPlace\Helper\MainCategoriesHelper $mainCategoriesHelper,
        \TCGCollective\MarketPlace\Helper\Products $productsHelper,
        array $data = []
    ) {
        $this->_group = $group;
        $this->_coreRegistry = $registry;
        $this->_sellerHelper = $sellerHelper;
        $this->rabbitmqQueuesFactory = $rabbitmqQueuesFactory;
        $this->catalogHelper = $catalogHelper;
        $this->mainCategoriesHelper = $mainCategoriesHelper;
        $this->productsHelper = $productsHelper;
        $this->urlBuilder = $context->getUrlBuilder();
        parent::__construct($context, $data);
    }

    public function getProductListingDefaultColumns()
    {
        return $this->productsHelper->getProductListingDefaultColumns();
    }

    public function getRequestParam($param) 
    {
        return $this->getRequest()->getParam($param);
    }

    public function getProductAttribute($attrCode) 
    {
        return $this->productsHelper->getProductAttribute($attrCode);
    }

    public function getProductListingConfig() 
    {
        if (!$this->productListingConfig) {
            $this->productListingConfig = $this->productsHelper->getProductListingConfig();
        }

        return $this->productListingConfig;
    }

    public function getProductListingColumnAttributes() 
    {
        $productListingConfig = $this->getProductListingConfig();

        return $productListingConfig && $productListingConfig['default_columns'] ? 
            $productListingConfig['default_columns'] : [];
    }

    public function getProductListingTabs() 
    {
        $productListingConfig = $this->getProductListingConfig();
        $categoryConfigId = $productListingConfig['config']['entity_id'] ?? null;
        $tabAttrCode = $productListingConfig['config']['tab_attribute_code'] ?? null;
        if ($tabAttrCode) {
            $tabsDataArray = $this->productsHelper->getProductListingTabsArray($categoryConfigId, $tabAttrCode);
            return $tabsDataArray;
        }

        return null;
    }

    public function getProductQueuesCount() 
    {
        $sellerId = $this->_sellerHelper->getSellerId();

        $collection = $this->rabbitmqQueuesFactory->create()
        ->getCollection();
        $collection->addFieldToFilter('seller_id', $sellerId);
        $collection->addFieldToFilter('status', 'pending');
        
        
        return $collection->getSize();
    }

    public function getMyProductsTabs()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->request = $objectManager->create(\Magento\Framework\App\RequestInterface::class);
        $this->mainCategoriesConfigProvider = $objectManager->create(\TCGCollective\PlatformDatabase\Model\MainCategoriesConfigProvider::class);

        $categoryId = $this->request->getParam('category');

        // dd($categoryId);
        
        $myProductsUrlKey = null;
        $buttons = null;

        if ($categoryId) {
            $mainCategoriesData = $this->mainCategoriesConfigProvider->getByCategoryId($categoryId);
            // dd($mainCategoriesData);
            if ($mainCategoriesData) {
                $myProductsUrlKey = $mainCategoriesData['myProductsUrlKey'];
                $buttons = $mainCategoriesData['myProductsTabs'];
            }
        }

        if ($buttons) {
            foreach($buttons as $key => $button) {
                $button['href'] = $this->getUrl(
                    'catalog/' . $myProductsUrlKey . '/' . $button['value'],
                    []
                );

                if (str_contains($currentUrl, '/'.$button['value'].'/')) {
                    $button['is_active'] = true;
                    $button['href'] = '#';
                } else {
                    $button['is_active'] = false;
                }

                $buttons[$key] = $button;
            }
        }

        return $buttons;
    }

    public function getTabsButtonModal()
    {
        $buttons = [];

        $buttons = [
            [
                'label' => __('Sealed Products'),
                'is_active' => false,
                'value' => 'sealedproductscontentonly'
            ],
            [
                'label' => __('Singles'),
                'is_active' => false,
                'value' => 'singlescontentonly'
            ],
            [
                'label' => __('Lots/Sets'),
                'is_active' => false,
                'value' => 'lotscontentonly'
            ],
            [
                'label' => __('Supplies'),
                'is_active' => false,
                'value' => 'suppliescontentonly'
            ],
            [
                'label' => __('Pre Orders (Sealed Products)'),
                'is_active' => false,
                'value' => 'preorders_sealedproductscontentonly'
            ],
            [
                'label' => __('Pre Orders (Singles)'),
                'is_active' => false,
                'value' => 'preorders_singlescontentonly'
            ]
        ];

        $currentUrl = $this->urlBuilder->getCurrentUrl();

        if (str_contains($currentUrl, '/cardgame/')) {
            $myProductsUrlKey = 'cardgame';
        }

        if (str_contains($currentUrl, '/sportcards/')) {
            $myProductsUrlKey = 'sportcards';
        }

        foreach($buttons as $key => $button) {
            $button['href'] = $this->getUrl(
                'catalog/' . $myProductsUrlKey . '/' . $button['value'],
                []
            );

            if (str_contains($currentUrl, '/'.$button['value'])) {
                $button['is_active'] = true;
                $button['href'] = '#';
            }

            

            $buttons[$key] = $button;
        }

        return $buttons;
    }
}
