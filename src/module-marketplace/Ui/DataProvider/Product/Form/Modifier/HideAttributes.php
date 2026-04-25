<?php

namespace TCGCollective\MarketPlace\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Customer\Model\Customer\Source\GroupSourceInterface;
use Magento\Directory\Helper\Data;
use Magento\Framework\App\ObjectManager;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Api\GroupManagementInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Module\Manager as ModuleManager;
use Magento\Ui\Component\Container;
use Magento\Ui\Component\Form\Element\DataType\Number;
use Magento\Ui\Component\Form\Element\DataType\Price;
use Magento\Ui\Component\Form\Element\DataType\Text;
use Magento\Ui\Component\Form\Element\Input;
use Magento\Ui\Component\Form\Element\Select;
use Magento\Ui\Component\Form\Field;
use Magento\Ui\Component\Modal;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\CurrencySymbolProvider;
use Magento\Framework\UrlInterface;

class HideAttributes extends \Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier
{
    /**
     * @var LocatorInterface
     * @since 101.0.0
     */
    protected $locator;

    /**
     * @var ModuleManager
     * @since 101.0.0
     */
    protected $moduleManager;

    /**
     * @var GroupManagementInterface
     * @since 101.0.0
     */
    protected $groupManagement;

    /**
     * @var SearchCriteriaBuilder
     * @since 101.0.0
     */
    protected $searchCriteriaBuilder;

    /**
     * @var GroupRepositoryInterface
     * @since 101.0.0
     */
    protected $groupRepository;

    /**
     * @var Data
     * @since 101.0.0
     */
    protected $directoryHelper;

    /**
     * @var StoreManagerInterface
     * @since 101.0.0
     */
    protected $storeManager;

    /**
     * @var ArrayManager
     * @since 101.0.0
     */
    protected $arrayManager;

    /**
     * @var string
     * @since 101.0.0
     */
    protected $scopeName;

    /**
     * @var array
     * @since 101.0.0
     */
    protected $meta = [];

    /**
     * @var GroupSourceInterface
     */
    private $customerGroupSource;

    /**
     * @var CurrencySymbolProvider
     */
    private $currencySymbolProvider;

    /**
     * @var \Lof\MarketPlace\Helper\Data
     */
    protected $helper;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;
    protected $platformDbHelper;

