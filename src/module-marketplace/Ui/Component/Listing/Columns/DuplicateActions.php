<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
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
class DuplicateActions extends Column
{
    protected $priceCurrency;


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

    // /**
    //  * Prepare Data Source
    //  *
    //  * @param array $dataSource
    //  * @return array
    //  */
    // public function prepareDataSource(array $dataSource)
    // {
    //     if (isset($dataSource['data']['items'])) {
    //         $storeId = $this->context->getFilterParam('store_id');

    //     //     foreach ($dataSource['data']['items'] as &$item) {
    //     //         $item[$this->getData('name')]['edit'] = [
    //     //             'href' => $this->urlBuilder->getUrl(
    //     //                 'catalog/product/edit',
    //     //                 ['id' => $item['entity_id'], 'store' => $storeId]
    //     //             ),
    //     //             'ariaLabel' => __('Edit ') . $item['name'],
    //     //             'label' => __('Edit'),
    //     //             'hidden' => false,
    //     //         ];
    //     //     }
    //         foreach ($dataSource['data']['items'] as &$item) {
    //             $listingUrl = $this->urlBuilder->getUrl(
    //                 'catalog/product/listing',
    //                 ['id' => $item['entity_id'], 'store' => $storeId]
    //             );

                

    //             $html = '';
    //             // $html .= '<a href="'. $editUrl .'">Duplicate</a>';
    //             $html = '<input type="button" onclick="location.href=\''. $editUrl .'\';" value="Duplicate" />';
                
    //             // $html .= ' | ';
    //             // $html .= '<a href="'. $listingUrl .'">List To Other Country</a>';

    //             $item[$this->getData('name')] = $html;
    //         }
    //     }

    //     return $dataSource;
    // }

    public function prepareDataSource(array $dataSource)
    {
        $this->priceCurrency = \Magento\Framework\App\ObjectManager::getInstance()
        ->get(\Magento\Framework\Pricing\PriceCurrencyInterface::class);
        
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $name = $this->getData('name');
                if (isset($item['entity_id'])) {
                    $storeId = $this->context->getFilterParam('store_id');

                    $editUrl = $this->urlBuilder->getUrl(
                        'catalog/product/duplicate',
                        ['id' => $item['entity_id']]
                    );
                    
                    // $html = '';
                    // $html = '<button class="button custom-button" onclick="location.href=\''. $editUrl .'\';"><span>Duplicate</span></button>';

                    // $item[$this->getData('name')] = $html;
                    $item[$name . '_html'] = "<button title='" . __('Duplicate Product') . "' class='listing-button button-primary'><span><i class='fa fa-files-o' aria-hidden='true' title='" . __('Duplicate Product') . "'></i></span></button>";
                    $item[$name . '_action_link'] = $editUrl;
                }
            }
        }

        return $dataSource;
    }
}
