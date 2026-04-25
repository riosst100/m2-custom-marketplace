<?php

namespace TCGCollective\MarketPlace\Ui\Component\Listing\Columns;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class CountryByStoreId extends Column
{
    protected $helper;
    protected $storeManager;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        \Lof\MarketPlace\Helper\Data $helper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->helper = $helper;
        $this->storeManager = $storeManager;
    }

    /**
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');

            foreach ($dataSource['data']['items'] as &$item) {
                // if (isset($item['entity_id'])) {
                    $value = '-';

                    $storeId = $item[$fieldName];
                    if ($storeId) {
                        $store = $this->storeManager->getStore($storeId);
                        if ($store) {
                            $value = $store->getWebsite()->getName();
                        }
                    }

                    // $categories = $item['categories'] ?? null;
                    // if ($categories) {
                    //     $attributeCode = null;

                    //     if ($categories == "Collectible Card Games") {
                    //         $attributeCode = 'card_game';
                    //     }
                    //     if ($categories == "Miniature Games") {
                    //         $attributeCode = 'miniature_game';
                    //     }
                    //     if ($categories == "Living Card Games") {
                    //         $attributeCode = 'living_card_game';
                    //     }

                    //     $valueId = $item[$attributeCode] ?? null;
                    //     if ($valueId) {
                    //         $label = $this->helper->getAttributeOptionLabel($attributeCode, $valueId);
                    //         $value = $label ?? '-';
                    //     }
                    // }

                    $item[$fieldName] = $value;
                // }
            }
        }

        return $dataSource;
    }
}
