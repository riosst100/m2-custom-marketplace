<?php

namespace TCGCollective\ProductAttributesLink\Block\PlatformDatabase;

class Tabs extends \Magento\Framework\View\Element\Template
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
        $currentUrl = $this->urlBuilder->getCurrentUrl();

        $buttons = [];

        $buttons[] = [
            'label' => __('Sets / Release Database'),
            'is_active' => false,
            'value' => 'index'
        ];
        
        if (str_contains($currentUrl, 'sportcard') || str_contains($currentUrl, 'cardgame') || str_contains($currentUrl, 'cardgames')) {
            $buttons[] = [
                'label' => __('Sealed Products Database'),
                'is_active' => false,
                'value' => 'sealedproducts'
            ];
        }

        // if (!str_contains($currentUrl, 'actiontoysmodelkits')) {
            $buttons[] = [
                'label' => __('Singles Database'),
                'is_active' => false,
                'value' => 'singles'
            ];
        // }

        

        $mainCategory = '';

        if ($currentUrl) {
            if (str_contains($currentUrl, 'actiontoys')) {
                $mainCategory = 'actiontoys';
            }

            if (str_contains($currentUrl, 'actiontoysmodelkits')) {
                $mainCategory = 'actiontoysmodelkits';
            }

            if (str_contains($currentUrl, 'anime')) {
                $mainCategory = 'anime';
            }

            if (str_contains($currentUrl, 'animemodelkits')) {
                $mainCategory = 'animemodelkits';
            }

            if (str_contains($currentUrl, 'fungoods')) {
                $mainCategory = 'fungoods';
            }

            if (str_contains($currentUrl, 'gundam')) {
                $mainCategory = 'gundam';
            }

            if (str_contains($currentUrl, 'cardgame')) {
                $mainCategory = 'cardgame';
            }

            if (str_contains($currentUrl, 'sportcards')) {
                $mainCategory = 'sportcards';
            }

            if (str_contains($currentUrl, 'livingcardgame')) {
                $mainCategory = 'livingcardgame';
            }

            if (str_contains($currentUrl, 'nonsportcards')) {
                $mainCategory = 'nonsportcards';
            }

            if (str_contains($currentUrl, 'miniaturegame')) {
                $mainCategory = 'miniaturegame';
            }

            if (str_contains($currentUrl, 'gamesworkshop')) {
                $mainCategory = 'gamesworkshop';
            }

            if (str_contains($currentUrl, 'bricks')) {
                $mainCategory = 'bricks';
            }

            if (str_contains($currentUrl, 'dolls')) {
                $mainCategory = 'dolls';
            }

            if (str_contains($currentUrl, 'diecastvehicles')) {
                $mainCategory = 'diecastvehicles';
            }

            if (str_contains($currentUrl, 'diecastaircrafts')) {
                $mainCategory = 'diecastaircrafts';
            }

            if (str_contains($currentUrl, 'diecastmilitary')) {
                $mainCategory = 'diecastmilitary';
            }

            if (str_contains($currentUrl, 'modelkitsvehicles')) {
                $mainCategory = 'modelkitsvehicles';
            }

            if (str_contains($currentUrl, 'modelkitsaircrafts')) {
                $mainCategory = 'modelkitsaircrafts';
            }

            if (str_contains($currentUrl, 'modelkitsmilitary')) {
                $mainCategory = 'modelkitsmilitary';
            }

            

            if (str_contains($currentUrl, 'trains')) {
                $mainCategory = 'trains';
            }
        }

        foreach($buttons as $key => $button) {
            $button['href'] = $this->getUrl(
                'database/' .  $mainCategory . '/' . $button['value'],
                []
            );

            if (str_contains($currentUrl, $button['value'])) {
                if ($button['value'] != "index" || $button['value'] == "index" && !str_contains($currentUrl, 'singles') && !str_contains($currentUrl, 'sealedproducts')) {
                    $button['is_active'] = true;
                    // $button['href'] = '#';
                }
            }

            

            $buttons[$key] = $button;
        }

        return $buttons;
    }
}
