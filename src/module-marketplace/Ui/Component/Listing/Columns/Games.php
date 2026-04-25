<?php

namespace TCGCollective\MarketPlace\Ui\Component\Listing\Columns;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class Games extends Column
{
    protected $helper;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        \Lof\MarketPlace\Helper\Data $helper,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->helper = $helper;
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
                if (isset($item['entity_id'])) {
                    $value = '-';

                    $categories = $item['categories'] ?? null;
                    if ($categories) {
                        $attributeCode = null;

                        if ($categories == "Collectible Card Games") {
                            $attributeCode = 'card_game';
                        }
                        if ($categories == "Miniature Games") {
                            $attributeCode = 'miniature_game';
                        }
                        if ($categories == "Living Card Games") {
                            $attributeCode = 'living_card_game';
                        }

                        $valueId = $item[$attributeCode] ?? null;
                        if ($valueId) {
                            $label = $this->helper->getAttributeOptionLabel($attributeCode, $valueId);
                            $value = $label ?? '-';
                        }
                    }

                    $item[$fieldName] = $value;
                }
            }
        }

        return $dataSource;
    }
}
