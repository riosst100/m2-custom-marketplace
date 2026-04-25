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
use Magento\Ui\Component\Form\Element\Select;


use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\CustomOptions;
use Magento\Ui\Component\Form\Fieldset;
use Magento\Ui\Component\Modal;

/**
 * Data provider for Configurable panel
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ListingCountry extends AbstractModifier
{
    protected $arrayManager;
    protected $websiteCollectionCache;
    protected $helper;
    protected $storeManager;
    protected $mappingHelper;
    protected $request;


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
        $associatedListingPrefix = ''
    ) {
        $this->locator = $locator;
        $this->urlBuilder = $urlBuilder;
        $this->formName = $formName;
        $this->dataScopeName = $dataScopeName;
        $this->dataSourceName = $dataSourceName;
        $this->associatedListingPrefix = $associatedListingPrefix;
    }

    /**
     * @inheritdoc
     */
    public function modifyData(array $data)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->arrayManager = $objectManager->create(\Magento\Framework\Stdlib\ArrayManager::class);
        $this->websiteCollectionCache = $objectManager->create(\TCGCollective\MarketPlace\Model\WebsiteCollectionCache::class);
        $this->helper = $objectManager->create(\Lof\MarketPlace\Helper\Data::class);
        $this->storeManager = $objectManager->create(\Magento\Store\Model\StoreManagerInterface::class);

        $product = $this->locator->getProduct();
        $productId = $product->getId();

        $firstPublish = true;

        $countryData = [];

        $websiteCollection = $this->websiteCollectionCache->getWebsiteCollection();
        if ($websiteCollection) {
            $websites = $websiteCollection['items'] ?? [];
            if ($websites) {
                foreach ($websites as $index => $website) {
                    $websiteName = $website['name'] ?? '';

                    $isDefault = $this->storeManager->getWebsite()->getId() == $website['website_id'] ? true : false;
                    if ($isDefault) {
                        $websiteName .= ' (Default Country)';
                    }
                    // if () {
                    //     $priceHtml .= $websiteName.' <b>'.$this->getProductPriceByWebsiteId($item['entity_id'], $website).'</b>';
                    //     if ($priceHtml && $index != count($websites)) {
                    //         $priceHtml .= '<br/>';
                    //     }
                    // }
                    $isPublished = $this->helper->checkPublishStatusByWebsiteData($website, $productId);
                    if ($isPublished) {
                        $firstPublish = false;
                    }
                    $countryData[] = [
                        "id" => $website['website_id'] ?? $index,
                        "country" => $websiteName,
                        "is_published" => $isPublished ? __('Published') : __('Draft'),
                        // "is_published" => $isPublished ? 1 : 2,
                        "publish_button" => $isPublished ? 1 : 2,
                        "website_id" => $website['website_id'] ?? '',
                        "store_id" => $this->helper->getWebsiteDefaultStoreId($website),
                        "edit_button" => 'pending',
                        "product_data" => '',
                        "is_default_country" => $isDefault,
                        "is_first_publish" => $firstPublish
                    ];
                }
            }
        }

        usort($countryData, function ($a, $b) {
            return $b['is_default_country'] <=> $a['is_default_country'];
        });

        // dd($countryData);

        if ($firstPublish) {
            $countryData = array_map(function ($country) {
                if (!$country['is_default_country']) { // Only update if is_default_country is false
                    $country['edit_button'] = "first_publish";
                    $country['publish_button'] = "first_publish";
                }
                return $country;
            }, $countryData);                      
        }

        $path = $productId . '/' . self::DATA_SOURCE_DEFAULT . '/' . 'listing_country';
        $data = $this->arrayManager->set($path, $data, $countryData);

        // dd($data);

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
        $this->websiteCollectionCache = $objectManager->create(\TCGCollective\MarketPlace\Model\WebsiteCollectionCache::class);
        $this->helper = $objectManager->create(\Lof\MarketPlace\Helper\Data::class);
        $this->storeManager = $objectManager->create(\Magento\Store\Model\StoreManagerInterface::class);

        $isVisible = false;

        $currentProduct = $this->locator->getProduct();
        if ($currentProduct->getId() && $this->request->getParam('action') == 'publish') {
            $isVisible = true;
        }

        $firstPublish = true;

        $websiteCollection = $this->websiteCollectionCache->getWebsiteCollection();
        if ($websiteCollection) {
            $websitesData = $websiteCollection['items'] ?? [];
            if ($websitesData) {
                foreach ($websitesData as $index => $website) {
                    if ($website['website_id'] != $this->storeManager->getWebsite()->getId()) {
                        continue;
                    }
                    $websiteName = $website['name'] ?? '';
                    if ($this->helper->checkPublishStatusByWebsiteData($website, $currentProduct->getId())) {
                        $firstPublish = false;
                        break;
                    }
                }
            }
        }

        $meta = array_merge_recursive(
            $meta,
            [
                'listing-country-group' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'label' => __('Listing Country'),
                                'visible' => $isVisible,
                                'collapsible' => false,
                                'opened' => true,
                                'componentType' => Form\Fieldset::NAME,
                                'sortOrder' => 1,
                                'dataScope' => 'data.product'
                            ],
                        ],
                    ],
                    'children' => $this->getPanelChildren($firstPublish)
                ]
            ]
        );

        return $meta;
    }

    /**
     * Prepares panel children configuration
     *
     * @return array
     */
    protected function getPanelChildren($firstPublish)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->storeManager = $objectManager->create(\Magento\Store\Model\StoreManagerInterface::class);
        return [
            'first_publish_info' => [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'componentType' => Container::NAME,
                            'component' => 'Magento_Ui/js/form/components/html',
                            'additionalClasses' => 'message message-info',
                            'content' => __(
                                "You can publish to another country after published to your Default Country."
                            ),
                            'visible' => $firstPublish
                        ],
                    ],
                ],
            ],
            'save_changes_info' => [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'componentType' => Container::NAME,
                            'component' => 'Magento_Ui/js/form/components/html',
                            'additionalClasses' => 'message message-info need-save-reminder',
                            'content' => __(
                                "Don't forget to click <b>Save Listing Country</b> button to save all changes."
                            ),
                            'visible' => 1
                        ],
                    ],
                ],
            ],
            'listing_country' => $this->getGrid(),
            'empty_price_error' => [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'componentType' => Container::NAME,
                            'component' => 'Magento_Ui/js/form/components/html',
                            'additionalClasses' => 'message message-warning publish-error-mg empty-price-error-mg',
                            'content' => __(
                                "Please input the listing Price below and try again."
                            ),
                            'visible' => 0
                        ],
                    ],
                ],
            ],
            'editing_product_data_info' => [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'componentType' => Container::NAME,
                            'component' => 'Magento_Ui/js/form/components/html',
                            'additionalClasses' => 'editing-country-msg message message-notice',
                            'content' => __(
                                "Editing listing data on <b>%1</b> country.",
                                $this->storeManager->getWebsite()->getName()
                            ),
                            'visible' => 0
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
    protected function getGrid()
    {
        $dataProvider = 'product_form.product_form_data_source';
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'additionalClasses' => 'admin__field-wide',
                        'componentType' => DynamicRows::NAME,
                        'label' => null,
                        'columnsHeader' => true,
                        'template' => 'ui/dynamic-rows/templates/grid',
                        'component' => 'Magento_Ui/js/dynamic-rows/dynamic-rows-grid',
                        'addButton' => false,
                        'recordTemplate' => 'record',
                        'dataScope' => '',
                        'deleteButtonLabel' => __('Remove'),
                        'dndConfig' => [
                            'enabled' => false,
                        ],
                        'sortOrder' => 2,
                    ],
                ],
            ],
            'children' => $this->getRows(),
        ];
    }

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
                    'id' => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'formElement' => Input::NAME,
                                    'componentType' => Field::NAME,
                                    'dataType' => Text::NAME,
                                    'elementTmpl' => 'ui/dynamic-rows/cells/text',
                                    'component' => 'Magento_Ui/js/form/element/text',
                                    'label' => __('ID'),
                                    'dataScope' => 'id',
                                    'visible' => 0,
                                    'require' => 0,
                                ],
                            ],
                        ],
                    ],
                    'is_first_publish' => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'formElement' => Input::NAME,
                                    'componentType' => Field::NAME,
                                    'dataType' => Text::NAME,
                                    'elementTmpl' => 'ui/dynamic-rows/cells/text',
                                    'component' => 'Magento_Ui/js/form/element/text',
                                    'label' => __('Is First Publish'),
                                    'dataScope' => 'is_first_publish',
                                    'visible' => 0,
                                    'require' => 0,
                                ],
                            ],
                        ],
                    ],
                    'is_default_country' => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'formElement' => Input::NAME,
                                    'componentType' => Field::NAME,
                                    'dataType' => Text::NAME,
                                    'elementTmpl' => 'ui/dynamic-rows/cells/text',
                                    'component' => 'Magento_Ui/js/form/element/text',
                                    'label' => __('Is First Publish'),
                                    'dataScope' => 'is_default_country',
                                    'visible' => 0,
                                    'require' => 0,
                                ],
                            ],
                        ],
                    ],
                    'website_id' => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'formElement' => Input::NAME,
                                    'componentType' => Field::NAME,
                                    'dataType' => Text::NAME,
                                    'elementTmpl' => 'ui/dynamic-rows/cells/text',
                                    'component' => 'Magento_Ui/js/form/element/text',
                                    'label' => __('Website ID'),
                                    'dataScope' => 'website_id',
                                    'visible' => 0,
                                    'require' => 0,
                                ],
                            ],
                        ],
                    ],
                    'store_id' => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'formElement' => Input::NAME,
                                    'componentType' => Field::NAME,
                                    'dataType' => Text::NAME,
                                    'elementTmpl' => 'ui/dynamic-rows/cells/text',
                                    'component' => 'Magento_Ui/js/form/element/text',
                                    'label' => __('Store ID'),
                                    'dataScope' => 'store_id',
                                    'visible' => 0,
                                    'require' => 0,
                                ],
                            ],
                        ],
                    ],
                    'country' => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    // 'formElement' => Input::NAME,
                                    // 'componentType' => Field::NAME,
                                    // 'dataType' => Text::NAME,
                                    // 'elementTmpl' => 'ui/dynamic-rows/cells/text',
                                    // 'component' => 'Magento_Ui/js/form/element/text',
                                    'label' => __('Country'),
                                    // 'dataScope' => 'country',
                                    // 'require' => 0,
                                    'componentType' => 'text',
                                    'component' => 'TCGCollective_MarketPlace/js/form/element/country',
                                    'template' => 'TCGCollective_MarketPlace/components/country',
                                    'dataScope' => 'country'
                                ],
                            ],
                        ],
                    ],
                    'is_published' => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    // 'formElement' => Input::NAME,
                                    // 'componentType' => Field::NAME,
                                    // 'dataType' => Text::NAME,
                                    // 'elementTmpl' => 'ui/dynamic-rows/cells/text',
                                    // 'component' => 'Magento_Ui/js/form/element/text',
                                    'label' => __('Status'),
                                    // 'dataScope' => 'is_published',
                                    // 'require' => 0,
                                    'componentType' => 'text',
                                    'component' => 'TCGCollective_MarketPlace/js/form/element/publish-status',
                                    'template' => 'TCGCollective_MarketPlace/components/publish-status',
                                    'dataScope' => 'is_published'
                                ],
                            ],
                        ],
                    ],
                    'publish_button' => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'componentType' => 'text',
                                    'component' => 'TCGCollective_MarketPlace/js/form/element/action-publish',
                                    'template' => 'TCGCollective_MarketPlace/components/action-publish',
                                    'label' => '',
                                    'dataScope' => 'publish_button'
                                ],
                            ],
                        ],
                    ],
                    'edit_button' => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'componentType' => 'text',
                                    'component' => 'TCGCollective_MarketPlace/js/form/element/action-edit',
                                    'template' => 'TCGCollective_MarketPlace/components/action-edit',
                                    'label' => '',
                                    'dataScope' => 'edit_button'
                                ],
                            ],
                        ],
                    ],
                    'product_data' => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'formElement' => Input::NAME,
                                    'componentType' => Field::NAME,
                                    'dataType' => Text::NAME,
                                    'elementTmpl' => 'ui/dynamic-rows/cells/text',
                                    'component' => 'Magento_Ui/js/form/element/text',
                                    'label' => __('Product Data'),
                                    'dataScope' => 'product_data',
                                    'visible' => 0,
                                    'require' => 0,
                                ],
                            ],
                        ],
                    ],
                    'init_product_data' => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'formElement' => Input::NAME,
                                    'componentType' => Field::NAME,
                                    'dataType' => Text::NAME,
                                    'elementTmpl' => 'ui/dynamic-rows/cells/text',
                                    'component' => 'Magento_Ui/js/form/element/text',
                                    'label' => __('Init Product Data'),
                                    'dataScope' => 'init_product_data',
                                    'visible' => 0,
                                    'require' => 0,
                                ],
                            ],
                        ],
                    ],
                ]
            ],
        ];
    }
}
