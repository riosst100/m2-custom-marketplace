<?php

namespace TCGCollective\ProductAttributesLink\Ui\Component\Listing\Columns;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;
use Magento\Framework\Escaper;

class CustomAttributesActions extends Column
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
                $params = [
                    'attribute_code' => $item['attribute_code']
                ];

                if (isset($item['toys_brand']) && $item['toys_brand']) {
                    $params['toys_brand'] = $item['toys_brand'];
                }

                if (isset($item['card_game']) && $item['card_game']) {
                    $params['card_game'] = $item['card_game'];
                }

                $editSetUrl = $this->urlBuilder->getUrl('productattributeslink/customattributes/values',
                    $params
                );

                $html = '';
                $html .= '<a href="'. $editSetUrl .'">Manage Options</a>';

                $item[$this->getData('name')] = $html;
            }
        }

        return $dataSource;
    }
}