    /**
     * @param LocatorInterface $locator
     * @param StoreManagerInterface $storeManager
     * @param GroupRepositoryInterface $groupRepository
     * @param GroupManagementInterface $groupManagement
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param ModuleManager $moduleManager
     * @param Data $directoryHelper
     * @param ArrayManager $arrayManager
     * @param \Lof\MarketPlace\Helper\Data $helper
     * @param UrlInterface $urlBuilder
     * @param string $scopeName
     * @param GroupSourceInterface|null $customerGroupSource
     * @param CurrencySymbolProvider|null $currencySymbolProvider
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \TCGCollective\PlatformDatabase\Helper\Data $platformDbHelper,
        LocatorInterface $locator,
        StoreManagerInterface $storeManager,
        GroupRepositoryInterface $groupRepository,
        GroupManagementInterface $groupManagement,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        ModuleManager $moduleManager,
        Data $directoryHelper,
        ArrayManager $arrayManager,
        \Lof\MarketPlace\Helper\Data $helper,
        UrlInterface $urlBuilder,
        $scopeName = '',
        GroupSourceInterface $customerGroupSource = null,
        ?CurrencySymbolProvider $currencySymbolProvider = null
    ) {
        $this->platformDbHelper = $platformDbHelper;
        $this->locator = $locator;
        $this->storeManager = $storeManager;
        $this->groupRepository = $groupRepository;
        $this->groupManagement = $groupManagement;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->moduleManager = $moduleManager;
        $this->directoryHelper = $directoryHelper;
        $this->arrayManager = $arrayManager;
        $this->scopeName = $scopeName;
        $this->helper = $helper;
        $this->urlBuilder = $urlBuilder;
        $this->customerGroupSource = $customerGroupSource
            ?: ObjectManager::getInstance()->get(GroupSourceInterface::class);
        $this->currencySymbolProvider = $currencySymbolProvider
            ?: ObjectManager::getInstance()->get(CurrencySymbolProvider::class);
    }

    /**
     * @inheritdoc
     *
     * @since 101.0.0
     */
    public function modifyMeta(array $meta)
    {
        $objectManager =  \Magento\Framework\App\ObjectManager::getInstance();
        $this->request = $objectManager->get('\Magento\Framework\App\RequestInterface');
        $isPublish = $this->request->getParam('action') == "publish" ? true : false;
        $isDebug = $this->request->getParam('debug') ? true : false;
        // $isDebug = false;

        // dd($isDebug);

        $fields = ['ship_from','publish_status','cod_available','container_price','custom_options','websites','downloadable','status','visibility','country_of_manufacture','sale','auction','tax_class_id','design','schedule-design-update','gift-options'];
        if (!$isDebug) {
            array_push($fields, 'attribute_set_id');
            array_push($fields, 'sku');
        }
        // $isPublish = false;
        
        $meta = $this->hideAttributes($meta, $fields, $isPublish);
        $meta = $this->hideFieldset($meta, $isPublish, $isDebug);

        if ($this->locator->getProduct()->getTypeId() == "configurable") {
            $meta = $this->hideAttributesForConfigurable($meta, $isPublish, $isDebug);
        }

        $meta = $this->hideCategoryDetailsAttributesOnProductCreationFromDb($meta, $isPublish, $isDebug);

        $currentProduct = $this->locator->getProduct();

        if (!$currentProduct->getId()
             || !$currentProduct->getData('pre_order_payment_type')
             || $currentProduct->getData('pre_order_payment_type') != 'deposit'
        ) {
            $path = $this->arrayManager->findPath('pre_order_deposit', $meta, null, 'children');
            if ($path) {
                $meta = $this->arrayManager->merge(
                    $path . '/arguments/data/config',
                    $meta,
                    [
                        'visible' => 0
                    ]
                );
            }
        }

        if ($currentProduct && $currentProduct->getData('pre_order_payment_type') == 'deposit') {
            $path = $this->arrayManager->findPath('pre_order_deposit', $meta, null, 'children');
            if ($path) {
                $meta = $this->arrayManager->merge(
                    $path . '/arguments/data/config',
                    $meta,
                    [
                        'visible' => 1
                    ]
                );
            }
        }

        // $hideFieldsContains = [];
        // $excludeFields = [];

        // $currentProduct = $this->locator->getProduct();
        // if ($currentProduct->getTypeId() != 'simple') {
        //     $hideFieldsContains = array_merge($hideFieldsContains, ['_sealed_products_', 'set_', 'card_', 'item_','release_date','number_of','release_type']);
        //     $excludeFields = array_merge($hideFieldsContains, ['attribute_set_id','card_game','card_product_type']);
        // }

        // $meta = $this->hideAttributesByContains($meta, $hideFieldsContains, $excludeFields);

        return $meta;
    }

    public function hideCategoryDetailsAttributesOnProductCreationFromDb($meta, $isPublish, $isDebug) 
    {
        $source = $this->request->getParam('source');
        if ($source != "database") {
            return $meta;
        }

        $attributeSetMappingAttributeCodesArr = $this->platformDbHelper->getAttributeSetMappingAttributeCodesArr();
        // dd($attributeSetMappingAttributeCodesArr);
        if (!$attributeSetMappingAttributeCodesArr) {
            return $meta;
        }

        if (!isset($meta['category-details']['children'])) {
            return $meta;
        }
        
        foreach($meta['category-details']['children'] as $containerName => $containerData) 
        {
            $fieldCode = str_replace("container_", "", $containerName);
            if (in_array($fieldCode, $attributeSetMappingAttributeCodesArr)) {
                continue;
            }

            if ($fieldCode == "category_ids") {
                continue;
            }

            // dd($fieldCode);

            $path = $this->arrayManager->findPath($fieldCode, $meta, null, 'children');
            if ($path) {
                $meta = $this->arrayManager->merge(
                    $path . '/arguments/data/config',
                    $meta,
                    [
                        'visible' => false
                    ]
                );
            }
        }

        return $meta;
    }

