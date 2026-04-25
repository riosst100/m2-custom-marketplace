<?php

namespace TCGCollective\ProductAttributesLink\Ui\Component\Listing\Columns;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;
use Magento\Framework\Escaper;

class NonSportCardSetsActions extends Column
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
                // $manageSinglesUrl = $this->urlBuilder->getUrl('productattributeslink/nonsportcardssetscard/index',
                //     [
                //         'set_id' => $item['entity_id']
                //     ]
                // );

                $editSetUrl = $this->urlBuilder->getUrl('productattributeslink/nonsportcardssets/edit',
                    [
                        'entity_id' => $item['entity_id'],
                        'nsc_categories' => $item['nsc_categories']
                    ]
                );

                $html = '';
                // $html .= '<a href="'. $manageSinglesUrl .'">Manage Singles</a>';
                // $html .= ' | ';
                $html .= '<a href="'. $editSetUrl .'">Edit Set</a>';

                $item[$this->getData('name')] = $html;
            }
        }

        return $dataSource;
    }
}
