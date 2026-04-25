<?php

namespace TCGCollective\ProductAttributesLink\Ui\Component\Listing\Columns;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;
use Magento\Framework\Escaper;

class SellerCardGamesActions extends Column
{
    protected $mappingHelper;


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
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->mappingHelper = $objectManager->create(\TCGCollective\ProductAttributesLink\Helper\Data::class);

        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $manageSetUrl = $this->urlBuilder->getUrl('database/cardgamesets/index/card_game/'.$item['option_id']);

                $html = '';
                
                // $card_game = $this->mappingHelper->getOptionLabelById('card_game', $item['option_id']);
                // if ($card_game && str_contains($card_game, "Secret Lair")) {
                //     $manageDropUrl = $this->urlBuilder->getUrl('database/cardgamedrop/index',
                //         [
                //             'card_game' => $item['option_id']
                //         ]
                //     );
                //     $html .= '<a href="'. $manageDropUrl .'">Drop</a>';
                //     $html .= '<br/>';
                // } else {
                    $html .= '<a href="'. $manageSetUrl .'">View Sets</a>';
                // }

                $item[$this->getData('name')] = $html;
            }
        }

        return $dataSource;
    }
}