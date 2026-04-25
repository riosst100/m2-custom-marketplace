<?php


namespace TCGCollective\MarketPlace\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Model\Product\Attribute\Backend\Sku;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Framework\UrlInterface;
use Magento\Ui\Component\Container;
use Magento\Ui\Component\DynamicRows;
use Magento\Ui\Component\Form;
use Magento\Framework\Phrase;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\CustomOptions;
use Magento\Ui\Component\Form\Element\DataType\Number;
use Magento\Ui\Component\Form\Element\DataType\Text;
use Magento\Ui\Component\Form\Element\Input;
use Magento\Ui\Component\Form\Field;
use Magento\Ui\Component\Form\Fieldset;
use Magento\Ui\Component\Modal;

/**
 * Data provider for Configurable panel
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SetsSelection extends AbstractModifier
{
    const GROUP_SETS = 'sets';
    public const DATA_SCOPE_CARD_GAME = 'card_game';
    public const DATA_SCOPE_LEGO = 'lego';


    const ASSOCIATED_PRODUCT_MODAL = 'sets_selection_modal';
    const ASSOCIATED_PRODUCT_LISTING = 'sets_listing';
    const SETS_MATRIX = 'sets-matrix';

    const CUSTOM_MODAL_LINK = 'custom_modal_link';
    const CUSTOM_MODAL_INDEX = 'custom_modal';
    const CUSTOM_MODAL_CONTENT = 'content';
    const CUSTOM_MODAL_FIELDSET = 'fieldset';
    const CONTAINER_HEADER_NAME = 'header';
    const CUSTOM_GROUP_NAME = 'my_custom_group';
    const CUSTOM_GROUP_BTN_CONTAINER_NAME = 'my_buttons';

    /**
     * @var string
     */
    private static $groupContent = 'content';

    /**
     * @var int
     */
    private static $sortOrder = 30;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var string
     */
    private $formName;

    /**
     * @var string
     */
    private $dataScopeName;

    /**
     * @var string
     */
    private $dataSourceName;

    /**
     * @var string
     */
    private $associatedListingPrefix;

    /**
     * @var LocatorInterface
     */
    private $locator;

    /**
     * @var string
     * @since 101.0.0
     */
    protected $scopePrefix;

    /**
     * @var string
     * @since 101.0.0
     */
    protected $scopeName;

    /**
     * @param LocatorInterface $locator
     * @param UrlInterface $urlBuilder
     * @param string $formName
     * @param string $dataScopeName
     * @param string $dataSourceName
     * @param string $associatedListingPrefix
     */
    public function __construct(
        LocatorInterface $locator,
        UrlInterface $urlBuilder,
        $formName,
        $dataScopeName,
        $dataSourceName,
        $associatedListingPrefix = '',
        $scopePrefix = '',
        $scopeName = ''
    ) {
        $this->locator = $locator;
        $this->urlBuilder = $urlBuilder;
        $this->formName = $formName;
        $this->dataScopeName = $dataScopeName;
        $this->dataSourceName = $dataSourceName;
        $this->associatedListingPrefix = $associatedListingPrefix;
        $this->scopePrefix = $scopePrefix;
        $this->scopeName = $scopeName;
    }

    /**
     * @inheritdoc
     */
    public function modifyData(array $data)
    {
        return $data;
    }

    /**
     * Retrieve grid
     *
     * @param string $scope
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @since 101.0.0
     */
    protected function getGrid($scope)
    {
        $dataProvider = $scope . '_product_listing';

        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'additionalClasses' => 'admin__field-wide',
                        'componentType' => DynamicRows::NAME,
                        'label' => null,
                        'columnsHeader' => false,
                        'columnsHeaderAfterRender' => true,
                        'renderDefaultRecord' => false,
                        'template' => 'ui/dynamic-rows/templates/grid',
                        'component' => 'Magento_Catalog/js/components/reset-dynamic-rows-grid-row-position-on-delete',
                        'addButton' => false,
                        'recordTemplate' => 'record',
                        'dataScope' => 'data.links',
                        'deleteButtonLabel' => __('Remove'),
                        'dataProvider' => $dataProvider,
                        'map' => [
                            'id' => 'entity_id',
                            'name' => 'name',
                            'status' => 'status_text',
                            'attribute_set' => 'attribute_set_text',
                            'sku' => 'sku',
                            'price' => 'price',
                            'thumbnail' => 'thumbnail_src',
                        ],
                        'links' => [
                            'insertData' => '${ $.provider }:${ $.dataProvider }',
                            '__disableTmpl' => ['insertData' => false],
                        ],
                        'sortOrder' => 2,
                    ],
                ],
            ],
            'children' => [
                'record' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'componentType' => 'container',
                                'isTemplate' => true,
                                'is_collection' => true,
                                'component' => 'Magento_Ui/js/dynamic-rows/record',
                                'dataScope' => '',
                            ],
                        ],
                    ],
                    'children' => $this->fillMeta(),
                ],
            ],
        ];
    }

    /**
     * Retrieve meta column
     *
     * @return array
     * @since 101.0.0
     */
    protected function fillMeta()
    {
        return [
            'id' => $this->getTextColumn('id', false, __('ID'), 0),
            'thumbnail' => [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'componentType' => Field::NAME,
                            'formElement' => Input::NAME,
                            'elementTmpl' => 'ui/dynamic-rows/cells/thumbnail',
                            'dataType' => Text::NAME,
                            'dataScope' => 'thumbnail',
                            'fit' => true,
                            'label' => __('Thumbnail'),
                            'sortOrder' => 10,
                        ],
                    ],
                ],
            ],
            'name' => $this->getTextColumn('name', false, __('Name'), 20),
            'status' => $this->getTextColumn('status', true, __('Status'), 30),
            'attribute_set' => $this->getTextColumn('attribute_set', false, __('Attribute Set'), 40),
            'sku' => $this->getTextColumn('sku', true, __('SKU'), 50),
            'price' => $this->getTextColumn('price', true, __('Price'), 60),
            'actionDelete' => [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'additionalClasses' => 'data-grid-actions-cell',
                            'componentType' => 'actionDelete',
                            'dataType' => Text::NAME,
                            'label' => __('Actions'),
                            'sortOrder' => 70,
                            'fit' => true,
                        ],
                    ],
                ],
            ],
            'position' => [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'dataType' => Number::NAME,
                            'formElement' => Input::NAME,
                            'componentType' => Field::NAME,
                            'dataScope' => 'position',
                            'sortOrder' => 80,
                            'visible' => false,
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Retrieve text column structure
     *
     * @param string $dataScope
     * @param bool $fit
     * @param Phrase $label
     * @param int $sortOrder
     * @return array
     * @since 101.0.0
     */
    protected function getTextColumn($dataScope, $fit, Phrase $label, $sortOrder)
    {
        $column = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => Field::NAME,
                        'formElement' => Input::NAME,
                        'elementTmpl' => 'ui/dynamic-rows/cells/text',
                        'component' => 'Magento_Ui/js/form/element/text',
                        'dataType' => Text::NAME,
                        'dataScope' => $dataScope,
                        'fit' => $fit,
                        'label' => $label,
                        'sortOrder' => $sortOrder,
                    ],
                ],
            ],
        ];

        return $column;
    }

    /**
     * Retrieve button set
     *
     * @param Phrase $content
     * @param Phrase $buttonTitle
     * @param string $scope
     * @return array
     * @since 101.0.0
     */
    protected function getButtonSet(Phrase $content, Phrase $buttonTitle, $scope)
    {
        $modalTarget = $this->scopeName . '.' . static::GROUP_SETS . '.' . $scope . '.modal';

        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'formElement' => 'container',
                        'componentType' => 'container',
                        'label' => false,
                        'content' => $content,
                        'template' => 'ui/form/components/complex',
                    ],
                ],
            ],
            'children' => [
                'button_' . $scope => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'formElement' => 'container',
                                'componentType' => 'container',
                                'component' => 'Magento_Ui/js/form/components/button',
                                'actions' => [
                                    [
                                        'targetName' => $modalTarget,
                                        'actionName' => 'toggleModal',
                                    ],
                                    [
                                        'targetName' => $modalTarget . '.' . $scope . '_product_listing',
                                        'actionName' => 'render',
                                    ]
                                ],
                                'title' => $buttonTitle,
                                'provider' => null,
                            ],
                        ],
                    ],

                ],
            ],
        ];
    }

    /**
     * Prepares config for modal slide-out panel
     *
     * @param Phrase $title
     * @param string $scope
     * @return array
     * @since 101.0.0
     */
    protected function getGenericModal(Phrase $title, $scope)
    {
        $listingTarget = $scope . '_product_listing';

        $modal = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => Modal::NAME,
                        'dataScope' => '',
                        'options' => [
                            'title' => $title,
                            'buttons' => [
                                [
                                    'text' => __('Cancel'),
                                    'actions' => [
                                        'closeModal'
                                    ]
                                ],
                                [
                                    'text' => __('Add Selected Products'),
                                    'class' => 'action-primary',
                                    'actions' => [
                                        [
                                            'targetName' => 'index = ' . $listingTarget,
                                            'actionName' => 'save'
                                        ],
                                        'closeModal'
                                    ]
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'children' => [
                $listingTarget => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'autoRender' => false,
                                'componentType' => 'insertListing',
                                'dataScope' => $listingTarget,
                                'externalProvider' => $listingTarget . '.' . $listingTarget . '_data_source',
                                'selectionsProvider' => $listingTarget . '.' . $listingTarget . '.product_columns.ids',
                                'ns' => $listingTarget,
                                'render_url' => $this->urlBuilder->getUrl('mui/index/render'),
                                'realTimeLink' => true,
                                'dataLinks' => [
                                    'imports' => false,
                                    'exports' => true
                                ],
                                'behaviourType' => 'simple',
                                'externalFilterMode' => true,
                                'imports' => [
                                    'productId' => '${ $.provider }:data.product.current_product_id',
                                    'storeId' => '${ $.provider }:data.product.current_store_id',
                                    '__disableTmpl' => ['productId' => false, 'storeId' => false],
                                ],
                                'exports' => [
                                    'productId' => '${ $.externalProvider }:params.current_product_id',
                                    'storeId' => '${ $.externalProvider }:params.current_store_id',
                                    '__disableTmpl' => ['productId' => false, 'storeId' => false],
                                ]
                            ],
                        ],
                    ],
                ],
            ],
        ];

        return $modal;
    }

    protected function getCardGameSetsFieldset()
    {
        $content = __(
            'Select card games sets and singles.'
        );

        return [
            'children' => [
                'button_set' => $this->getButtonSet(
                    $content,
                    __('Select Card Game Sets'),
                    $this->scopePrefix . static::DATA_SCOPE_CARD_GAME
                ),
                'modal' => $this->getGenericModal(
                    __('Card Game Sets'),
                    $this->scopePrefix . static::DATA_SCOPE_CARD_GAME
                ),
                static::DATA_SCOPE_CARD_GAME => $this->getGrid($this->scopePrefix . static::DATA_SCOPE_CARD_GAME),
            ],
            'arguments' => [
                'data' => [
                    'config' => [
                        'additionalClasses' => 'admin__fieldset-section',
                        'label' => __('Card Game Sets'),
                        'collapsible' => false,
                        'componentType' => Fieldset::NAME,
                        'dataScope' => '',
                        'sortOrder' => 10,
                    ],
                ],
            ]
        ];
    }

    protected function getLegoSetsFieldset()
    {
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function modifyMeta(array $meta)
    {
        $meta = array_replace_recursive(
            $meta,
            [
                static::GROUP_SETS => [
                    'children' => [
                        $this->scopePrefix . static::DATA_SCOPE_CARD_GAME => $this->getCardGameSetsFieldset(),
                        // $this->scopePrefix . static::DATA_SCOPE_LEGO => $this->getLegoSetsFieldset()
                    ],
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'label' => __('Sets & Singles'),
                                'collapsible' => false,
                                'componentType' => Form\Fieldset::NAME,
                                'dataScope' => '',
                                'sortOrder' => 2
                            ],
                        ],

                    ],
                ],
            ]
        );

        // $meta = array_merge_recursive(
        //     $meta,
        //     [
        //         static::GROUP_SETS => [
        //             'arguments' => [
        //                 'data' => [
        //                     'config' => [
        //                         'label' => __('Sets & Singles'),
        //                         'collapsible' => false,
        //                         'opened' => true,
        //                         'componentType' => Form\Fieldset::NAME,
        //                         'sortOrder' => 2,
        //                     ],
        //                 ],
        //             ],
        //             'children' => $this->getPanelChildren(),
        //         ],
        //         static::ASSOCIATED_PRODUCT_MODAL => [
        //             'arguments' => [
        //                 'data' => [
        //                     'config' => [
        //                         'componentType' => Modal::NAME,
        //                         'dataScope' => '',
        //                         'provider' => $this->dataSourceName,
        //                         'options' => [
        //                             'title' => __('Select Associated Product'),
        //                             'buttons' => [
        //                                 [
        //                                     'text' => __('Done'),
        //                                     'class' => 'action-primary',
        //                                     'actions' => [
        //                                         [
        //                                             'targetName' => 'ns= ' . $this->associatedListingPrefix
        //                                                 . static::ASSOCIATED_PRODUCT_LISTING
        //                                                 . ', index=' . static::ASSOCIATED_PRODUCT_LISTING,
        //                                             'actionName' => 'save'
        //                                         ],
        //                                         'closeModal'
        //                                     ],
        //                                 ],
        //                             ],
        //                         ],
        //                     ],
        //                 ],
        //             ],
        //             'children' => [
        //                 'information-block1' => [
        //                     'arguments' => [
        //                         'data' => [
        //                             'config' => [
        //                                 'componentType' => Container::NAME,
        //                                 'component' => 'Magento_Ui/js/form/components/html',
        //                                 'additionalClasses' => 'message message-notice',
        //                                 'content' => __(
        //                                     'Choose a new product to delete and replace'
        //                                     . ' the current product configuration.'
        //                                 ),
        //                                 'imports' => [
        //                                     'visible' => '!ns = ${ $.ns }, index = '
        //                                         . SetsSelection::SETS_MATRIX . ':isEmpty',
        //                                     '__disableTmpl' => ['visible' => false],
        //                                 ],
        //                             ],
        //                         ],
        //                     ],
        //                 ],
        //                 'information-block2' => [
        //                     'arguments' => [
        //                         'data' => [
        //                             'config' => [
        //                                 'componentType' => Container::NAME,
        //                                 'component' => 'Magento_Ui/js/form/components/html',
        //                                 'additionalClasses' => 'message message-notice',
        //                                 'content' => __(
        //                                     'For better results, add attributes and attribute values to your products.'
        //                                 ),
        //                                 'imports' => [
        //                                     'visible' => 'ns = ${ $.ns }, index = '
        //                                         . SetsSelection::SETS_MATRIX . ':isEmpty',
        //                                     '__disableTmpl' => ['visible' => false],
        //                                 ],
        //                             ],
        //                         ],
        //                     ],
        //                 ],
        //                 static::ASSOCIATED_PRODUCT_LISTING => [
        //                     'arguments' => [
        //                         'data' => [
        //                             'config' => [
        //                                 'autoRender' => false,
        //                                 'componentType' => 'insertListing',
        //                                 'component' => 'Magento_ConfigurableProduct/js'
        //                                     . '/components/associated-product-insert-listing',
        //                                 'dataScope' => $this->associatedListingPrefix
        //                                     . static::ASSOCIATED_PRODUCT_LISTING,
        //                                 'externalProvider' => $this->associatedListingPrefix
        //                                     . static::ASSOCIATED_PRODUCT_LISTING . '.data_source',
        //                                 'selectionsProvider' => $this->associatedListingPrefix
        //                                     . static::ASSOCIATED_PRODUCT_LISTING . '.'
        //                                     . $this->associatedListingPrefix
        //                                     . static::ASSOCIATED_PRODUCT_LISTING . '.product_columns.ids',
        //                                 'ns' => $this->associatedListingPrefix . static::ASSOCIATED_PRODUCT_LISTING,
        //                                 'render_url' => $this->urlBuilder->getUrl('mui/index/render'),
        //                                 'realTimeLink' => true,
        //                                 'behaviourType' => 'simple',
        //                                 'externalFilterMode' => false,
        //                                 'currentProductId' => $this->locator->getProduct()->getId(),
        //                                 'dataLinks' => [
        //                                     'imports' => false,
        //                                     'exports' => true
        //                                 ],
        //                                 'changeProductProvider' => 'change_product',
        //                                 'productsProvider' => $this->associatedListingPrefix
        //                                     . 'sets_associated_product_listing.data_source',
        //                                 'productsColumns' => $this->associatedListingPrefix
        //                                     . 'sets_associated_product_listing'
        //                                     . '.' . $this->associatedListingPrefix
        //                                     . 'sets_associated_product_listing.product_columns',
        //                                 'productsMassAction' => $this->associatedListingPrefix
        //                                     . 'sets_associated_product_listing'
        //                                     . '.' . $this->associatedListingPrefix
        //                                     . 'sets_associated_product_listing.product_columns.ids',
        //                                 'modalWithGrid' => 'ns=' . $this->formName . ', index='
        //                                     . static::ASSOCIATED_PRODUCT_MODAL,
        //                                 'productsFilters' => $this->associatedListingPrefix
        //                                     . 'sets_associated_product_listing'
        //                                     . '.' . $this->associatedListingPrefix
        //                                     . 'sets_associated_product_listing.listing_top.listing_filters',
        //                             ],
        //                         ],
        //                     ],
        //                 ],
        //             ],
        //         ],
        //     ]
        // );

        return $meta;
    }

    /**
     * Prepares panel children configuration
     *
     * @return array
     */
    protected function getPanelChildren()
    {
        return [
            'sets_products_button_set' => $this->getButtonSet(),
            // 'sets_products_button_set2' => $this->getButtonSet2(),
            'sets-matrix' => $this->getGrid(),
        ];
    }

    // /**
    //  * Returns Buttons Set configuration
    //  *
    //  * @return array
    //  */
    // protected function getButtonSet2()
    // {
    //     // dd($this->dataScopeName);
    //     return [
    //         'arguments' => [
    //             'data' => [
    //                 'config' => [
    //                     'component' => 'TCGCollective_MarketPlace/js/components/container-sets-handler',
    //                     'formElement' => 'container',
    //                     'componentType' => 'container',
    //                     'label' => false,
    //                     'content1' => __(
    //                         'Select the sets or create new ones.'
    //                     ),
    //                     'content2' => '',
    //                     'template' => 'ui/form/components/complex',
    //                     'createSetsButton2' => 'ns = ${ $.ns }, index = create_sets_products_button2',
    //                     '__disableTmpl' => ['createSetsButton2' => false],
    //                 ],
    //             ],
    //         ],
    //         'children' => [
    //             'create_sets_products_button2' => [
    //                 'arguments' => [
    //                     'data' => [
    //                         'config' => [
    //                             'formElement' => 'container',
    //                             'componentType' => 'container',
    //                             'component' => 'Magento_Ui/js/form/components/button',
    //                             'actions' => [
    //                                 [
    //                                     'targetName' => $this->dataScopeName . '.legoSetsModal',
    //                                     'actionName' => 'trigger',
    //                                     'params' => ['active', true],
    //                                 ],
    //                                 [
    //                                     'targetName' => $this->dataScopeName . '.legoSetsModal',
    //                                     'actionName' => 'openModal',
    //                                 ],
    //                             ],
    //                             'title' => __('Lego Sets & Singles 2'),
    //                             'sortOrder' => 20,
    //                         ],
    //                     ],
    //                 ],
    //             ],
    //         ],
    //     ];
    // }

    // /**
    //  * Returns Buttons Set configuration
    //  *
    //  * @return array
    //  */
    // protected function getButtonSet()
    // {
    //     // dd($this->dataScopeName);
    //     return [
    //         'arguments' => [
    //             'data' => [
    //                 'config' => [
    //                     'component' => 'TCGCollective_MarketPlace/js/components/container-sets-handler',
    //                     'formElement' => 'container',
    //                     'componentType' => 'container',
    //                     'label' => false,
    //                     'content1' => __(
    //                         'Select the sets or create new ones.'
    //                     ),
    //                     'content2' => '',
    //                     'template' => 'ui/form/components/complex',
    //                     'createSetsButton' => 'ns = ${ $.ns }, index = create_sets_products_button',
    //                     '__disableTmpl' => ['createSetsButton' => false],
    //                 ],
    //             ],
    //         ],
    //         'children' => [
    //             'create_sets_products_button' => [
    //                 'arguments' => [
    //                     'data' => [
    //                         'config' => [
    //                             'formElement' => 'container',
    //                             'componentType' => 'container',
    //                             'component' => 'Magento_Ui/js/form/components/button',
    //                             'actions' => [
    //                                 [
    //                                     'targetName' => $this->dataScopeName . '.setsModal',
    //                                     'actionName' => 'trigger',
    //                                     'params' => ['active', true],
    //                                 ],
    //                                 [
    //                                     'targetName' => $this->dataScopeName . '.setsModal',
    //                                     'actionName' => 'openModal',
    //                                 ],
    //                             ],
    //                             'title' => __('Sets & Singles'),
    //                             'sortOrder' => 20,
    //                         ],
    //                     ],
    //                 ],
    //             ],
    //         ],
    //     ];
    // }

    // /**
    //  * Returns dynamic rows configuration
    //  *
    //  * @return array
    //  */
    // protected function getGrid()
    // {
    //     return [
    //         'arguments' => [
    //             'data' => [
    //                 'config' => [
    //                     'additionalClasses' => 'admin__field-wide',
    //                     'componentType' => DynamicRows::NAME,
    //                     'dndConfig' => [
    //                         'enabled' => false,
    //                     ],
    //                     'label' => __('Selected Sets & Singles'),
    //                     'renderDefaultRecord' => false,
    //                     'template' => 'ui/dynamic-rows/templates/grid',
    //                     'component' => 'TCGCollective_MarketPlace/js/components/dynamic-rows-sets',
    //                     'addButton' => false,
    //                     'isEmpty' => true,
    //                     'itemTemplate' => 'record',
    //                     'dataScope' => 'data',
    //                     'dataProviderFromGrid' => $this->associatedListingPrefix . static::ASSOCIATED_PRODUCT_LISTING,
    //                     'dataProviderChangeFromGrid' => 'change_product',
    //                     'dataProviderFromWizard' => 'variations',
    //                     'map' => [
    //                         'id' => 'entity_id',
    //                         'product_link' => 'product_link',
    //                         'name' => 'name',
    //                         'sku' => 'sku',
    //                         'price' => 'price_number',
    //                         'price_string' => 'price',
    //                         'price_currency' => 'price_currency',
    //                         'qty' => 'qty',
    //                         'weight' => 'weight',
    //                         'thumbnail_image' => 'thumbnail_src',
    //                         'status' => 'status',
    //                         'attributes' => 'attributes',
    //                     ],
    //                     'links' => [
    //                         'insertDataFromGrid' => '${$.provider}:${$.dataProviderFromGrid}',
    //                         'insertDataFromWizard' => '${$.provider}:${$.dataProviderFromWizard}',
    //                         'changeDataFromGrid' => '${$.provider}:${$.dataProviderChangeFromGrid}',
    //                         '__disableTmpl' => [
    //                             'insertDataFromGrid' => false,
    //                             'insertDataFromWizard' => false,
    //                             'changeDataFromGrid' => false
    //                         ],
    //                     ],
    //                     'sortOrder' => 20,
    //                     'columnsHeader' => false,
    //                     'columnsHeaderAfterRender' => true,
    //                     'modalWithGrid' => 'ns=' . $this->formName . ', index='
    //                         . static::ASSOCIATED_PRODUCT_MODAL,
    //                     'gridWithProducts' => 'ns=' . $this->associatedListingPrefix
    //                         . static::ASSOCIATED_PRODUCT_LISTING
    //                         . ', index=' . static::ASSOCIATED_PRODUCT_LISTING,
    //                 ],
    //             ],
    //         ],
    //         'children' => $this->getRows(),
    //     ];
    // }

    /**
     * Returns Dynamic rows records configuration
     *
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function getRows()
    {
        return [
            'record' => [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'componentType' => Container::NAME,
                            'isTemplate' => true,
                            'is_collection' => true,
                            'component' => 'Magento_Ui/js/dynamic-rows/record',
                            'dataScope' => '',
                        ],
                    ],
                ],
                'children' => [
                    'thumbnail_image_container' => $this->getColumn(
                        'thumbnail_image',
                        __('Image'),
                        [
                            'fit' => true,
                            'formElement' => 'fileUploader',
                            'componentType' => 'fileUploader',
                            'component' => 'Magento_ConfigurableProduct/js/components/file-uploader',
                            'elementTmpl' => 'Magento_ConfigurableProduct/components/file-uploader',
                            'fileInputName' => 'image',
                            'isMultipleFiles' => false,
                            'links' => [
                                'thumbnailUrl' => '${$.provider}:${$.parentScope}.thumbnail_image',
                                'thumbnail' => '${$.provider}:${$.parentScope}.thumbnail',
                                'smallImage' => '${$.provider}:${$.parentScope}.small_image',
                                '__disableTmpl' => [
                                    'thumbnailUrl' => false,
                                    'thumbnail' => false,
                                    'smallImage' => false
                                ],
                            ],
                            'uploaderConfig' => [
                                'url' => $this->urlBuilder->getUrl(
                                    'catalog/product_gallery/upload'
                                ),
                            ],
                            'dataScope' => 'image',
                        ],
                        [
                            'elementTmpl' => 'ui/dynamic-rows/cells/thumbnail',
                            'fit' => true,
                            'sortOrder' => 0
                        ]
                    ),
                    'name_container' => $this->getColumn(
                        'name',
                        __('Name'),
                        [],
                        ['dataScope' => 'product_link']
                    ),
                    'sku_container' => $this->getColumn(
                        'sku',
                        __('SKU'),
                        [
                            'validation' => [
                                    'required-entry' => true,
                                    'max_text_length' => Sku::SKU_MAX_LENGTH,
                                ],
                        ],
                        [
                            'elementTmpl' => 'Magento_ConfigurableProduct/components/cell-sku',
                        ]
                    ),
                    'price_container' => $this->getColumn(
                        'price',
                        __('Price'),
                        [
                            'imports' => [
                                'addbefore' => '${$.provider}:${$.parentScope}.price_currency',
                                '__disableTmpl' => ['addbefore' => false],
                            ],
                            'validation' => ['validate-zero-or-greater' => true]
                        ],
                        ['dataScope' => 'price_string']
                    ),
                    'quantity_container' => $this->getColumn(
                        'quantity',
                        __('Quantity'),
                        ['dataScope' => 'qty'],
                        ['dataScope' => 'qty']
                    ),
                    'price_weight' => $this->getColumn('weight', __('Weight')),
                    'status' => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'componentType' => 'text',
                                    'component' => 'Magento_Ui/js/form/element/abstract',
                                    'template' => 'Magento_ConfigurableProduct/components/cell-status',
                                    'label' => __('Status'),
                                    'dataScope' => 'status',
                                ],
                            ],
                        ],
                    ],
                    'attributes' => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'componentType' => Form\Field::NAME,
                                    'formElement' => Form\Element\Input::NAME,
                                    'component' => 'Magento_Ui/js/form/element/text',
                                    'elementTmpl' => 'ui/dynamic-rows/cells/text',
                                    'dataType' => Form\Element\DataType\Text::NAME,
                                    'label' => __('Attributes'),
                                ],
                            ],
                        ],
                    ],
                    'actionsList' => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'additionalClasses' => 'data-grid-actions-cell',
                                    'componentType' => 'text',
                                    'component' => 'Magento_Ui/js/form/element/abstract',
                                    'template' => 'Magento_ConfigurableProduct/components/actions-list',
                                    'label' => __('Actions'),
                                    'fit' => true,
                                    'dataScope' => 'status',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Get configuration of column
     *
     * @param string $name
     * @param \Magento\Framework\Phrase $label
     * @param array $editConfig
     * @param array $textConfig
     * @return array
     */
    protected function getColumn(
        $name,
        \Magento\Framework\Phrase $label,
        $editConfig = [],
        $textConfig = []
    ) {
        $fieldEdit['arguments']['data']['config'] = [
            'dataType' => Form\Element\DataType\Number::NAME,
            'formElement' => Form\Element\Input::NAME,
            'componentType' => Form\Field::NAME,
            'dataScope' => $name,
            'fit' => true,
            'visibleIfCanEdit' => true,
            'imports' => [
                'visible' => '${$.provider}:${$.parentScope}.canEdit',
                '__disableTmpl' => ['visible' => false],
            ],
        ];
        $fieldText['arguments']['data']['config'] = [
            'componentType' => Form\Field::NAME,
            'formElement' => Form\Element\Input::NAME,
            'elementTmpl' => 'Magento_ConfigurableProduct/components/cell-html',
            'dataType' => Form\Element\DataType\Text::NAME,
            'dataScope' => $name,
            'visibleIfCanEdit' => false,
            'labelVisible' => false,
            'imports' => [
                'visible' => '!${$.provider}:${$.parentScope}.canEdit',
                '__disableTmpl' => ['visible' => false],
            ],
        ];
        $fieldEdit['arguments']['data']['config'] = array_replace_recursive(
            $fieldEdit['arguments']['data']['config'],
            $editConfig
        );
        $fieldText['arguments']['data']['config'] = array_replace_recursive(
            $fieldText['arguments']['data']['config'],
            $textConfig
        );
        $container['arguments']['data']['config'] = [
            'componentType' => Container::NAME,
            'formElement' => Container::NAME,
            'component' => 'Magento_Ui/js/form/components/group',
            'label' => $label,
            'dataScope' => '',
            'showLabel' => false
        ];
        $container['children'] = [
            $name . '_edit' => $fieldEdit,
            $name . '_text' => $fieldText,
        ];

        return $container;
    }
}
