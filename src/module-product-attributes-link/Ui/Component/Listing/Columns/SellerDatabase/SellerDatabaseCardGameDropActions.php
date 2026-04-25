<?php

namespace TCGCollective\ProductAttributesLink\Ui\Component\Listing\Columns\SellerDatabase;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;

class SellerDatabaseCardGameDropActions extends Column
{
    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
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
            foreach ($dataSource['data']['items'] as &$item) {
                $singlesUrl = $this->urlBuilder->getUrl('database/cardgamesets/index',
                    [
                        'card_drop' => $item['entity_id'],
                        'card_game' => $item['card_game']
                    ]
                );

                $sealedProductsUrl = $this->urlBuilder->getUrl('database/cardgamesealedproducts/index',
                    [
                        'card_drop' => $item['entity_id'],
                        'card_game' => $item['card_game']
                    ]
                );

                $html = '';
                $html .= '<a href="'. $sealedProductsUrl .'">Sealed Products</a>';
                $html .= '<br />';
                $html .= '<a href="'. $singlesUrl .'">View Sets / Release</a>';

                $item[$this->getData('name')] = $html;
            }
        }

        return $dataSource;
    }
}
