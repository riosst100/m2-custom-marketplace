<?php

namespace TCGCollective\ProductAttributesLink\Ui\Component\Listing\Columns;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;
use Magento\Framework\Escaper;

class CardGamesReleaseActions extends Column
{
    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var Escaper
     */
    protected $escaper;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param Escaper $escaper
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        Escaper $escaper,
        array $components = [],
        array $data = []
    ) {
        $this->escaper = $escaper;
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
                $manageSealedProductsUrl = $this->urlBuilder->getUrl('productattributeslink/cardgamessealedproducts/index',
                    [
                        'card_game' => $item['card_game'],
                        'card_release' => $item['entity_id']
                    ]
                );

                $manageSetsUrl = $this->urlBuilder->getUrl('productattributeslink/cardgamessets/index',
                    [
                        'card_game' => $item['card_game'],
                        'card_release' => $item['entity_id']
                    ]
                );

                $editReleaseUrl = $this->urlBuilder->getUrl('productattributeslink/cardgamesrelease/edit',
                    [
                        'entity_id' => $item['entity_id']
                    ]
                );

                $html = '';
                $html .= '<a class="button" href="'. $manageSealedProductsUrl .'">Manage Sealed Products</a>';
                $html .= '<br />';
                $html .= '<a href="'. $manageSetsUrl .'">Manage Sets</a>';
                $html .= '<br/>';
                $html .= '<a href="'. $editReleaseUrl .'">Edit Release</a>';

                $item[$this->getData('name')] = $html;
            }
        }

        return $dataSource;
    }
}
