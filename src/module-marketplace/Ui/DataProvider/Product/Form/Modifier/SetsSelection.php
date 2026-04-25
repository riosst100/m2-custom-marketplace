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
use Magento\Ui\Component\Form\Field;
use Magento\Ui\Component\Form\Element\Input;
use Magento\Ui\Component\Form\Element\DataType\Text;

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\CustomOptions;
use Magento\Ui\Component\Form\Fieldset;
use Magento\Ui\Component\Modal;

/**
 * Data provider for Configurable panel
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SetsSelection extends AbstractModifier
{
    protected $mappingHelper;
    protected $request;


    const GROUP_NO_DATABASE_FOUND = 'no_database';
    const GROUP_NO_VARIATIONS_FOUND = 'no_variations';
    const GROUP_ACTION_TOYS_SETS = 'actiontoy_sets';
    const GROUP_ANIME_FIGURES_SETS = 'animefigures_sets';
    const GROUP_CARD_GAME_SETS = 'cardgame_sets';
    const GROUP_CARD_GAME_MTG_SETS = 'cardgamemtg_sets';
    const GROUP_LEGOSETS_SETS = 'legosets_sets';
    const GROUP_LIVING_CARD_GAME_SETS = 'livingcardgame_sets';
    const GROUP_MINIATURE_GAME_SETS = 'miniaturegame_sets';
    const GROUP_GAMES_WORKSHOP_SETS = 'gamesworkshop_sets';
    const GROUP_SPORT_CARD_SETS = 'sportcard_sets';
    const GROUP_NON_SPORT_CARD_SETS = 'nonsportcard_sets';

    const ASSOCIATED_PRODUCT_MODAL = 'sets_selection_modal';
    const ASSOCIATED_PRODUCT_LISTING = 'sets_listing';

    const CUSTOM_MODAL_LINK = 'custom_modal_link';
    const CUSTOM_MODAL_INDEX = 'custom_modal';
    const CUSTOM_MODAL_CONTENT = 'content';
    const CUSTOM_MODAL_FIELDSET = 'fieldset';
    const CONTAINER_HEADER_NAME = 'header';
    const CUSTOM_GROUP_NAME = 'my_custom_group';
    const CUSTOM_GROUP_BTN_CONTAINER_NAME = 'my_buttons';

    public const DATA_SCOPE_CARD_GAME = 'cardgame';
    public const DATA_SCOPE_NO_DATABASE = 'no_database';
    public const DATA_SCOPE_NO_VARIATIONS = 'no_variations';
    public const DATA_SCOPE_LEGO = 'lego';
    public const DATA_SCOPE_MINIATURE_GAME = 'miniaturegame';
    public const DATA_SCOPE_LIVING_CARD_GAME = 'livingcardgame';
    public const DATA_SCOPE_SPORT_CARD = 'sportcard';
    public const DATA_SCOPE_NON_SPORT_CARD = 'nonsportcard';

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
    private $platformDbHelper;

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
        \TCGCollective\PlatformDatabase\Helper\Data $platformDbHelper,
        $formName,
        $dataScopeName,
        $dataSourceName,
        $associatedListingPrefix = ''
    ) {
        $this->locator = $locator;
        $this->urlBuilder = $urlBuilder;
        $this->formName = $formName;
        $this->dataScopeName = $dataScopeName;
        $this->dataSourceName = $dataSourceName;
        $this->associatedListingPrefix = $associatedListingPrefix;

        $this->platformDbHelper = $platformDbHelper;
    }

    /**
     * @inheritdoc
     */
    public function modifyData(array $data)
    {
        return $data;
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function modifyMeta(array $meta)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->mappingHelper = $objectManager->create(\TCGCollective\ProductAttributesLink\Helper\Data::class);
        $this->request = $objectManager->get('\Magento\Framework\App\RequestInterface');

//         $productTypeCode = null;
//         $productTypeVal = null;
//         $entityCode = null;
//         $entityVal = null;

        $currentProduct = $this->locator->getProduct();
//         // if ($currentProduct->getTypeId() != 'simple') {
//         //     return $meta;
//         // }


//         // dd($currentProduct->getData('miniature_game'));
//         // dd($productTypeCode);

$isCustomTableExist = false;
            $isHavingSingles = false;
            $isSealedProducts = false;
            $setsSelectionScope = false;

        $source = $this->request->getParam('source');
        
        if ($currentProduct && $currentProduct->getId()) {
            $source = $currentProduct->getData('source');

            $productTypeCode = null;
            $productTypeVal = null;

            $entityCode = null;
            $entityVal = null;

            $productType = 'simple';
            if ($currentProduct->getData('card_game')) {
                $productTypeCode = 'card_product_type';
                $productTypeVal = $currentProduct->getData($productTypeCode);

                $entityCode = 'card_game';
                $entityVal = $currentProduct->getData($entityCode);
            }

            if ($currentProduct->getData('sc_categories')) {
                

                $productTypeCode = 'sc_product_type';
                $productTypeVal = $currentProduct->getData($productTypeCode);
                $productTypeLabel = $this->mappingHelper->getOptionLabelById($productTypeCode, $productTypeVal);

                if ($productTypeLabel == "Singles") {
                    $productTypeCode = 'sc_set_type';
                    $productTypeVal = $currentProduct->getData($productTypeCode);
                }

                $entityCode = 'sc_categories';
                $entityVal = $currentProduct->getData($entityCode);
//                 $entityCode = 'sc_sport';
//                 $entityVal = $currentProduct->getData('sc_sport');

//                 $productTypeCode = $prefix.'sc_product_type';
//                 $productTypeVal = $currentProduct->getData($prefix.'sc_product_type');
            }
            // dd($source);
       
            // dd($source);
            // if ($productType == "configurable" || $source != "database") {
            //     $data['is_custom_table_exist'] = false;
            //     $data['is_singles'] = false;
            //     $data['is_sealedproducts'] = false;
            // } else {
            if ($this->request->getParam('debug')) {
                $customTableDetails = $this->mappingHelper->getCustomTableDetails($productTypeCode, $productTypeVal, $entityCode, $entityVal, $productType);
                $isCustomTableExist = $customTableDetails['is_custom_table_exist'] ?? false;
                $isHavingSingles = $customTableDetails['is_singles'] ?? false;
                $setsSelectionScope = $customTableDetails['sets_selection_scope'] ?? null;
            }
        } else {

//         // dd($setsSelectionScope);

//         if ($currentProduct && $currentProduct->getId()) {
            
//         }
        }

        
        // dd($source);

        if ($name = $this->getGeneralPanelName($meta)) {
            $meta[$name]['children']['is_having_singles']['arguments']['data']['config']  = [
                'formElement' => 'input',
                'componentType' => Field::NAME,
                'visible' => 0,
                'required' => 0,
                'label' => __('Is Having Singles'),
                'source' => $name,
                'value' => $isHavingSingles,
                'dataScope' => 'is_having_singles'
            ];
            $meta[$name]['children']['is_sealedproducts']['arguments']['data']['config']  = [
                'formElement' => 'input',
                'componentType' => Field::NAME,
                'visible' => 0,
                'required' => 0,
                'label' => __('Is Sealed Products'),
                'source' => $name,
                'value' => !$isHavingSingles ? true : false,
                'dataScope' => 'is_sealedproducts'
            ];
            $meta[$name]['children']['is_having_release']['arguments']['data']['config']  = [
                'formElement' => 'input',
                'componentType' => Field::NAME,
                'visible' => 0,
                'required' => 0,
                'label' => __('Is Have Release'),
                'source' => $name,
                'value' => false,
                'dataScope' => 'is_having_release'
            ];
            $meta[$name]['children']['source']['arguments']['data']['config']  = [
                'formElement' => 'input',
                'componentType' => Field::NAME,
                'visible' => 0,
                'required' => 0,
                'label' => __('Created Source'),
                'source' => $name,
                'value' => $source,
                'dataScope' => 'source'
            ];
        }

        $meta = array_merge_recursive(
            $meta,
            [
                static::GROUP_NO_DATABASE_FOUND => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'label' => __('No Database Available'),
                                'visible' => 0,
                                'collapsible' => false,
                                'opened' => true,
                                'componentType' => Form\Fieldset::NAME,
                                'sortOrder' => 3,
                            ],
                        ],
                    ],
                    'children' => $this->getNoDatabasePanelChildren()
                ],
                static::GROUP_NO_VARIATIONS_FOUND => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'label' => __('No Variations Available'),
                                'visible' => 0,
                                'collapsible' => false,
                                'opened' => true,
                                'componentType' => Form\Fieldset::NAME,
                                'sortOrder' => 3,
                            ],
                        ],
                    ],
                    'children' => $this->getNoVariationsPanelChildren()
                ],
                static::GROUP_ACTION_TOYS_SETS => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'label' => __('Sets/Release'),
                                'visible' => $isCustomTableExist && $setsSelectionScope == static::GROUP_ACTION_TOYS_SETS,
                                'collapsible' => false,
                                'opened' => true,
                                'componentType' => Form\Fieldset::NAME,
                                'sortOrder' => 3,
                            ],
                        ],
                    ],
                    'children' => $this->getPanelChildren('actiontoy', __('Select Sets/Release'))
                ],
                static::GROUP_CARD_GAME_SETS => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'label' => __('Set/Release'),
                                'visible' => $isCustomTableExist && $setsSelectionScope == static::GROUP_CARD_GAME_SETS,
                                'collapsible' => false,
                                'opened' => true,
                                'componentType' => Form\Fieldset::NAME,
                                'sortOrder' => 3,
                            ],
                        ],
                    ],
                    'children' => $this->getPanelChildren('cardgame', __('Select Set/Release'))
                ],
                static::GROUP_CARD_GAME_MTG_SETS => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'label' => __('Set/Release'),
                                'visible' => $isCustomTableExist && $setsSelectionScope == static::GROUP_CARD_GAME_MTG_SETS,
                                'collapsible' => false,
                                'opened' => true,
                                'componentType' => Form\Fieldset::NAME,
                                'sortOrder' => 3,
                            ],
                        ],
                    ],
                    'children' => $this->getPanelChildren('cardgamemtg', __('Select Set/Release'))
                ],
                static::GROUP_LEGOSETS_SETS => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'label' => __('Set/Release'),
                                'visible' => $isCustomTableExist && $setsSelectionScope == static::GROUP_LEGOSETS_SETS,
                                'collapsible' => false,
                                'opened' => true,
                                'componentType' => Form\Fieldset::NAME,
                                'sortOrder' => 3,
                            ],
                        ],
                    ],
                    'children' => $this->getPanelChildren('legosets', __('Select Set/Release'))
                ],
                static::GROUP_MINIATURE_GAME_SETS => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'label' => __('Set/Release'),
                                'visible' => $isCustomTableExist && $setsSelectionScope == static::GROUP_MINIATURE_GAME_SETS,
                                'collapsible' => false,
                                'opened' => true,
                                'componentType' => Form\Fieldset::NAME,
                                'sortOrder' => 3,
                            ],
                        ],
                    ],
                    'children' => $this->getPanelChildren('miniaturegame', __('Select Set/Release'))
                ],
                static::GROUP_GAMES_WORKSHOP_SETS => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'label' => __('Set/Release'),
                                'visible' => $isCustomTableExist && $setsSelectionScope == static::GROUP_GAMES_WORKSHOP_SETS,
                                'collapsible' => false,
                                'opened' => true,
                                'componentType' => Form\Fieldset::NAME,
                                'sortOrder' => 3,
                            ],
                        ],
                    ],
                    'children' => $this->getPanelChildren('gamesworkshop', __('Select Set/Release'))
                ],
                static::GROUP_LIVING_CARD_GAME_SETS => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'label' => __('Set/Release'),
                                'visible' => $isCustomTableExist && $setsSelectionScope == static::GROUP_LIVING_CARD_GAME_SETS,
                                'collapsible' => false,
                                'opened' => true,
                                'componentType' => Form\Fieldset::NAME,
                                'sortOrder' => 3,
                            ],
                        ],
                    ],
                    'children' => $this->getPanelChildren('livingcardgame', __('Select Set/Release'))
                ],
                static::GROUP_SPORT_CARD_SETS => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'label' => __('Set/Release'),
                                'visible' => $isCustomTableExist && $setsSelectionScope == static::GROUP_SPORT_CARD_SETS,
                                'collapsible' => false,
                                'opened' => true,
                                'componentType' => Form\Fieldset::NAME,
                                'sortOrder' => 3,
                            ],
                        ],
                    ],
                    'children' => $this->getPanelChildren('sportcard', __('Select Set/Release'))
                ],
                static::GROUP_NON_SPORT_CARD_SETS => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'label' => __('Set/Release'),
                                'visible' => $isCustomTableExist && $setsSelectionScope == static::GROUP_NON_SPORT_CARD_SETS,
                                'collapsible' => false,
                                'opened' => true,
                                'componentType' => Form\Fieldset::NAME,
                                'sortOrder' => 3,
                            ],
                        ],
                    ],
                    'children' => $this->getPanelChildren('nonsportcard', __('Select Set/Release'))
                ],
            ]
        );

        return $meta;
    }

    /**
     * Prepares panel children configuration
     *
     * @return array
     */
    protected function getNoDatabasePanelChildren()
    {
        return [
            static::DATA_SCOPE_NO_DATABASE => $this->getNoDatabaseButtonSet()
        ];
    }

    /**
     * Prepares panel children configuration
     *
     * @return array
     */
    protected function getNoVariationsPanelChildren()
    {
        return [
            static::DATA_SCOPE_NO_VARIATIONS => $this->getNoVariationsButtonSet()
        ];
    }

    
    /**
     * Returns Buttons Set configuration
     *
     * @return array
     */
    protected function getNoVariationsButtonSet()
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'component' => 'TCGCollective_MarketPlace/js/components/container-sets-handler',
                        'formElement' => 'container',
                        'componentType' => 'container',
                        'label' => false,
                        'content1' => __(
                            "No variations available for product that you want to create."
                        ),
                        'content2' => '',
                        'template' => 'ui/form/components/complex',
                        // 'create' . $scope . 'SetsButton' => 'ns = ${ $.ns }, index = ' . $scope . '_button',
                        // '__disableTmpl' => ['create' . $scope . 'SetsButton' => false],
                    ],
                ],
            ],
            'children' => [],
        ];
    }

    /**
     * Returns Buttons Set configuration
     *
     * @return array
     */
    protected function getNoDatabaseButtonSet()
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'component' => 'TCGCollective_MarketPlace/js/components/container-sets-handler',
                        'formElement' => 'container',
                        'componentType' => 'container',
                        'label' => false,
                        'content1' => __(
                            "No product data available in our database for the selected option. Click 'Add Product Information' to manually input the product information."
                        ),
                        'content2' => '',
                        'template' => 'ui/form/components/complex',
                        // 'create' . $scope . 'SetsButton' => 'ns = ${ $.ns }, index = ' . $scope . '_button',
                        // '__disableTmpl' => ['create' . $scope . 'SetsButton' => false],
                    ],
                ],
            ],
            'children' => [
                static::DATA_SCOPE_NO_DATABASE . '_button' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'formElement' => 'container',
                                'componentType' => 'container',
                                'component' => 'Magento_Ui/js/form/components/html',
                                'content' => '<button id="switch_add_product_non_db" class="main_color_bg" style="max-width:250px;">Add Product Information</button>',
                                'title' => 'Add Product Information',
                                'sortOrder' => 1,
                            ],
                        ],
                    ],
                ],
            ],
            'children' => [
                static::DATA_SCOPE_NO_DATABASE . '_button' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'formElement' => 'container',
                                'componentType' => 'container',
                                'component' => 'TCGCollective_MarketPlace/js/form/components/addProductInformationButton',
                                'actions' => [
                                    [
                                        'actionName' => 'openModal',
                                    ],
                                ],
                                'title' => __('Add Product Information'),
                                'sortOrder' => 1,
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    public function getAddProductLink() 
    {
        return $this->urlBuilder->getUrl(
            'catalog/product/new',
            ['set' => 4,'type' => 'simple']
        );
    }

    /**
     * Prepares panel children configuration
     *
     * @return array
     */
    protected function getPanelChildren($scope, $title)
    {
        return [
            static::DATA_SCOPE_CARD_GAME => $this->getButtonSet($scope, $title),
            'sets-matrix' => $this->getGrid($scope),
            'release-matrix' => $this->getReleaseGrid($scope),
            'sealedproducts-matrix' => $this->getSealedProductsGrid($scope),
            'singles-matrix' => $this->getSinglesGrid($scope)
        ];
    }

    /**
     * Returns Buttons Set configuration
     *
     * @return array
     */
    protected function getButtonSet($scope, $title)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'component' => 'TCGCollective_MarketPlace/js/components/container-sets-handler',
                        'formElement' => 'container',
                        'componentType' => 'container',
                        'label' => false,
                        'content1' => __(
                            'Select Set/Release for this product.'
                        ),
                        'content2' => '',
                        'template' => 'ui/form/components/complex',
                        'create' . $scope . 'SetsButton' => 'ns = ${ $.ns }, index = ' . $scope . '_button',
                        '__disableTmpl' => ['create' . $scope . 'SetsButton' => false],
                    ],
                ],
            ],
            'children' => [
                $scope . '_button' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'formElement' => 'container',
                                'componentType' => 'container',
                                'component' => 'Magento_Ui/js/form/components/button',
                                'actions' => [
                                    [
                                        'targetName' => $this->dataScopeName . '.' . $scope . '.setsModal',
                                        'actionName' => 'trigger',
                                        'params' => ['active', true],
                                    ],
                                    [
                                        'targetName' => $this->dataScopeName . '.' . $scope . '.setsModal',
                                        'actionName' => 'openModal',
                                    ],
                                ],
                                'title' => $title,
                                'sortOrder' => 20,
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Returns dynamic rows configuration
     *
     * @return array
     */
    protected function getReleaseGrid($scope = null)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'additionalClasses' => 'admin__field-wide',
                        'componentType' => DynamicRows::NAME,
                        'dndConfig' => [
                            'enabled' => false,
                        ],
                        'label' => __('Selected Release'),
                        'renderDefaultRecord' => true,
                        'template' => 'ui/dynamic-rows/templates/grid',
                        'component' => 'TCGCollective_MarketPlace/js/components/dynamic-rows-release',
                        'addButton' => false,
                        'isEmpty' => true,
                        'itemTemplate' => 'record',
                        'dataScope' => 'data',
                        'dataProviderFromGrid' => $this->associatedListingPrefix . static::ASSOCIATED_PRODUCT_LISTING,
                        'dataProviderChangeFromGrid' => 'change_product',
                        'dataProviderFromWizard' => 'release',
                        'links' => [
                            'insertDataFromGrid' => '${$.provider}:${$.dataProviderFromGrid}',
                            'insertDataFromWizard' => '${$.provider}:${$.dataProviderFromWizard}',
                            'changeDataFromGrid' => '${$.provider}:${$.dataProviderChangeFromGrid}',
                            '__disableTmpl' => [
                                'insertDataFromGrid' => false,
                                'insertDataFromWizard' => false,
                                'changeDataFromGrid' => false
                            ],
                        ],
                        'sortOrder' => 90,
                        'columnsHeader' => false,
                        'columnsHeaderAfterRender' => true,
                        'modalWithGrid' => 'ns=' . $this->formName . ', index='
                            . static::ASSOCIATED_PRODUCT_MODAL,
                        'gridWithProducts' => 'ns=' . $this->associatedListingPrefix
                            . static::ASSOCIATED_PRODUCT_LISTING
                            . ', index=' . static::ASSOCIATED_PRODUCT_LISTING
                    ],
                ],
            ],
            'children' => $this->getReleaseRows($scope)
        ];
    }

    /**
     * Returns dynamic rows configuration
     *
     * @return array
     */
    protected function getSealedProductsGrid($scope = null)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'additionalClasses' => 'admin__field-wide',
                        'componentType' => DynamicRows::NAME,
                        'dndConfig' => [
                            'enabled' => false,
                        ],
                        'label' => __('Selected Sealed Products'),
                        'renderDefaultRecord' => true,
                        'template' => 'ui/dynamic-rows/templates/grid',
                        'component' => 'TCGCollective_MarketPlace/js/components/dynamic-rows-sealedproducts',
                        'addButton' => false,
                        'isEmpty' => true,
                        'itemTemplate' => 'record',
                        'dataScope' => 'data',
                        'dataProviderFromGrid' => $this->associatedListingPrefix . static::ASSOCIATED_PRODUCT_LISTING,
                        'dataProviderChangeFromGrid' => 'change_product',
                        'dataProviderFromWizard' => 'sealedproducts',
                        'links' => [
                            'insertDataFromGrid' => '${$.provider}:${$.dataProviderFromGrid}',
                            'insertDataFromWizard' => '${$.provider}:${$.dataProviderFromWizard}',
                            'changeDataFromGrid' => '${$.provider}:${$.dataProviderChangeFromGrid}',
                            '__disableTmpl' => [
                                'insertDataFromGrid' => false,
                                'insertDataFromWizard' => false,
                                'changeDataFromGrid' => false
                            ],
                        ],
                        'sortOrder' => 100,
                        'columnsHeader' => false,
                        'columnsHeaderAfterRender' => true,
                        'modalWithGrid' => 'ns=' . $this->formName . ', index='
                            . static::ASSOCIATED_PRODUCT_MODAL,
                        'gridWithProducts' => 'ns=' . $this->associatedListingPrefix
                            . static::ASSOCIATED_PRODUCT_LISTING
                            . ', index=' . static::ASSOCIATED_PRODUCT_LISTING
                    ],
                ],
            ],
            'children' => $this->getSealedProductsRows($scope)
        ];
    }

    /**
     * Returns dynamic rows configuration
     *
     * @return array
     */
    protected function getSinglesGrid($scope = null)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'additionalClasses' => 'admin__field-wide',
                        'componentType' => DynamicRows::NAME,
                        'dndConfig' => [
                            'enabled' => false,
                        ],
                        'label' => __('Selected Singles'),
                        'renderDefaultRecord' => true,
                        'template' => 'ui/dynamic-rows/templates/grid',
                        'component' => 'TCGCollective_MarketPlace/js/components/dynamic-rows-singles',
                        'addButton' => false,
                        'isEmpty' => true,
                        'itemTemplate' => 'record',
                        'dataScope' => 'data',
                        'dataProviderFromGrid' => $this->associatedListingPrefix . static::ASSOCIATED_PRODUCT_LISTING,
                        'dataProviderChangeFromGrid' => 'change_product',
                        'dataProviderFromWizard' => 'singles',
                        'map' => [
                            'id' => 'entity_id',
                            'product_link' => 'product_link',
                            'name' => 'name',
                            'sku' => 'sku',
                            'price' => 'price_number',
                            'price_string' => 'price',
                            'price_currency' => 'price_currency',
                            'qty' => 'qty',
                            'weight' => 'weight',
                            'thumbnail_image' => 'thumbnail_src',
                            'status' => 'status',
                            'attributes' => 'attributes',
                        ],
                        'links' => [
                            'insertDataFromGrid' => '${$.provider}:${$.dataProviderFromGrid}',
                            'insertDataFromWizard' => '${$.provider}:${$.dataProviderFromWizard}',
                            'changeDataFromGrid' => '${$.provider}:${$.dataProviderChangeFromGrid}',
                            '__disableTmpl' => [
                                'insertDataFromGrid' => false,
                                'insertDataFromWizard' => false,
                                'changeDataFromGrid' => false
                            ],
                        ],
                        'sortOrder' => 100,
                        'columnsHeader' => false,
                        'columnsHeaderAfterRender' => true,
                        'modalWithGrid' => 'ns=' . $this->formName . ', index='
                            . static::ASSOCIATED_PRODUCT_MODAL,
                        'gridWithProducts' => 'ns=' . $this->associatedListingPrefix
                            . static::ASSOCIATED_PRODUCT_LISTING
                            . ', index=' . static::ASSOCIATED_PRODUCT_LISTING
                    ],
                ],
            ],
            'children' => $this->getSinglesRows($scope)
        ];
    }
    
    protected function getReleaseRows($scope)
    {
        $rowChildren = [];
        $rows = [];
        
        // if ($scope != "actiontoy") {
        //     dd($scope);
        // }

        $customTableData = $this->platformDbHelper->getCustomTableDataByKey($scope);
        // if ($scope == "cardgame") {
        // dd($customTableData);
        // }
        if ($customTableData) {
            $productCreationReleaseRows = $customTableData['productCreationReleaseRows'] ?? null;
            if ($productCreationReleaseRows) {
                $rows = $productCreationReleaseRows;
            }
        }

        if ($scope == "cardgame") {
        // dd($rows);
        }

        if ($rows) {
            foreach($rows as $index => $row) {
                $code = $row['code'];
                $label = $row['label'];

                if ($code == "images") {
                    $rowChildren['images'] = [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'componentType' => Field::NAME,
                                    'formElement' => Input::NAME,
                                    'elementTmpl' => 'ui/dynamic-rows/cells/thumbnail',
                                    'dataType' => Text::NAME,
                                    'dataScope' => 'images',
                                    'fit' => true,
                                    'label' => __('Images'),
                                    'labelVisible' => false,
                                    'sortOrder' => 0,
                                ],
                            ],
                        ],
                    ];
                } else {
                    $rowChildren[$code . '_container'] = $this->getTextColumn(
                        $code,
                        false,
                        __($label),
                        $index+1
                    );
                }
            }
        }

        if ($scope == "cardgame") {
        // dd($rowChildren);
        }

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
                'children' => $rowChildren
            ],
        ];
    }

    /**
     * Returns Dynamic rows records configuration
     *
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function getSealedProductsRows($scope)
    {
        $rowChildren = [];
        $rows = [];
        
        // if ($scope != "actiontoy") {
        //     dd($scope);
        // }

        $customTableData = $this->platformDbHelper->getCustomTableDataByKey($scope);
        // if ($scope == "cardgame") {
        // dd($customTableData);
        // }
        if ($customTableData) {
            $productCreationSetsRows = $customTableData['productCreationSealedProductsRows'] ?? null;
            if ($productCreationSetsRows) {
                $rows = $productCreationSetsRows;
            }
        }

        if ($scope == "cardgame") {
        // dd($rows);
        }

        if ($rows) {
            foreach($rows as $index => $row) {
                $code = $row['code'];
                $label = $row['label'];

                if ($code == "images") {
                    $rowChildren['images'] = [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'componentType' => Field::NAME,
                                    'formElement' => Input::NAME,
                                    'elementTmpl' => 'ui/dynamic-rows/cells/thumbnail',
                                    'dataType' => Text::NAME,
                                    'dataScope' => 'images',
                                    'fit' => true,
                                    'label' => __('Images'),
                                    'labelVisible' => false,
                                    'sortOrder' => 0,
                                ],
                            ],
                        ],
                    ];
                } else {
                    $rowChildren[$code . '_container'] = $this->getTextColumn(
                        $code,
                        false,
                        __($label),
                        $index+1
                    );
                }
            }
        }

        if ($scope == "cardgame") {
        // dd($rowChildren);
        }

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
                'children' => $rowChildren
            ],
        ];
    }

    /**
     * Returns Dynamic rows records configuration
     *
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function getSinglesRows($scope)
    {
        $rowChildren = [];
        $rows = [];
        
        // if ($scope != "actiontoy") {
        //     dd($scope);
        // }

        $customTableData = $this->platformDbHelper->getCustomTableDataByKey($scope);
        // if ($scope == "cardgame") {
        // dd($customTableData);
        // }
        if ($customTableData) {
            $productCreationSinglesRows = $customTableData['productCreationSinglesRows'] ?? null;
            if ($productCreationSinglesRows) {
                $rows = $productCreationSinglesRows;
            }
        }

        if ($scope == "cardgame") {
        // dd($rows);
        }

        if ($rows) {
            foreach($rows as $index => $row) {
                $code = $row['code'];
                $label = $row['label'];

                if ($code == "images") {
                    $rowChildren['images'] = [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'componentType' => Field::NAME,
                                    'formElement' => Input::NAME,
                                    'elementTmpl' => 'ui/dynamic-rows/cells/thumbnail',
                                    'dataType' => Text::NAME,
                                    'dataScope' => 'images',
                                    'fit' => true,
                                    'label' => __('Images'),
                                    'labelVisible' => false,
                                    'sortOrder' => 0,
                                ],
                            ],
                        ],
                    ];
                } else {
                    $rowChildren[$code . '_container'] = $this->getTextColumn(
                        $code,
                        false,
                        __($label),
                        $index+1
                    );
                }
            }
        }

        if ($scope == "cardgame") {
        // dd($rowChildren);
        }

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
                'children' => $rowChildren
            ],
        ];
    }

    /**
     * Returns dynamic rows configuration
     *
     * @return array
     */
    protected function getGrid($scope = null)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'additionalClasses' => 'admin__field-wide',
                        'componentType' => DynamicRows::NAME,
                        'dndConfig' => [
                            'enabled' => false,
                        ],
                        'label' => __('Selected Sets'),
                        'renderDefaultRecord' => true,
                        'template' => 'ui/dynamic-rows/templates/grid',
                        'component' => 'TCGCollective_MarketPlace/js/components/dynamic-rows-sets',
                        'addButton' => false,
                        'isEmpty' => true,
                        'itemTemplate' => 'record',
                        'dataScope' => 'data',
                        'dataProviderFromGrid' => $this->associatedListingPrefix . static::ASSOCIATED_PRODUCT_LISTING,
                        'dataProviderChangeFromGrid' => 'change_product',
                        'dataProviderFromWizard' => 'sets',
                        'map' => [
                            'id' => 'entity_id',
                            'product_link' => 'product_link',
                            'name' => 'name',
                            'sku' => 'sku',
                            'price' => 'price_number',
                            'price_string' => 'price',
                            'price_currency' => 'price_currency',
                            'qty' => 'qty',
                            'weight' => 'weight',
                            'thumbnail_image' => 'thumbnail_src',
                            'status' => 'status',
                            'attributes' => 'attributes',
                        ],
                        'links' => [
                            'insertDataFromGrid' => '${$.provider}:${$.dataProviderFromGrid}',
                            'insertDataFromWizard' => '${$.provider}:${$.dataProviderFromWizard}',
                            'changeDataFromGrid' => '${$.provider}:${$.dataProviderChangeFromGrid}',
                            '__disableTmpl' => [
                                'insertDataFromGrid' => false,
                                'insertDataFromWizard' => false,
                                'changeDataFromGrid' => false
                            ],
                        ],
                        'sortOrder' => 10,
                        'columnsHeader' => false,
                        'columnsHeaderAfterRender' => true,
                        'modalWithGrid' => 'ns=' . $this->formName . ', index='
                            . static::ASSOCIATED_PRODUCT_MODAL,
                        'gridWithProducts' => 'ns=' . $this->associatedListingPrefix
                            . static::ASSOCIATED_PRODUCT_LISTING
                            . ', index=' . static::ASSOCIATED_PRODUCT_LISTING,
                    ],
                ],
            ],
            'children' => $this->getRows($scope)
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
     * Returns Dynamic rows records configuration
     *
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function getRows($scope)
    {
        $rowChildren = [];
        $rows = [];
        
        // if ($scope != "actiontoy") {
        //     dd($scope);
        // }

        $customTableData = $this->platformDbHelper->getCustomTableDataByKey($scope);
        // if ($scope == "cardgame") {
        // dd($customTableData);
        // }
        if ($customTableData) {
            $productCreationSetsRows = $customTableData['productCreationSetsRows'] ?? null;
            if ($productCreationSetsRows) {
                $rows = $productCreationSetsRows;
            }
        }

        if ($scope == "cardgame") {
        // dd($rows);
        }

        if ($rows) {
            foreach($rows as $index => $row) {
                $code = $row['code'];
                $label = $row['label'];

                if ($code == "images") {
                    $rowChildren['images'] = [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'componentType' => Field::NAME,
                                    'formElement' => Input::NAME,
                                    'elementTmpl' => 'ui/dynamic-rows/cells/thumbnail',
                                    'dataType' => Text::NAME,
                                    'dataScope' => 'images',
                                    'fit' => true,
                                    'label' => __('Images'),
                                    'labelVisible' => false,
                                    'sortOrder' => 0,
                                ],
                            ],
                        ],
                    ];
                } else {
                    $rowChildren[$code . '_container'] = $this->getTextColumn(
                        $code,
                        false,
                        __($label),
                        $index+1
                    );
                }
            }
        }

        // if ($scope == "cardgame") {
        // dd($rowChildren);
        // }
        
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
                'children' => $rowChildren
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
