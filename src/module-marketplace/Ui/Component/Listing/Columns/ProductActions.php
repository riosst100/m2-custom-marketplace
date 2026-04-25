<?php

namespace TCGCollective\MarketPlace\Ui\Component\Listing\Columns;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;

/**
 * Class DuplicateActions for Listing Columns
 *
 * @api
 * @since 100.0.2
 */
class ProductActions extends Column
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

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $name = $this->getData('name');
                if (isset($item['entity_id'])) {
                    $storeId = $this->context->getFilterParam('store_id');

                    $editUrl = $this->urlBuilder->getUrl(
                        'catalog/product/edit',
                        ['id' => $item['entity_id'], 'store' => $storeId]
                    );
                    
                    $item[$name . '_html'] = "<button title='" . __('Edit Product') . "' class='listing-button button-primary'><span><i class='fa fa-pencil' aria-hidden='true' title='" . __('Edit Product') . "'></i></span></button>";
                    $item[$name . '_action_link'] = $editUrl;
                }
            }
        }

        return $dataSource;
    }
}