    public function hideAttributesForConfigurable($meta, $isPublish, $isDebug) 
    {
        // Attributes to show on parent/configurable product form
        $allowedAttributes = ['price','name','supplier_sku','quantity_and_stock_status'];

        if ($isDebug) {
            array_push($allowedAttributes, 'sku', 'attribute_set_id');
        }

        // CONFIGURABLE ONLY | PRODUCT DETAILS FIELDSET
        if (isset($meta['product-details']['children']) && count($meta['product-details']['children'])) {
            foreach($meta['product-details']['children'] as $containerName => $containerData) 
            {
                $fieldCode = str_replace("container_", "", $containerName);
                if (!in_array($fieldCode, $allowedAttributes)) 
                {
                    $path = $this->arrayManager->findPath($fieldCode, $meta, null, 'children');
                    if ($path) {
                        $meta = $this->arrayManager->merge(
                            $path . '/arguments/data/config',
                            $meta,
                            [
                                'visible' => false
                            ]
                        );
                    }

                    $path = $this->arrayManager->findPath($containerName, $meta, null, 'children');
                    if ($path) {
                        $meta = $this->arrayManager->merge(
                            $path . '/arguments/data/config',
                            $meta,
                            [
                                'visible' => false
                            ]
                        );
                    }
                }
            }
        }

        return $meta;
    }

    public function hideFieldset($meta, $isPublish, $isDebug)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->request = $objectManager->get('Magento\Framework\App\RequestInterface');
        
        $source = $this->request->getParam('source');
        $productType = $this->request->getParam('type');

        
        $path = $this->arrayManager->findPath('search-engine-optimization', $meta, null, 'children');
        if ($path) {
            $meta = $this->arrayManager->merge(
                $path . '/arguments/data/config',
                $meta,
                [
                    'visible' => false
                ]
            );
        }
        
        $product = $this->locator->getProduct();
        if ($product->getId() && $isPublish) {

            $path = $this->arrayManager->findPath('pre-orders', $meta, null, 'children');
            if ($path) {
                $meta = $this->arrayManager->merge(
                    $path . '/arguments/data/config',
                    $meta,
                    [
                        'visible' => false
                    ]
                );
            }

            // Hide Product Details Fieldset
            $path = $this->arrayManager->findPath('category-details', $meta, null, 'children');
            if ($path) {
                $meta = $this->arrayManager->merge(
                    $path . '/arguments/data/config',
                    $meta,
                    [
                        'visible' => false
                    ]
                );
            }

        //     $path = $this->arrayManager->findPath('content', $meta, null, 'children');
        //     if ($path) {
        //         $meta = $this->arrayManager->merge(
        //             $path . '/arguments/data/config',
        //             $meta,
        //             [
        //                 'visible' => false
        //             ]
        //         );
        //     }

        //     $path = $this->arrayManager->findPath('product-details', $meta, null, 'children');
        //     if ($path) {
        //         $meta = $this->arrayManager->merge(
        //             $path . '/arguments/data/config',
        //             $meta,
        //             [
        //                 'visible' => false
        //             ]
        //         );
        //     }

            // Hide SEO Fieldset
            $path = $this->arrayManager->findPath('search-engine-optimization', $meta, null, 'children');
            if ($path) {
                $meta = $this->arrayManager->merge(
                    $path . '/arguments/data/config',
                    $meta,
                    [
                        'visible' => false
                    ]
                );
            }
// dd($meta['product-details']['children']);
            foreach($meta['product-details']['children'] as $containerName => $containerData) 
            {
                $fieldCode = str_replace("container_", "", $containerName);
                if (!in_array($fieldCode, ['price','name','quantity_and_stock_status','quantity_and_stock_status_qty'])) 
                {
                    $path = $this->arrayManager->findPath($fieldCode, $meta, null, 'children');
                    if ($path) {
                        $meta = $this->arrayManager->merge(
                            $path . '/arguments/data/config',
                            $meta,
                            [
                                'visible' => false
                            ]
                        );
                    }

                    $path = $this->arrayManager->findPath($containerName, $meta, null, 'children');
                    if ($path) {
                        $meta = $this->arrayManager->merge(
                            $path . '/arguments/data/config',
                            $meta,
                            [
                                'visible' => false
                            ]
                        );
                    }
                }
            }
        }

        // dd($meta);

        // if ($source && $source == "database" && !$product->getId() || $productType == "configurable" && !$product->getId()) {
        if (!$product->getId()) {
            // Hide Product Details Fieldset
            $path = $this->arrayManager->findPath('product-details', $meta, null, 'children');
            if ($path) {
                $meta = $this->arrayManager->merge(
                    $path . '/arguments/data/config',
                    $meta,
                    [
                        'visible' => $isDebug ? true : false
                    ]
                );
            }

            // Hide Content Fieldset
            $path = $this->arrayManager->findPath('content', $meta, null, 'children');
            if ($path) {
                $meta = $this->arrayManager->merge(
                    $path . '/arguments/data/config',
                    $meta,
                    [
                        'visible' => false
                    ]
                );
            }

            // Hide SEO Fieldset
            $path = $this->arrayManager->findPath('search-engine-optimization', $meta, null, 'children');
            if ($path) {
                $meta = $this->arrayManager->merge(
                    $path . '/arguments/data/config',
                    $meta,
                    [
                        'visible' => false
                    ]
                );
            }
        }

