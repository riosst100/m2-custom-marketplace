<?php

namespace TCGCollective\MarketPlace\Block\Catalog;

class ActionToysTabs extends \Magento\Framework\View\Element\Template
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
                'label' => __('Box Set/Case'),
                'is_active' => false,
                'value' => 'index'
            ],
            [
                'label' => __('Singles'),
                'is_active' => false,
                'value' => 'singles'
            ],
            [
                'label' => __('Lots/Sets'),
                'is_active' => false,
                'value' => 'lots'
            ]
        ];

        $currentUrl = $this->urlBuilder->getCurrentUrl();
        $mainCategoryUrlKey = 'actiontoys';

        foreach($buttons as $key => $button) {
            $button['href'] = $this->getUrl(
                'catalog/' . $mainCategoryUrlKey . '/' . $button['value'],
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
            $mainCategoryUrlKey = 'cardgame';
        }

        if (str_contains($currentUrl, '/sportcards/')) {
            $mainCategoryUrlKey = 'sportcards';
        }

        foreach($buttons as $key => $button) {
            $button['href'] = $this->getUrl(
                'catalog/' . $mainCategoryUrlKey . '/' . $button['value'],
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
