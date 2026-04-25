<?php

namespace TCGCollective\MarketPlace\Block\Page;

class RequireJs extends \Magento\Backend\Block\Page\RequireJs
{
    protected $priceCurrency;
    protected $_storeRepository;


    /**
     * @var \Magento\Framework\Data\Form\FormKey
     */
    protected $formKey;

    /**
     * @var \TCGCollective\ProductAttributesLink\Helper\Data
     */
    protected $mappingHelper;
    protected $mainCategoriesHelper;
    protected $platformDbHelper;
    protected $urlBuilder;
    protected $formCollectionFactory;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Data\Form\FormKey $formKey
     * @param \TCGCollective\ProductAttributesLink\Helper\Data $mappingHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Data\Form\FormKey $formKey,
        \TCGCollective\ProductAttributesLink\Helper\Data $mappingHelper,
        \TCGCollective\MarketPlace\Helper\MainCategoriesHelper $mainCategoriesHelper,
        \TCGCollective\PlatformDatabase\Helper\Data $platformDbHelper,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Lof\Formbuilder\Model\ResourceModel\Form\CollectionFactory $formCollectionFactory,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $formKey,
            $data
        );
        $this->formKey = $formKey;
        $this->mappingHelper = $mappingHelper;
        $this->mainCategoriesHelper = $mainCategoriesHelper;
        $this->platformDbHelper = $platformDbHelper;
        $this->_urlBuilder = $urlBuilder;
        $this->formCollectionFactory = $formCollectionFactory;
    }

    public function getGameOrBrandsReqestFormAttributeCodes() 
    {
        $attrCodes = [];

        $collection = $this->formCollectionFactory->create()
        ->addFieldToFilter('status', 1);

        $collection->getSelect()->group('identifier');
        if ($collection->getSize()) {
            foreach($collection as $model) {
                $attrCodes[] = $model->getIdentifier();
            }
        }

        return json_encode($attrCodes,true);
    }

    public function getPlatformDbSelectionScopes() 
    {
        return json_encode([
            'actiontoy_sets',
            'animefigures_sets',
            'cardgame_sets',
            'cardgamemtg_sets',
            'legosets_sets',
            'livingcardgame_sets',
            'miniaturegame_sets',
            'gamesworkshop_sets',
            'sportcard_sets',
            'nonsportcard_sets'
        ],true);
    }

    public function isProductCreationPage() 
    {
        $currentUrl = $this->_urlBuilder->getCurrentUrl();
        if (str_contains($currentUrl, 'catalog/product/new') || str_contains($currentUrl, 'catalog/product/edit')) {
            return true;
        }
        
        return false;
    }

    public function isProductListingPage() 
    {
        $currentUrl = $this->_urlBuilder->getCurrentUrl();
        if (str_contains($currentUrl, 'catalog/')) {
            return true;
        }
        
        return false;
    }

    public function getCustomTableMetadata() 
    {
        return json_encode($this->platformDbHelper->getAllCustomTableData(),true);
    }

    public function getCustomTableTypeAttrCodes() 
    {
        return json_encode($this->platformDbHelper->getCustomTableTypeAttrCodes(),true);
    }

    public function getMainAttrCodes() 
    {
        return json_encode($this->mainCategoriesHelper->getMainAttrCodes(),true);
    }

    public function getMainAttrCodesWithUrlKeyMapping() 
    {
        return json_encode($this->mainCategoriesHelper->getMainAttrCodesWithUrlKeyMapping(),true);
    }

    public function getAdminBaseUrl() 
    {
        return $this->getUrl('');
        $urlInterface = \Magento\Framework\App\ObjectManager::getInstance()
    ->get(\Magento\Backend\Helper\Data::class);
$currentUrl = $urlInterface->getHomePageUrl();

// $this->helper('Magento\Backend\Helper\Data')->getHomePageUrl(); //

return $currentUrl;
    }

    public function getCurrentWebsiteId() 
    {
        $storeManager = \Magento\Framework\App\ObjectManager::getInstance()
    ->get(\Magento\Store\Model\StoreManagerInterface::class);
    return $storeManager->getStore()->getWebsiteId();
    }

    public function getAvailableWebsiteIds() 
    {
        return null;
    //     $storeManager = \Magento\Framework\App\ObjectManager::getInstance()
    // ->get(\Magento\Store\Model\StoreManagerInterface::class);
    // $this->priceCurrency = \Magento\Framework\App\ObjectManager::getInstance()
    // ->get(\Magento\Framework\Pricing\PriceCurrencyInterface::class);
    
    //     // $allowedCurrencies = $storeManager->getStore()->getAllowedCurrencies();
    //     // dd($allowedCurrencies);
    //     $this->_storeRepository = \Magento\Framework\App\ObjectManager::getInstance()
    //     ->get(\Magento\Store\Model\StoreRepository::class);
    //     $stores = $this->_storeRepository->getList();
    //     $websiteIds = array();
    //     foreach ($stores as $store) {
    //         $website = $store->getWebsite();
    //         $websiteId = $website->getId();
    //         if (array_search($websiteId, array_column($websiteIds, 'value')) !== FALSE) {
    //             continue;
    //         }

    //         // dd($website->getDefaultStore()->getDefaultCurrencyCode());

            

    //         if ($websiteId != 0) {
    //             $currencySymbol = $this->priceCurrency->getCurrencySymbol($website->getDefaultStore()->getId());

    //         // dd($currencySymbol);

    //             $websiteIds[] = [
    //                 'label' => $website->getName(),
    //                 'currency' => $currencySymbol,
    //                 'value' => $websiteId
    //             ];
    //         }
    //     }

    //     return json_encode($websiteIds);
    }

    public function getMappingFieldsPriority() 
    {
        // return null;
        return $this->mappingHelper->getMappingFieldsPriority();
    }

    /**
     * Retrieve Session Form Key
     *
     * @return string
     */
    public function getAttributeSetMappingFields()
    {
        // return null;
        return $this->mappingHelper->getAttributeSetMappingFields();
    }

    /**
     * Retrieve Session Form Key
     *
     * @return string
     */
    public function getCardGamesMappingFields()
    {
        // return null;
        return $this->mappingHelper->getCardGamesMappingFields();
    }

    /**
     * Retrieve Session Form Key
     *
     * @return string
     */
    public function getLegoSetsFields()
    {
        // return null;
        return $this->mappingHelper->getLegoSetsFields();
    }

     /**
     * Retrieve Session Form Key
     *
     * @return string
     */
    public function getFormKey()
    {
        return $this->formKey->getFormKey();
    }
}