        // dd($meta);

        return $meta;
    }

    public function hideAttributesByContains($meta, $fields, $excludeFields)
    {
        if ($fields) {
            // dd($meta['category-details']['children']);
            // Get all attributes on product details group to change to ui select
            foreach($meta['category-details']['children'] as $containerName => $containerData) 
            {
                $fieldCode = str_replace("container_", "", $containerName);
                if (in_array($fieldCode, $excludeFields)) {
                    continue;
                }

                if ($this->contains($fieldCode, $fields)) {
                    $path = $this->arrayManager->findPath($fieldCode, $meta, null, 'children');
                    if ($path) {
                        $meta = $this->arrayManager->merge(
                            $path . '/arguments/data/config',
                            $meta,
                            [
                                'visible' => false
                            ]
                        );
                    } 
                }
            }

            foreach($meta['product-details']['children'] as $containerName => $containerData) 
            {
                $fieldCode = str_replace("container_", "", $containerName);
                if (in_array($fieldCode, $excludeFields)) 
                {
                    continue;
                }

                if ($this->contains($fieldCode, $fields)) 
                {
                    $path = $this->arrayManager->findPath($fieldCode, $meta, null, 'children');
                    if ($path) {
                        $meta = $this->arrayManager->merge(
                            $path . '/arguments/data/config',
                            $meta,
                            [
                                'visible' => false
                            ]
                        );
                    } 
                }
            }
        }

        return $meta;
    }

    public function contains($str, array $arr)
    {
        foreach($arr as $a) {
            if (stripos($str,$a) !== false) return true;
        }
        return false;
    }

    /**
     * @inheritdoc
     *
     * @since 101.0.0
     */
    public function modifyData(array $data)
    {
        return $data;
    }

    private function hideAttributes($meta, $fields, $isPublish)
    {
        $fromField = 'news_from_date';
        $fromFieldPath = $this->arrayManager->findPath($fromField, $meta, null, 'children');
        $fromContainerPath = $this->arrayManager->slicePath($fromFieldPath, 0, -2);
        $meta = $this->arrayManager->remove($fromContainerPath, $meta);

        // $path = $this->arrayManager->findPath('search-engine-optimization', $meta, null);
        // // dd($path);
        // if ($path) {
        //     $meta = $this->arrayManager->merge(
        //         $path . '/arguments/data/config',
        //         $meta,
        //         [
        //             'opened' => true
        //         ]
        //     );
        // }

        $path = $this->arrayManager->findPath('related', $meta, null);
        // dd($path);
        if ($path) {
            $meta = $this->arrayManager->merge(
                $path . '/arguments/data/config',
                $meta,
                [
                    'visible' => false
                ]
            );
        }

        $path = $this->arrayManager->findPath('product.form.configurable.matrix.content', $meta, null);
        // dd($path);
        if ($path) {
            $meta = $this->arrayManager->merge(
                $path . '/arguments/data/config',
                $meta,
                [
                    'visible' => false
                ]
            );
        }

        $path = $this->arrayManager->findPath('content', $meta, null, 'children');
        // dd($path);
        if ($path) {
            $meta = $this->arrayManager->merge(
                $path . '/arguments/data/config',
                $meta,
                [
                    'opened' => true
                ]
            );
        }

        // dd($meta);
        if ($fields) {
            // dd($fields);
            foreach($fields as $fieldCode) {
                if ($fieldCode == "container_price" && $isPublish) {
                    continue;
                }
                if ($fieldCode == "container_quantity_and_stock_status" || $fieldCode == "container_quantity_and_stock_status_qty") {
                    continue;
                }
                $path = $this->arrayManager->findPath($fieldCode, $meta, null, 'children');
                if ($path) {
                    $meta = $this->arrayManager->merge(
                        $path . '/arguments/data/config',
                        $meta,
                        [
                            'visible' => 0
                        ]
                    );
                }
            }
        }

        return $meta;
    }
}
