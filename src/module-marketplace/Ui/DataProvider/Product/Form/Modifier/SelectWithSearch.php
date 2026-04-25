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

class SelectWithSearch extends \Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier
{
    protected $request;


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
        $meta = $this->modifySelectWithSearchByContains($meta);
        return $meta;
    }

    private function modifySelectWithSearchByContains($meta)
    {
        $excludeAttributes = [
            'mapping_group_name',
            'mapping_priority',
            'quantity_and_stock_status'
        ];

        $isVisible = true;
        $currentProduct = $this->locator->getProduct();
        if ($currentProduct && $currentProduct->getId()) {
            $isVisible = true;
        }

        // Get all attributes on product details group to change to ui select
        if (isset($meta['product-details']) && $meta['product-details']) {
            foreach($meta['product-details']['children'] as $containerName => $containerData) 
            {
                // Get attribute_code without container
                $fieldCode = str_replace("container_", "", $containerName);

                // Modify attributes
                if (in_array($fieldCode, $excludeAttributes)) {
                    continue;
                }

                if ($fieldCode == "attribute_set_id") {
                    continue;
                }

                $path = $this->arrayManager->findPath($fieldCode, $meta, null, 'children');
                if ($path) {
                    $containerPath = $this->arrayManager->findPath(static::CONTAINER_PREFIX . $fieldCode, $meta, null, 'children');
                    $data = $this->arrayManager->get(
                        $path,
                        $meta
                    );

                    $dataType = $data['arguments']['data']['config']['dataType'] ?? null;

                    if ($dataType == "select" || $dataType == "multiselect") {
                        $meta = $this->arrayManager->merge(
                            $path . '/arguments/data/config',
                            $meta,
                            [
                                'elementTmpl' => 'ui/grid/filters/elements/ui-select',
                                'component' => 'Magento_Catalog/js/components/attribute-set-select',
                                'filterOptions' => true,
                                'disableLabel' => true,
                                'visible' => $isVisible,
                                'multiple' => $dataType == "multiselect" ? true : false,
                                'addNewBtn' => false,
                                'showPath' => false
                            ]
                        );
                    } else {
                        $meta = $this->arrayManager->merge(
                            $path . '/arguments/data/config',
                            $meta,
                            [
                                'visible' => $isVisible
                            ]
                        );
                    }
                }
            }
        }

        // Get all attributes on category details group to change to ui select
        if (isset($meta['category-details']) && $meta['category-details']) {
            foreach($meta['category-details']['children'] as $containerName => $containerData) 
            {
                $fieldCode = str_replace("container_", "", $containerName);
                if (in_array($fieldCode, $excludeAttributes)) {
                    continue;
                }
                
                $addNewBtn = false;
                if ($fieldCode == "card_game") {
                    $addNewBtn = true;
                }
                $path = $this->arrayManager->findPath($fieldCode, $meta, null, 'children');
                if ($path) {
                    $meta = $this->arrayManager->merge(
                        $path . '/arguments/data/config',
                        $meta,
                        [
                            'elementTmpl' => 'ui/grid/filters/elements/ui-select',
                            'component' => 'Magento_Catalog/js/components/attribute-set-select',
                            'filterOptions' => true,
                            'disableLabel' => true,
                            'required' => true,
                            'multiple' => false,
                            'addNewBtn' => $addNewBtn,
                            'showPath' => false
                        ]
                    );
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

    private function modifySelectWithSearch($meta, $fields)
    {
        $objectManager =  \Magento\Framework\App\ObjectManager::getInstance();
        $this->request = $objectManager->get('\Magento\Framework\App\RequestInterface');
        if ($fields) {
            foreach($fields as $fieldCode) {
                $path = $this->arrayManager->findPath($fieldCode, $meta, null, 'children');
                if ($path) {
                    $isMultiple = false;
                    $meta = $this->arrayManager->merge(
                        $path . '/arguments/data/config',
                        $meta,
                        [
                            'elementTmpl' => 'ui/grid/filters/elements/ui-select',
                            'component' => 'Magento_Catalog/js/components/attribute-set-select',
                            'filterOptions' => true,
                            'disableLabel' => true,
                            'multiple' => $isMultiple,
                            'addNewBtn' => false,
                            'showPath' => false
                        ]
                    );
                }
            }
        }

        return $meta;
    }
}
