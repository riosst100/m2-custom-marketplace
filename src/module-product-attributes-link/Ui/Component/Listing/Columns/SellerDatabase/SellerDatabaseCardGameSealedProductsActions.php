<?php

namespace TCGCollective\ProductAttributesLink\Ui\Component\Listing\Columns\SellerDatabase;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;

class SellerDatabaseCardGameSealedProductsActions extends Column
{
    protected $request;


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
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->request = $objectManager->get('\Magento\Framework\App\RequestInterface');

        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $params = [
                    'set_id' => $item['entity_id']
                ];

                $dropId = $this->request->getParam('card_drop');
                if ($dropId) {
                    $params = array_merge($params, [
                        'card_drop' => $dropId
                    ]);
                }

                $singlesUrl = $this->urlBuilder->getUrl('database/cardgamesingles/index',
                    $params
                );

                $html = '';
                $html .= '<a href="'. $singlesUrl .'">Singles</a>';

                $item[$this->getData('name')] = $html;
            }
        }

        return $dataSource;
    }
}
