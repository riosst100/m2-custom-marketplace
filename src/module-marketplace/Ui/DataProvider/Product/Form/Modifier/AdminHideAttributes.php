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

class AdminHideAttributes extends \Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier
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
        // $fields = ['container_price','custom_options','websites','downloadable','status','visibility','country_of_manufacture','sale','auction','tax_class_id','design','schedule-design-update','gift-options'];
        // array_push($fields, 'attribute_set_id');
        // array_push($fields, 'sku');
        $fields = ['sku'];
        $meta = $this->hideAttributes($meta, $fields);

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

    private function hideAttributes($meta, $fields)
    {
        $fromField = 'news_from_date';
        $fromFieldPath = $this->arrayManager->findPath($fromField, $meta, null, 'children');
        $fromContainerPath = $this->arrayManager->slicePath($fromFieldPath, 0, -2);
        $meta = $this->arrayManager->remove($fromContainerPath, $meta);

        if ($fields) {
            foreach($fields as $fieldCode) {
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
