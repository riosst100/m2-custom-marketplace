<?php

namespace TCGCollective\MarketPlace\Block\Catalog;

class TabsConfigurable extends \Magento\Framework\View\Element\Template
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
        array $data = []
    ) {
        $this->_group = $group;
        $this->_coreRegistry = $registry;
        $this->_sellerHelper = $sellerHelper;
        $this->urlBuilder = $context->getUrlBuilder();
        parent::__construct($context, $data);
    }

    public function getTabsButton()
    {
        $buttons = [];

        $buttons = [
            [
                'label' => __('Sealed Products'),
                'is_active' => false,
                'value' => 'sealedproducts'
            ],
            [
                'label' => __('Singles'),
                'is_active' => false,
                'value' => 'singles'
            ]
        ];

        $currentUrl = $this->urlBuilder->getCurrentUrl();

        foreach($buttons as $key => $button) {
            $button['href'] = $this->getUrl(
                'catalog/cardgameconfigurable/' . $button['value'],
                []
            );

            if (str_contains($currentUrl, $button['value'])) {
                $button['is_active'] = true;
                $button['href'] = '#';
            }

            

            $buttons[$key] = $button;
        }

        return $buttons;
    }
}
