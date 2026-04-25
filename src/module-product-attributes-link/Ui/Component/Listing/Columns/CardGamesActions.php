<?php

namespace TCGCollective\ProductAttributesLink\Ui\Component\Listing\Columns;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;
use Magento\Framework\Escaper;

class CardGamesActions extends Column
{
    protected $mappingHelper;
    protected $request;


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
        $this->request = $objectManager->get('Magento\Framework\App\RequestInterface');

        
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {

                $manageSetUrl = $this->urlBuilder->getUrl('productattributeslink/cardgamessets/index',
                    [
                        'card_game' => $item['option_id']
                    ]
                );

                $html = '';

                
                if ($this->request->getParam('sealedproducts')) {
                    // dd($this->request->getParam('sealedproducts'));

                    $manageReleaseUrl = $this->urlBuilder->getUrl('productattributeslink/cardgamessealedproducts/index',
                        [
                            'card_game' => $item['option_id']
                        ]
                    );

                    $html .= '<a href="'. $manageReleaseUrl .'">Manage Sealed Products</a>';
                    $html .= '<br/>';
                } else {

                    // $card_game = $this->mappingHelper->getOptionLabelById('card_game', $item['option_id']);
                    // if ($card_game && str_contains($card_game, "Secret Lair")) {
                    //     $manageDropUrl = $this->urlBuilder->getUrl('productattributeslink/cardgamesdrop/index',
                    //         [
                    //             'card_game' => $item['option_id']
                    //         ]
                    //     );
                    //     $html .= '<a href="'. $manageDropUrl .'">Manage Drop</a>';
                    //     $html .= '<br/>';
                    // }

                    $html .= '<a href="'. $manageSetUrl .'">Manage Sets</a>';
                    // $html .= ' | ';
                    // $html .= '<a href="'. $importSetUrl .'">Import Sets</a>';
                }

                $item[$this->getData('name')] = $html;
            }
        }

        return $dataSource;
    }
}