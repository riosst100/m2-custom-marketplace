<?php

namespace TCGCollective\ProductAttributesLink\Ui\Component\Listing\Columns;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;
use Magento\Framework\Escaper;

class SinglesNonSportCardActions extends Column
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
                $importSetUrl = $this->urlBuilder->getUrl('productattributeslink/import/sets',
                    [
                        'entity' => 'non_sport_card',
                        'nsc_categories' => $item['option_id']
                    ]
                );

                $manageSetUrl = $this->urlBuilder->getUrl('productattributeslink/nonsportcardssets/singles',
                    [
                        'nsc_categories' => $item['option_id']
                    ]
                );

                $html = '';
                $html .= '<a href="'. $manageSetUrl .'">Manage Sets</a>';
                // $html .= ' | ';
                // $html .= '<a href="'. $importSetUrl .'">Import Sets</a>';

                $item[$this->getData('name')] = $html;
            }
        }

        return $dataSource;
    }
}