<?php

namespace TCGCollective\ProductAttributesLink\Block\Adminhtml\PlatformDatabase;

class Tabs extends \Magento\Backend\Block\Template
{
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
    protected $mainCategoriesConfigProvider;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Lof\MarketPlace\Helper\Data $sellerHelper
     * @param \Lof\MarketPlace\Model\Group $group
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Lof\MarketPlace\Helper\Data $sellerHelper,
        \Lof\MarketPlace\Model\Group $group,
        \TCGCollective\PlatformDatabase\Model\MainCategoriesConfigProvider $mainCategoriesConfigProvider,
        array $data = []
    ) {
        $this->_group = $group;
        $this->_coreRegistry = $registry;
        $this->_sellerHelper = $sellerHelper;
        $this->mainCategoriesConfigProvider = $mainCategoriesConfigProvider;
        $this->urlBuilder = $context->getUrlBuilder();
        parent::__construct($context, $data);
    }

    public function getTabLabel($tab) 
    {
        if ($tab == "sealedproducts") {
            return __('Sealed Products Database');
        }

        if ($tab == "singles") {
            return __('Singles Database');
        }

        if ($tab == "attributes") {
            return __('Attributes');
        }

        if ($tab == "genericattributes") {
            return __('Generic Attributes');
        }

        if ($tab == "linkedattributes") {
            return __('Linked Attributes');
        }

        return __('Sets / Release Database');
    }

    public function getTabsButton()
    {
        $buttons = [];
        $tabsArr = ['index','singles','attributes','genericattributes','linkedattributes'];

        $currentUrl = $this->urlBuilder->getCurrentUrl();

        $path = parse_url($currentUrl, PHP_URL_PATH);
        $segments = explode('/', trim($path, '/'));
        $mainCategoriesCode = $segments[2] ?? null;

        $mainCategoriesCode = str_replace("sets", "", $mainCategoriesCode);
        $mainCategoriesCode = str_replace("singles", "", $mainCategoriesCode);
        $mainCategoriesCode = str_replace("sealedproducts", "", $mainCategoriesCode);

        if ($mainCategoriesCode) {
            if ($mainCategoriesCode == "customattributes") {
                $mainCategoriesCode = $this->getRequest()->getParam('entity');
            }
            //     $mainCategoriesCode = $segments[count($segments)-2];
            //     // dd($mainCategoriesCode);
            //     $mainCategoriesConfigData = $this->mainCategoriesConfigProvider->getByMainAttrCode($mainCategoriesCode);
            //     $mainCategoriesCode = $mainCategoriesConfigData['entityCode'] ?? null;
            // } else {
                $mainCategoriesConfigData = $this->mainCategoriesConfigProvider->getByEntityCode($mainCategoriesCode);
            // }

            
            if ($mainCategoriesConfigData) {
                $tabsArr = $mainCategoriesConfigData['platformDbTabs'] 
                    ? explode(",", $mainCategoriesConfigData['platformDbTabs']) : $tabsArr;
            }
        }

        // dd($mainCategoriesCode);
        

        // dd($mainCategoriesCode);

        foreach($tabsArr as $tab) {
            $buttons[] = [
                'label' => $this->getTabLabel($tab),
                'is_active' => false,
                'value' => $tab
            ];
        }

        foreach($buttons as $key => $button) {
            $urlPath = 'productattributeslink/' . $mainCategoriesCode . '/' . $button['value'];
            $params = [];

            if ($button['value'] == "genericattributes") {
                $urlPath = 'productattributeslink/customattributes/index';
                if ($mainCategoriesConfigData) {
                    $mainAttrCode = $mainCategoriesConfigData['mainAttrCode'] ?? null;
                    $entityCode = $mainCategoriesConfigData['entityCode'] ?? null;
                    $params = [
                        'entity' => $entityCode,
                        $mainAttrCode => 0
                    ];
                }
            }
            $button['href'] = $this->getUrl(
                $urlPath,
                $params
            );

            if (str_contains($currentUrl, '/attributes') || str_contains($currentUrl, 'customattributes')) {
                if ($button['value'] == "genericattributes" || $button['value'] == "attributes") {
                    if ($button['value'] == "genericattributes") {
                        if (str_contains($currentUrl, 'genericattributes')) {
                            $button['is_active'] = true;
                        }

                        if (str_contains($currentUrl, 'customattributes')) {
                            $path = parse_url($currentUrl, PHP_URL_PATH);
                            // Pecah URL menjadi array
                            $segments = explode('/', trim($path, '/'));
                            $mainAttrVal = $segments[count($segments)-1];
                            if ($mainAttrVal == 0) {
                                $button['is_active'] = true;
                            }
                        }
                    }
                    
                    if ($button['value'] == "attributes" ) {
                        if (str_contains($currentUrl, '/attributes') && !str_contains($currentUrl, 'customattributes') && !str_contains($currentUrl, 'genericattributes')) {
                            $button['is_active'] = true;
                        }

                        if (str_contains($currentUrl, 'customattributes')) {
                            $path = parse_url($currentUrl, PHP_URL_PATH);
                            // Pecah URL menjadi array
                            $segments = explode('/', trim($path, '/'));
                            $mainAttrVal = $segments[count($segments)-1];
                            // dd($mainAttrVal);
                            if ($mainAttrVal != 0) {
                                $button['is_active'] = true;
                            }
                        }
                    }
                }
            } else {
                if ($button['value'] == "singles" && str_contains($currentUrl, 'singles') 
                        || $button['value'] == "singles" && str_contains($currentUrl, 'setscard')) {
                        $button['is_active'] = true;
                    }

                    if ($button['value'] == "sealedproducts" && str_contains($currentUrl, 'sealedproducts')) {
                        $button['is_active'] = true;
                    }

                    if ($button['value'] == "index" && !str_contains($currentUrl, 'singles') && !str_contains($currentUrl, 'cardgamescard') && !str_contains($currentUrl, 'setscard') && !str_contains($currentUrl, 'sealedproducts')) {
                        $button['is_active'] = true;
                    }

                if ($button['value'] == "singles" && str_contains($currentUrl, 'singles') 
                    || $button['value'] == "singles" && str_contains($currentUrl, 'setscard') 
                    || $button['value'] == "singles" && str_contains($currentUrl, 'cardgamescard')
                ) {
                    $button['is_active'] = true;
                }
            }

            $buttons[$key] = $button;
        }

        return $buttons;
    }
}
