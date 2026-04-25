<?php

namespace TCGCollective\MarketPlace\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Api\ProductAttributeGroupRepositoryInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Model\Attribute\ScopeOverriddenValue;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type as ProductType;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute as EavAttribute;
use Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory as EavAttributeFactory;
use Magento\Catalog\Ui\DataProvider\CatalogEavValidationRules;
use Magento\Eav\Api\Data\AttributeGroupInterface;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute\Source\SpecificSourceInterface;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory as GroupCollectionFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Filter\Translit;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\Component\Form\Element\Wysiwyg as WysiwygElement;
use Magento\Ui\Component\Form\Field;
use Magento\Ui\Component\Form\Fieldset;
use Magento\Ui\DataProvider\Mapper\FormElement as FormElementMapper;
use Magento\Ui\DataProvider\Mapper\MetaProperties as MetaPropertiesMapper;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\Eav\CompositeConfigProcessor;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory as AttributeCollectionFactory;

class Eav extends \Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\Eav
{
    public const SORT_ORDER_MULTIPLIER = 10;

    /**
     * @var LocatorInterface
     * @since 101.0.0
     */
    protected $locator;

    /**
     * @var Config
     * @since 101.0.0
     */
    protected $eavConfig;

    /**
     * @var CatalogEavValidationRules
     * @since 101.0.0
     */
    protected $catalogEavValidationRules;

    /**
     * @var RequestInterface
     * @since 101.0.0
     */
    protected $request;

    /**
     * @var GroupCollectionFactory
     * @since 101.0.0
     */
    protected $groupCollectionFactory;

    /**
     * @var StoreManagerInterface
     * @since 101.0.0
     */
    protected $storeManager;

    /**
     * @var FormElementMapper
     * @since 101.0.0
     */
    protected $formElementMapper;

    /**
     * @var MetaPropertiesMapper
     * @since 101.0.0
     */
    protected $metaPropertiesMapper;

    /**
     * @var ProductAttributeGroupRepositoryInterface
     * @since 101.0.0
     */
    protected $attributeGroupRepository;

    /**
     * @var SearchCriteriaBuilder
     * @since 101.0.0
     */
    protected $searchCriteriaBuilder;

    /**
     * @var ProductAttributeRepositoryInterface
     * @since 101.0.0
     */
    protected $attributeRepository;

    /**
     * @var SortOrderBuilder
     * @since 101.0.0
     */
    protected $sortOrderBuilder;

    /**
     * @var EavAttributeFactory
     * @since 101.0.0
     */
    protected $eavAttributeFactory;

    /**
     * @var Translit
     * @since 101.0.0
     */
    protected $translitFilter;

    /**
     * @var ArrayManager
     * @since 101.0.0
     */
    protected $arrayManager;

    /**
     * @var ScopeOverriddenValue
     */
    private $scopeOverriddenValue;

    /**
     * @var array
     */
    private $attributesToDisable;

    /**
     * @var array
     * @since 101.0.0
     */
    protected $attributesToEliminate;

    /**
     * @var DataPersistorInterface
     * @since 101.0.0
     */
    protected $dataPersistor;

    /**
     * @var EavAttribute[]
     */
    private $attributes = [];

    /**
     * @var AttributeGroupInterface[]
     */
    private $attributeGroups = [];

    /**
     * @var array
     */
    private $canDisplayUseDefault = [];

    /**
     * @var array
     */
    private $bannedInputTypes = ['media_image'];

    /**
     * @var array
     */
    private $prevSetAttributes;

    /**
     * @var array
     */
    private $attributesCache = [];

    /**
     * @var AttributeCollectionFactory
     */
    private $attributeCollectionFactory;

    /**
     * @var CompositeConfigProcessor
     */
    private $wysiwygConfigProcessor;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var AuthorizationInterface
     */
    private $auth;

    /**
     * @var \TCGCollective\ProductAttributesLink\Model\ResourceModel\ProductAttributesLink\CollectionFactory
     */
    protected $prdAttrLinkCollFactory;

    /**
     * @var \TCGCollective\ProductAttributesLink\Model\ResourceModel\CardGamesMapping\CollectionFactory
     */
    protected $cardGamesMappingCollFactory;

    /**
     * @var \TCGCollective\ProductAttributesLink\Helper\Data
     */
    protected $mappingHelper;

    protected $cardGameSets;
    protected $legoSets;
    protected $legoSingles;
    protected $sportCardsSets;
    protected $sportCardsSetsCard;
    protected $nonSportCardsSets;
    protected $nonSportCardsSetsCard;
    protected $miniatureGameSets;
    protected $miniatureGameSingles;
    protected $livingCardGameSets;

    /**
     * Eav constructor.
     * @param LocatorInterface $locator
     * @param CatalogEavValidationRules $catalogEavValidationRules
     * @param Config $eavConfig
     * @param RequestInterface $request
     * @param GroupCollectionFactory $groupCollectionFactory
     * @param StoreManagerInterface $storeManager
     * @param FormElementMapper $formElementMapper
     * @param MetaPropertiesMapper $metaPropertiesMapper
     * @param ProductAttributeGroupRepositoryInterface $attributeGroupRepository
     * @param ProductAttributeRepositoryInterface $attributeRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param SortOrderBuilder $sortOrderBuilder
     * @param EavAttributeFactory $eavAttributeFactory
     * @param Translit $translitFilter
     * @param ArrayManager $arrayManager
     * @param ScopeOverriddenValue $scopeOverriddenValue
     * @param DataPersistorInterface $dataPersistor
     * @param \TCGCollective\ProductAttributesLink\Model\ResourceModel\ProductAttributesLink\CollectionFactory $prdAttrLinkCollFactory
     * @param \TCGCollective\ProductAttributesLink\Model\ResourceModel\CardGamesMapping\CollectionFactory $cardGamesMappingCollFactory
     * @param \TCGCollective\ProductAttributesLink\Helper\Data $mappingHelper
     * @param array $attributesToDisable
     * @param array $attributesToEliminate
     * @param CompositeConfigProcessor|null $wysiwygConfigProcessor
     * @param ScopeConfigInterface|null $scopeConfig
     * @param AttributeCollectionFactory $attributeCollectionFactory
     * @param AuthorizationInterface|null $auth
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        LocatorInterface $locator,
        CatalogEavValidationRules $catalogEavValidationRules,
        Config $eavConfig,
        RequestInterface $request,
        GroupCollectionFactory $groupCollectionFactory,
        StoreManagerInterface $storeManager,
        FormElementMapper $formElementMapper,
        MetaPropertiesMapper $metaPropertiesMapper,
        ProductAttributeGroupRepositoryInterface $attributeGroupRepository,
        ProductAttributeRepositoryInterface $attributeRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SortOrderBuilder $sortOrderBuilder,
        EavAttributeFactory $eavAttributeFactory,
        Translit $translitFilter,
        ArrayManager $arrayManager,
        ScopeOverriddenValue $scopeOverriddenValue,
        DataPersistorInterface $dataPersistor,
        \TCGCollective\ProductAttributesLink\Model\ResourceModel\ProductAttributesLink\CollectionFactory $prdAttrLinkCollFactory,
        \TCGCollective\ProductAttributesLink\Model\ResourceModel\CardGamesMapping\CollectionFactory $cardGamesMappingCollFactory,
        \TCGCollective\ProductAttributesLink\Helper\Data $mappingHelper,
        \TCGCollective\ProductAttributesLink\Model\CardGamesSetsFactory $cardGameSets,
        \TCGCollective\ProductAttributesLink\Model\CardGamesSetsCardFactory $cardGameSetsCard,
        \TCGCollective\ProductAttributesLink\Model\LegoSetsFactory $legoSets,
        \TCGCollective\ProductAttributesLink\Model\LivingCardGameSetsFactory $livingCardGameSets,
        \TCGCollective\ProductAttributesLink\Model\LegoSinglesFactory $legoSingles,
        \TCGCollective\ProductAttributesLink\Model\SportCardsSetsFactory $sportCardsSets,
        \TCGCollective\ProductAttributesLink\Model\SportCardsSetsCardFactory $sportCardsSetsCard,
        \TCGCollective\ProductAttributesLink\Model\NonSportCardsSetsFactory $nonSportCardsSets,
        \TCGCollective\ProductAttributesLink\Model\NonSportCardsSetsCardFactory $nonSportCardsSetsCard,
        \TCGCollective\ProductAttributesLink\Model\MiniatureGameSetsFactory $miniatureGameSets,
        \TCGCollective\ProductAttributesLink\Model\MiniatureGameSinglesFactory $miniatureGameSingles,
        $attributesToDisable = [],
        $attributesToEliminate = [],
        CompositeConfigProcessor $wysiwygConfigProcessor = null,
        ScopeConfigInterface $scopeConfig = null,
        AttributeCollectionFactory $attributeCollectionFactory = null,
        ?AuthorizationInterface $auth = null
    ) {
        $this->locator = $locator;
        $this->catalogEavValidationRules = $catalogEavValidationRules;
        $this->eavConfig = $eavConfig;
        $this->request = $request;
        $this->groupCollectionFactory = $groupCollectionFactory;
        $this->storeManager = $storeManager;
        $this->formElementMapper = $formElementMapper;
        $this->metaPropertiesMapper = $metaPropertiesMapper;
        $this->attributeGroupRepository = $attributeGroupRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->attributeRepository = $attributeRepository;
        $this->sortOrderBuilder = $sortOrderBuilder;
        $this->eavAttributeFactory = $eavAttributeFactory;
        $this->translitFilter = $translitFilter;
        $this->arrayManager = $arrayManager;
        $this->scopeOverriddenValue = $scopeOverriddenValue;
        $this->dataPersistor = $dataPersistor;
        $this->prdAttrLinkCollFactory = $prdAttrLinkCollFactory;
        $this->cardGamesMappingCollFactory = $cardGamesMappingCollFactory;
        $this->attributesToDisable = $attributesToDisable;
        $this->attributesToEliminate = $attributesToEliminate;
        $this->wysiwygConfigProcessor = $wysiwygConfigProcessor
            ?: ObjectManager::getInstance()->get(CompositeConfigProcessor::class);
        $this->scopeConfig = $scopeConfig ?: ObjectManager::getInstance()->get(ScopeConfigInterface::class);
        $this->attributeCollectionFactory = $attributeCollectionFactory
            ?: ObjectManager::getInstance()->get(AttributeCollectionFactory::class);
        $this->auth = $auth ?? ObjectManager::getInstance()->get(AuthorizationInterface::class);
        $this->mappingHelper = $mappingHelper;
        $this->cardGameSets = $cardGameSets;
        $this->cardGameSetsCard = $cardGameSetsCard;
        $this->livingCardGameSets = $livingCardGameSets;
        $this->legoSets = $legoSets;
        $this->legoSingles = $legoSingles;
        $this->sportCardsSets = $sportCardsSets;
        $this->sportCardsSetsCard = $sportCardsSetsCard;
        $this->nonSportCardsSets = $nonSportCardsSets;
        $this->nonSportCardsSetsCard = $nonSportCardsSetsCard;
        $this->miniatureGameSets = $miniatureGameSets;
        $this->miniatureGameSingles = $miniatureGameSingles;

        parent::__construct(
            $locator,
            $catalogEavValidationRules,
            $eavConfig,
            $request,
            $groupCollectionFactory,
            $storeManager,
            $formElementMapper,
            $metaPropertiesMapper,
            $attributeGroupRepository,
            $attributeRepository,
            $searchCriteriaBuilder,
            $sortOrderBuilder,
            $eavAttributeFactory,
            $translitFilter,
            $arrayManager,
            $scopeOverriddenValue,
            $dataPersistor,
            $attributesToDisable,
            $attributesToEliminate,
            $wysiwygConfigProcessor,
            $scopeConfig,
            $attributeCollectionFactory,
            $auth
        );
    }

    /**
     * @inheritdoc
     * @since 101.0.0
     */
    public function modifyMeta(array $meta)
    {
        $sortOrder = 0;

        foreach ($this->getGroups() as $groupCode => $group) {
            $attributes = !empty($this->getAttributes()[$groupCode]) ? $this->getAttributes()[$groupCode] : [];

            if ($attributes) {
                $meta[$groupCode]['children'] = $this->getAttributesMeta($attributes, $groupCode);
                $meta[$groupCode]['arguments']['data']['config']['componentType'] = Fieldset::NAME;
                $meta[$groupCode]['arguments']['data']['config']['label'] = __($group->getAttributeGroupName());
                $meta[$groupCode]['arguments']['data']['config']['collapsible'] = true;
                $meta[$groupCode]['arguments']['data']['config']['dataScope'] = self::DATA_SCOPE_PRODUCT;
                $meta[$groupCode]['arguments']['data']['config']['sortOrder'] = $sortOrder * self::SORT_ORDER_MULTIPLIER;
            }

            if ($groupCode == "image-management") {
                $meta[$groupCode]['arguments']['data']['config']['label'] = "Images";
            }

            $sortOrder++;
        }

        return $meta;
    }

    /**
     * Get attributes meta
     *
     * @param ProductAttributeInterface[] $attributes
     * @param string $groupCode
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getAttributesMeta(array $attributes, $groupCode)
    {
        $meta = [];

        foreach ($attributes as $sortOrder => $attribute) {
            if (in_array($attribute->getFrontendInput(), $this->bannedInputTypes)) {
                continue;
            }

            if (in_array($attribute->getAttributeCode(), $this->attributesToEliminate)) {
                continue;
            }

            if (!($attributeContainer = $this->setupAttributeContainerMeta($attribute))) {
                continue;
            }

            $attributeContainer = $this->addContainerChildren($attributeContainer, $attribute, $groupCode, $sortOrder);

            $meta[static::CONTAINER_PREFIX . $attribute->getAttributeCode()] = $attributeContainer;
        }

        return $meta;
    }

    /**
     * Add container children
     *
     * @param array $attributeContainer
     * @param ProductAttributeInterface $attribute
     * @param string $groupCode
     * @param int $sortOrder
     * @return array
     * @since 101.0.0
     */
    public function addContainerChildren(
        array $attributeContainer,
        ProductAttributeInterface $attribute,
        $groupCode,
        $sortOrder
    ) {
        foreach ($this->getContainerChildren($attribute, $groupCode, $sortOrder) as $childCode => $child) {
            $attributeContainer['children'][$childCode] = $child;
        }

        $attributeContainer = $this->arrayManager->merge(
            ltrim(static::META_CONFIG_PATH, ArrayManager::DEFAULT_PATH_DELIMITER),
            $attributeContainer,
            [
                'sortOrder' => $sortOrder * self::SORT_ORDER_MULTIPLIER
            ]
        );

        return $attributeContainer;
    }

    /**
     * Retrieve container child fields
     *
     * @param ProductAttributeInterface $attribute
     * @param string $groupCode
     * @param int $sortOrder
     * @return array
     */
    public function getContainerChildren(ProductAttributeInterface $attribute, $groupCode, $sortOrder)
    {
        if (!($child = $this->setupAttributeMeta($attribute, $groupCode, $sortOrder))) {
            return [];
        }

        return [$attribute->getAttributeCode() => $child];
    }

    /**
     * @inheritdoc
     * @since 101.0.0
     */
    public function modifyData(array $data)
    {
        if (!$this->locator->getProduct()->getId() && $this->dataPersistor->get('catalog_product')) {
            return $this->resolvePersistentData($data);
        }

        $productId = $this->locator->getProduct()->getId();

        /** @var string $groupCode */
        foreach (array_keys($this->getGroups()) as $groupCode) {
            /** @var ProductAttributeInterface[] $attributes */
            $attributes = !empty($this->getAttributes()[$groupCode]) ? $this->getAttributes()[$groupCode] : [];

            // if ($groupCode == "product-details") {
            //     dd($attributes);
            // }

            foreach ($attributes as $attribute) {
                // if ($attribute->getAttributeCode() == "mtg_card_foil") {
                // dd($attribute->getData());
                // }
                if (null !== ($attributeValue = $this->setupAttributeData($attribute))) {
                    if ($this->isPriceAttribute($attribute, $attributeValue)) {
                        $attributeValue = $this->formatPrice($attributeValue);
                    }
                    $data[$productId][self::DATA_SOURCE_DEFAULT][$attribute->getAttributeCode()] = $attributeValue;
                }
            }
        }

        return $data;
    }

    /**
     * Obtain if given attribute is a price
     *
     * @param \Magento\Catalog\Api\Data\ProductAttributeInterface $attribute
     * @param string|integer $attributeValue
     * @return bool
     */
    private function isPriceAttribute(ProductAttributeInterface $attribute, $attributeValue)
    {
        return $attribute->getFrontendInput() === 'price'
            && is_scalar($attributeValue)
            && !$this->isBundleSpecialPrice($attribute);
    }

    /**
     * Obtain if current product is bundle and given attribute is special_price
     *
     * @param \Magento\Catalog\Api\Data\ProductAttributeInterface $attribute
     * @return bool
     */
    private function isBundleSpecialPrice(ProductAttributeInterface $attribute)
    {
        return $this->locator->getProduct()->getTypeId() === ProductType::TYPE_BUNDLE
            && $attribute->getAttributeCode() === ProductAttributeInterface::CODE_SPECIAL_PRICE;
    }

    /**
     * Resolve data persistence
     *
     * @param array $data
     * @return array
     */
    private function resolvePersistentData(array $data)
    {
        $persistentData = (array)$this->dataPersistor->get('catalog_product');
        $this->dataPersistor->clear('catalog_product');
        $productId = $this->locator->getProduct()->getId();

        if (empty($data[$productId][self::DATA_SOURCE_DEFAULT])) {
            $data[$productId][self::DATA_SOURCE_DEFAULT] = [];
        }

        $data[$productId] = array_replace_recursive(
            $data[$productId][self::DATA_SOURCE_DEFAULT],
            $persistentData
        );

        return $data;
    }

    /**
     * Get product type
     *
     * @return null|string
     */
    private function getProductType()
    {
        return (string)$this->request->getParam('type', $this->locator->getProduct()->getTypeId());
    }

    /**
     * Return prev set id
     *
     * @return int
     */
    private function getPreviousSetId()
    {
        return (int)$this->request->getParam('prev_set_id', 0);
    }

    /**
     * Retrieve groups
     *
     * @return AttributeGroupInterface[]
     */
    private function getGroups()
    {
        if (!$this->attributeGroups) {
            $searchCriteria = $this->prepareGroupSearchCriteria()->create();
            $attributeGroupSearchResult = $this->attributeGroupRepository->getList($searchCriteria);
            foreach ($attributeGroupSearchResult->getItems() as $group) {
                $this->attributeGroups[$this->calculateGroupCode($group)] = $group;
            }
        }

        return $this->attributeGroups;
    }

    /**
     * Initialize attribute group search criteria with filters.
     *
     * @return SearchCriteriaBuilder
     */
    private function prepareGroupSearchCriteria()
    {
        return $this->searchCriteriaBuilder->addFilter(
            AttributeGroupInterface::ATTRIBUTE_SET_ID,
            $this->getAttributeSetId()
        );
    }

    /**
     * Return current attribute set id
     *
     * @return int|null
     */
    private function getAttributeSetId()
    {
        return $this->locator->getProduct()->getAttributeSetId();
    }

    /**
     * Retrieve attributes
     *
     * @return ProductAttributeInterface[]
     */
    private function getAttributes()
    {
        if (!$this->attributes) {
            $this->attributes = $this->loadAttributesForGroups($this->getGroups());
        }

        return $this->attributes;
    }

    /**
     * Loads attributes for specified groups at once
     *
     * @param AttributeGroupInterface[] $groups
     * @return ProductAttributeInterface[]
     */
    private function loadAttributesForGroups(array $groups)
    {
        $attributes = [];
        $groupIds = [];

        foreach ($groups as $group) {
            $groupIds[$group->getAttributeGroupId()] = $this->calculateGroupCode($group);
            $attributes[$this->calculateGroupCode($group)] = [];
        }

        $collection = $this->attributeCollectionFactory->create();
        $collection->setAttributeGroupFilter(array_keys($groupIds));

        $mapAttributeToGroup = [];

        foreach ($collection->getItems() as $attribute) {
            $mapAttributeToGroup[$attribute->getAttributeId()] = $attribute->getAttributeGroupId();
        }

        $sortOrder = $this->sortOrderBuilder
            ->setField('sort_order')
            ->setAscendingDirection()
            ->create();

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(AttributeGroupInterface::GROUP_ID, array_keys($groupIds), 'in')
            ->addFilter(ProductAttributeInterface::IS_VISIBLE, 1)
            ->addSortOrder($sortOrder)
            ->create();

        $groupAttributes = $this->attributeRepository->getList($searchCriteria)->getItems();

        $productType = $this->getProductType();

        foreach ($groupAttributes as $attribute) {
            $applyTo = $attribute->getApplyTo();
            $isRelated = !$applyTo || in_array($productType, $applyTo);
            if ($isRelated) {
                $attributeGroupId = $mapAttributeToGroup[$attribute->getAttributeId()];
                $attributeGroupCode = $groupIds[$attributeGroupId];
                $attributes[$attributeGroupCode][] = $attribute;
            }
        }

        return $attributes;
    }

    /**
     * Get attribute codes of prev set
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getPreviousSetAttributes()
    {
        if ($this->prevSetAttributes === null) {
            $searchCriteria = $this->searchCriteriaBuilder
                ->addFilter('attribute_set_id', $this->getPreviousSetId())
                ->create();
            $attributes = $this->attributeRepository->getList($searchCriteria)->getItems();
            $this->prevSetAttributes = [];
            foreach ($attributes as $attribute) {
                $this->prevSetAttributes[] = $attribute->getAttributeCode();
            }
        }

        return $this->prevSetAttributes;
    }

    /**
     * Check is product already new or we trying to create one
     *
     * @return bool
     */
    private function isProductExists()
    {
        return (bool) $this->locator->getProduct()->getId();
    }

    public function getCurrentCategoryName() 
    {
        $categoryName = '';

        $objectManager =  \Magento\Framework\App\ObjectManager::getInstance();
        $this->mappingHelper = $objectManager->create(\TCGCollective\ProductAttributesLink\Helper\Data::class);

        $currentProduct = $this->locator->getProduct();
        $params = $this->request->getParams();

        $categoryIds = isset($params['category_ids']) && $params['category_ids'] ? $params['category_ids'] : (
            $currentProduct->getData('category_ids') ?: null
        );
        
        if ($categoryIds) {
            $category = $this->mappingHelper->getCategoryById($categoryIds);
            if ($category) {
                $categoryName = $category->getName();
            }
        }

        return $categoryName;
    }

    public function getCustomTableOptions($attributeModel) 
    {
        $objectManager =  \Magento\Framework\App\ObjectManager::getInstance();
        $this->customAttributesFactory = $objectManager->get('\TCGCollective\ProductAttributesLink\Model\CustomAttributesFactory');
        $this->mappingHelper = $objectManager->create(\TCGCollective\ProductAttributesLink\Helper\Data::class);
        $this->actionToySetsFactory = $objectManager->create(\TCGCollective\ProductAttributesLink\Model\ActionToySetsFactory::class);
        $this->livingCardGameSetsFactory = $objectManager->create(\TCGCollective\ProductAttributesLink\Model\LivingCardGameSetsFactory::class);
        $this->cardGameSetsFactory = $objectManager->create(\TCGCollective\ProductAttributesLink\Model\CardGamesSetsFactory::class);
        $this->bricksSetsFactory = $objectManager->create(\TCGCollective\ProductAttributesLink\Model\LegoSetsFactory::class);
        $this->sportCardsSetsFactory = $objectManager->create(\TCGCollective\ProductAttributesLink\Model\SportCardsSetsFactory::class);

        $result = [];

        $params = $this->request->getParams();
        $currentProduct = $this->locator->getProduct();

        $currentAttributeCode = $attributeModel->getAttributeCode();

        $customTableExist = false;
        $options = [];

        if ($params) {
            /* START CATEGORIES - COLLECTIBLE CARD GAMES */

            // Filter card_game dropdown options on product creation
            if ($currentAttributeCode == "card_game") {
                $collection = $this->cardGameSetsFactory->create()
                ->getCollection();
                
                $collection->addFieldToSelect(['card_game']);
                $collection->addFieldToFilter('card_game', ['neq' => null]);
                $collection->getSelect()->group('card_game');

                if ($collection->getSize()) {
                    foreach($collection as $model) {
                        $val = $model->getData('card_game');
                        if ($val) {
                            $label = $this->mappingHelper->getOptionLabelById('card_game', $val);
                            $options[] = [
                                'label' => $label,
                                'value' => $val
                            ];
                        }
                    }

                    if ($options) {
                        usort($options, fn($a, $b) => $a['label'] <=> $b['label']);
                    }

                    return $options;
                }
            }

            // Filter living_card_game dropdown options on product creation
            if ($currentAttributeCode == "living_card_game") {
                $collection = $this->livingCardGameSetsFactory->create()
                ->getCollection();
                
                $collection->addFieldToSelect(['living_card_game']);
                $collection->addFieldToFilter('living_card_game', ['neq' => null]);
                $collection->getSelect()->group('living_card_game');

                if ($collection->getSize()) {
                    foreach($collection as $model) {
                        $val = $model->getData('living_card_game');
                        if ($val) {
                            $label = $this->mappingHelper->getOptionLabelById('living_card_game', $val);
                            $options[] = [
                                'label' => $label,
                                'value' => $val
                            ];
                        }
                    }

                    if ($options) {
                        usort($options, fn($a, $b) => $a['label'] <=> $b['label']);
                    }

                    return $options;
                }
            }
            /* END CATEGORIES - CARD GAME */

            /* START CATEGORIES CARDS */

            // Filter sc_categories dropdown options on product creation
            if ($currentAttributeCode == "sc_categories") {
                $collection = $this->sportCardsSetsFactory->create()
                ->getCollection();
                
                $collection->addFieldToSelect(['sc_categories']);
                $collection->addFieldToFilter('sc_categories', ['neq' => null]);
                $collection->getSelect()->group('sc_categories');

                if ($collection->getSize()) {
                    foreach($collection as $model) {
                        $val = $model->getData('sc_categories');
                        if ($val) {
                            $label = $this->mappingHelper->getOptionLabelById('sc_categories', $val);
                            $options[] = [
                                'label' => $label,
                                'value' => $val
                            ];
                        }
                    }

                    if ($options) {
                        usort($options, fn($a, $b) => $a['label'] <=> $b['label']);
                    }

                    return $options;
                }
            }
            /* END CATEGORIES CARDS */
            
            /* START CATEGORIES - BRICKS */

            // Filter bricks_brand dropdown options on product creation
            if ($currentAttributeCode == "bricks_brand") {
                $collection = $this->bricksSetsFactory->create()
                ->getCollection();
                
                $collection->addFieldToSelect(['bricks_brand']);
                $collection->addFieldToFilter('bricks_brand', ['neq' => null]);
                $collection->getSelect()->group('bricks_brand');

                if ($collection->getSize()) {
                    foreach($collection as $model) {
                        $val = $model->getData('bricks_brand');
                        if ($val) {
                            $label = $this->mappingHelper->getOptionLabelById('bricks_brand', $val);
                            $options[] = [
                                'label' => $label,
                                'value' => $val
                            ];
                        }
                    }

                    if ($options) {
                        usort($options, fn($a, $b) => $a['label'] <=> $b['label']);
                    }

                    return $options;
                }
            }
            /* END CATEGORIES - BRICKS */

            /* START CATEGORIES - ACTION TOYS */
            $customAttributesColl = $this->customAttributesFactory->create()
            ->getCollection()
            ->addFieldToFilter('attribute_code', $currentAttributeCode);

            // Filter toys_brand dropdown options on product creation
            if ($currentAttributeCode == "toys_brand") {
                $categoryName = $this->getCurrentCategoryName();
                if ($categoryName) {
                    $collection = $this->actionToySetsFactory->create()
                    ->getCollection();
                    
                    $collection->addFieldToSelect(['toys_brand']);
                    // $collection->addFieldToFilter('categories', $categoryName);
                    $collection->addFieldToFilter('toys_brand', ['neq' => null]);
                    $collection->getSelect()->group('toys_brand');

                    if ($collection->getSize()) {
                        foreach($collection as $model) {
                            $val = $model->getData('toys_brand');
                            if ($val) {
                                $label = $this->mappingHelper->getOptionLabelById('toys_brand', $val);
                                $options[] = [
                                    'label' => $label,
                                    'value' => $val
                                ];
                            }
                        }

                        if ($options) {
                            usort($options, fn($a, $b) => $a['label'] <=> $b['label']);
                        }

                        return $options;
                    }
                }
            }

            // Filter toys_line dropdown options on product creation
            if ($currentAttributeCode == "toys_line") {
                $categoryName = $this->getCurrentCategoryName();
                if ($categoryName) {
                    $collection = $this->actionToySetsFactory->create()
                    ->getCollection();
                    
                    $collection->addFieldToSelect(['toys_line']);
                    $collection->addFieldToFilter('categories', $categoryName);
                    $collection->addFieldToFilter('toys_line', ['neq' => null]);
                    $collection->getSelect()->group('toys_line');

                    if ($collection->getSize()) {
                        foreach($collection as $model) {
                            $val = $model->getData('toys_line');
                            if ($val) {
                                $label = $this->mappingHelper->getOptionLabelById('toys_line', $val);
                                $options[] = [
                                    'label' => $label,
                                    'value' => $val
                                ];
                            }
                        }

                        if ($options) {
                            usort($options, fn($a, $b) => $a['label'] <=> $b['label']);
                        }

                        return $options;
                    }
                }
            }

            if ($currentAttributeCode == "toys_line") {
                $entityCode = 'toys_brand';
                

                // dd($customAttributesColl->getSize());
                // die($customAttributesColl->getSelect()->__toString());
                if ($customAttributesColl->getSize()) {
                    // die($customAttributesColl->getSelect()->__toString());

                    $customTableExist = true;

                    $entityValId = $params[$entityCode] ?? null;
                    if ($entityValId) {
                        $entityVal = $this->mappingHelper->getOptionLabelById($entityCode, $entityValId);
                        if ($entityVal) {
                            $customAttributesColl->addFieldToFilter($entityCode, $entityVal);
                        }
                    }
                    
                    // die($customAttributesColl->getSelect()->__toString());
                    // dd($customAttributesColl->getSize());
                    if ($customAttributesColl->getSize()) {
                        foreach($customAttributesColl as $customAttributes) {
                            $options[] = [
                                'label' => $customAttributes->getAttributeValue(),
                                'value' => $customAttributes->getAttributeValue()
                            ];
                        }
                    }
                }
            }
        }

        if ($customTableExist) {
            return $options;
        }
        
        return null;
    }

    public function getFilteredOptionIds($attributeModel)
    {
        $result = [];

        $params = $this->request->getParams();
        $currentProduct = $this->locator->getProduct();

        $currentAttributeCode = $attributeModel->getAttributeCode();

        if ($params) {
            $categoryIds = isset($params['category_ids']) && $params['category_ids'] ? $params['category_ids'] : (
                $currentProduct->getData('category_ids') ?: null
            );
            // die($categoryIds);
            if ($categoryIds) {
                $category = $this->mappingHelper->getCategoryById($categoryIds);
                if ($category) {
                    $categoryName = $category->getName();
                    if ($categoryName) {
                        // CARD GAME
                        if ($categoryName == "Collectible Card Games") {
                            // die('ok');
                            // $this->getCardGamesMapping();
                        }
                    }
                }
            }
        }

        return null;
        // if ($params) {
            // $targetAttributeCode = $attributeModel->getAttributeCode();

            // $cardGamesMapping = [
            //     'card_name',
            //     'card_artist',
            //     'card_feature',
            //     'card_manufacturer',
            //     // 'card_language',
            //     'card_type',
            //     'card_rarity',
            //     'card_sub_type',
            //     'card_number',
            //     'card_set_abbreviation',
            //     'release_date',
            //     'card_release_year'
            // ];

            

            // if (in_array($targetAttributeCode, $cardGamesMapping)) {

            //     $cardName = isset($params['card_name']) && $params['card_name'] ? $params['card_name'] : (
            //         $currentProduct->getCardName() ?: null
            //     );
            //     if ($targetAttributeCode == "card_number") {
            //         // die('d'.$cardName);
            //     }
            //     // die('d'.$cardName);
            //     if ($cardName && $targetAttributeCode != "card_name" && $targetAttributeCode != "card_set") {
            //         $collection = $this->cardGamesMappingCollFactory->create()
            //         ->addFieldToFilter('card_name', $cardName);
            //         if ($collection->getSize()) {
            //             $mapping = $collection->getFirstItem();
            //             $mappingData = $mapping->getData();
            //             // if ($targetAttributeCode == "card_number") {
            //             // die('<pre>'.print_r($mappingData,true));
            //             // }
            //             if ($mappingData) {
            //                 if (isset($mappingData[$targetAttributeCode])) {
            //                     $mappingDataValue = isset($mappingData[$targetAttributeCode]) ? $mappingData[$targetAttributeCode] : [];
            //                     // foreach($options as $option) {
            //                     //     if ($option['label'] != $mappingDataValue) {
            //                     //         continue;
            //                     //     }
            //                     if ($mappingDataValue) {
            //                         $result[] = $mappingDataValue;
            //                     }
            //                     // }

            //                     // if ($targetAttributeCode == "card_number") {

            //                     // // die('<pre>'.print_r($filteredOptions,true));
            //                     // }

            //                     // if (count($filteredOptions) > 1) {
            //                     //     // $filteredOptions = $this->addEmptyOption($filteredOptions);
            //                     // }

            //                     if ($targetAttributeCode == "card_number") {
            //                         // die('<pre>'.print_r($result,true));
            //                     }

            //                     return $result;
            //                 }
            //             }
            //         }
            //     }

            //     $data = [];

            //     $cardSet = isset($params['card_set']) && $params['card_set'] ? $params['card_set'] : (
            //         $currentProduct->getCardSet() ?: null
            //     );

            //     if ($targetAttributeCode == "card_name") {
            //         // die('cc'.$cardSet);
            //     }
                
            //     if ($cardSet) {
            //         $collection = $this->cardGamesMappingCollFactory->create()
            //         ->addFieldToFilter('card_set', $cardSet);
            //         if ($collection->getSize()) {
                        
            //             $setAbbrv = '';
            //             if ($targetAttributeCode == "card_name") {
            //                 foreach($collection as $mapping) {
            //                 // if ($mapping->getData('card_name') == "Against All Odds") {
            //                     $data[] = $mapping->getData('card_name');
            //                 // }
            //                 }
            //             } elseif ($targetAttributeCode == "card_set_abbreviation") {
            //                 $data[] = $collection->getFirstItem()->getData('card_set_abbreviation');
            //             }
            //         }
            //     }

                

            //     // if ($data && $this->locator->getProduct()->getId() || $data || !$this->locator->getProduct()->getId()) {/}
                
            //     // $filteredOptions = [];
            //     // foreach($options as $option) {
                    
            //     //     if (!in_array($option['label'], $data)) {
            //     //         continue;
            //     //     }
            //     if ($data) {
            //         $result[] = $data;
            //     }

                // return $result;
            // }

            // $collection = $this->prdAttrLinkCollFactory->create()
            // ->addFieldToFilter('target_attribute_code', $targetAttributeCode)
            // ->addFieldToFilter('mapping_type', 'attribute_option');

            // // if ($collection->getSize() > 1 && isset($params['mapping_group_id']) && $params['mapping_group_id']) {
            // //     $collection->addFieldToFilter('group_id', $params['mapping_group_id']);
            // // }

            // if ($collection->getSize()) {
            //     foreach($collection as $mapping) {
            //         $categoryId = $mapping->getCategoryId();
            //         $attributeCode = $mapping->getAttributeCode();
            //         $attributeOptionId = $mapping->getAttributeOptionId();

            //         $allowedOptions = [];
            //         if ($attributeCode && $attributeOptionId ) {
            //             if (isset($params[$attributeCode]) && $params[$attributeCode] == $attributeOptionId) {
            //                 $allowedOptions = $mapping->getTargetAttributeOptions() ? explode(",", $mapping->getTargetAttributeOptions()) : null;
            //             } elseif ($currentProduct->getData($attributeCode) && $currentProduct->getData($attributeCode) == $attributeOptionId) {
            //                 $allowedOptions = $mapping->getTargetAttributeOptions() ? explode(",", $mapping->getTargetAttributeOptions()) : null;
            //             }
            //         } elseif ($categoryId) {
            //             if (isset($params['category_ids']) && $params['category_ids'] == $categoryId) {
            //                 $allowedOptions = $mapping->getTargetAttributeOptions() ? explode(",", $mapping->getTargetAttributeOptions()) : null;
            //             } elseif ($currentProduct->getData('category_ids') && in_array($categoryId, $currentProduct->getData('category_ids'))) {
            //                 $allowedOptions = $mapping->getTargetAttributeOptions() ? explode(",", $mapping->getTargetAttributeOptions()) : null;
            //             }
            //         }

            //         if ($allowedOptions) {
            //             // $filteredOptions = [];
            //             // foreach($options as $option) {
            //             //     if (!in_array($option['value'], $allowedOptions)) {
            //             //         continue;
            //             //     }

            //                 $result[] = array_merge($result, $allowedOptions);
            //             // }

            //             // return $result;
            //         }
            //     }
            // }
        // }

        // return $result;
    }
    
    private function addEmptyOption($options)
    {
        if ($options && $options[0]['value'] != '') {
            array_unshift($options, ['label' => ' ', 'value' => '']);
        }

        return $options;
    }

    public function filterProductTypeOptions(
        $options
    ) {
        $filteredOptions = [];
        
        if ($options) {
            foreach($options as $option) {
                $label = $option['label'] ?? null;
                if ($label) {
                    if (str_contains($label, "Box Sets") || 
                    str_contains($label, "Minifigures") || 
                    str_contains($label, "Sealed Products") || 
                    str_contains($label, "Singles")) {
                        $filteredOptions[] = $option;
                    }
                }
            }
        }

        return $filteredOptions;
    }

    /**
     * Initial meta setup
     *
     * @param ProductAttributeInterface $attribute
     * @param string $groupCode
     * @param int $sortOrder
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function setupAttributeMeta(ProductAttributeInterface $attribute, $groupCode, $sortOrder)
    {
        $configPath = ltrim(static::META_CONFIG_PATH, ArrayManager::DEFAULT_PATH_DELIMITER);
        $attributeCode = $attribute->getAttributeCode();
        $meta = $this->arrayManager->set(
            $configPath,
            [],
            [
                'dataType' => $attribute->getFrontendInput(),
                'formElement' => $this->getFormElementsMapValue($attribute->getFrontendInput()),
                'visible' => $attribute->getIsVisible(),
                'required' => $attribute->getIsRequired(),
                'notice' => $attribute->getNote() === null ? null : __($attribute->getNote()),
                'default' => (!$this->isProductExists()) ? $this->getAttributeDefaultValue($attribute) : null,
                'label' => __($attribute->getDefaultFrontendLabel()),
                'code' => $attributeCode,
                'source' => $groupCode,
                'scopeLabel' => $this->getScopeLabel($attribute),
                'globalScope' => $this->isScopeGlobal($attribute),
                'sortOrder' => $sortOrder * self::SORT_ORDER_MULTIPLIER,
            ]
        );
        $product = $this->locator->getProduct();

        // TODO: Refactor to $attribute->getOptions() when MAGETWO-48289 is done
        $attributeModel = $this->getAttributeModel($attribute);
        if ($attributeModel->usesSource()) {
            $source = $attributeModel->getSource();
            // if ($source instanceof SpecificSourceInterface) {
            //     $options = $source->getOptionsFor($product);
            // } else {
            //     $options = $source->getAllOptions(true, false);
            // }
            // foreach ($options as &$option) {
            //     $option['__disableTmpl'] = true;
            // }
            $options = [];

            // $mappingAttributeCodeArr = $this->mappingHelper->getAllMappingTargetAttributeCode();
            $objectManager =  \Magento\Framework\App\ObjectManager::getInstance();
            $this->request = $objectManager->get('\Magento\Framework\App\RequestInterface');

            $sourceParam = $this->request->getParam('source');
            if ($product && $product->getId()) {
                $sourceParam = $product->getData('source');
            }
            // dd($sourceParam);
            
                
            // } else {
            // if ($groupCode != "product-details" || $groupCode == "product-details" && $attributeCode == "quantity_and_stock_status") {
            if ($sourceParam == "database") {
                $customTableOptions = $this->getCustomTableOptions($attributeModel);
                if ($customTableOptions && is_array($customTableOptions)) {
                    $options = $customTableOptions;
                } else {
                    $ajaxOptions = ['card_set','card_name','artist','card_number','card_feature','card_type'];
                    if (!array_filter($ajaxOptions, fn($opt) => str_contains($attributeCode, $opt))) {
                        $options = $source->getAllOptions(false, false);
                    } else {
                        if ($product->getData($attributeCode)) {
                            $existingOptionsColl = $this->mappingHelper->getCachedAttributeOptions($attributeCode);
                            if ($existingOptionsColl) {
                                $selectedOtions = array_filter($existingOptionsColl, fn($o) => $o['value'] == $product->getData($attributeCode));
                                $options = array_values($selectedOtions);
                            }
                        }
                    }
                }
            }

            if (!$sourceParam) {
                $ajaxOptions = ['card_set','card_name','artist','card_number','card_feature','card_type'];
                if (!array_filter($ajaxOptions, fn($opt) => str_contains($attributeCode, $opt))) {
                    $options = $source->getAllOptions(true, true);
                } else {
                    if ($product->getData($attributeCode)) {
                        $existingOptionsColl = $this->mappingHelper->getCachedAttributeOptions($attributeCode);
                        if ($existingOptionsColl) {
                            $selectedOtions = array_filter($existingOptionsColl, fn($o) => $o['value'] == $product->getData($attributeCode));
                            $options = array_values($selectedOtions);
                        }
                    }
                }
            }
            // if (
            //     $groupCode == "category-details"
            //     ||
            //     $attributeCode == "quantity_and_stock_status" 
            //     || str_contains($attributeCode, 'product_type')
            // ) {
            
            // }
                // }
            // }

            // if ($attributeCode == "visibility") {
            //     dd($options);
            // }

            $excludedAttributes = ["quantity_and_stock_status"];

            // if ($groupCode == "product-details" && !in_array($attributeCode, $excludedAttributes)) {
            //     $categoryName = null;

            //     $categoryId = $product->getData('category_ids') ? $product->getData('category_ids')[0] : null;
            //     if ($categoryId) {
            //         $category = $this->mappingHelper->getCategoryById($categoryId);
            //         if ($category) {
            //             $categoryName = $category->getName();
            //         }
            //     }

            //     $customTableMetadata = $this->mappingHelper->getCustomTableMetadata(null, $categoryName);
            //     if ($customTableMetadata) {
            //         $mainAttrCode = $customTableMetadata['mainAttrCode'] ?? null;
            //         if ($mainAttrCode) {
            //             $mainAttrVal = $product->getData($mainAttrCode);
            //             if ($mainAttrVal) {
            //                 $mainAttrValLabel = $this->mappingHelper->getOptionLabelById($mainAttrCode, $mainAttrVal);
            //                 $allowedOptions = $this->mappingHelper->getAllowedCustomAttributesOptions($attributeCode, $mainAttrCode, $mainAttrValLabel);

            //                 if ($product->getId()) {
                                
            //                         $options = $source->getAllOptions(false, false);
            //                         // dd($allOptions);

            //                         if ($allowedOptions) {
            //                             $result = array_filter($options, function ($item) use ($allowedOptions, $product, $attributeCode) {
            //                                 return in_array($item['label'], $allowedOptions) || $item['value'] == $product->getData($attributeCode);
            //                             });

            //                             $options = array_values($result);
            //                         }
            //                         // dd($options);
            //                         // $options = $source->getAllOptions(false, false);
            //                     // }
            //                     // $options = $this->mappingHelper->filterByCustomAttributes($options);
            //                 }
            //             }
            //         }
            //     }
            // }

            if ($sourceParam && $sourceParam == "database" && str_contains($attributeModel->getAttributeCode(), "product_type")) {
                // $options = $this->filterProductTypeOptions($options);
            }
            // }
            //     // die(print_r($filteredOptionIds,true));
            //     if ($filteredOptionIds) {
            //         $options = $source->getSpecificOptions($filteredOptionIds, false);
            //     } elseif ($filteredOptionIds != []) {
            //         $options = $source->getAllOptions(false, false);
            //     }
            // } else {
            //     $options = $source->getAllOptions(false, false);
            // }
            
            // if (is_array($customTableOptions)) {
                // $options = $customTableOptions;
            // } else {
                
            // }
            // } else {
            //     $filteredOptionIds = $this->getFilteredOptionIds($attributeModel);
            //     // die(print_r($filteredOptionIds,true));
            //     if (is_array($filteredOptionIds)) {
            //         $options = $source->getSpecificOptions($filteredOptionIds, false);
            //     } elseif ($filteredOptionIds == null) {
                    // $options = $source->getAllOptions(false, false);
                // }
            // }
// 
            // $options = $filteredOptionIds = $this->getFilteredOptionIds($attributeModel);;

            if (count($options)) {
                if ($attributeCode != "pre_order_payment_type") {
                    usort($options, fn($a, $b) => $a['label'] <=> $b['label']);
                }
            }

            // foreach ($options as &$option) {
            //     $option['__disableTmpl'] = true;
            // }

            // die('ini'.$options[0]['value']);
            
            // $meta = $this->arrayManager->merge(
            //     $configPath,
            //     $meta,
            //     [
            //         'options' => $this->convertOptionsValueToString($options),
            //         'default' => $options[0]['value']
            //     ]
            // );
            // if (count($newOptions) > 1) {
            //     $newOptions = $this->addEmptyOption($newOptions);
            // }

            // if ($attributeModel->getAttributeCode() == "card_name") {
            //     die('OKE:<pre>'.print_r($newOptions,true));
            // }
            
            $default = '';
            // if (count($newOptions) == 1) {
            //     $default = $newOptions[0]['value'];
            // }
            
            $mappingGroupName = $this->request->getParam('mapping_group_name') ?? null;
            $mappingPriority = $this->request->getParam('mapping_priority') ?? null;
            
            if ($mappingGroupName && $groupCode == "category-details") { 
                $collection = $this->mappingHelper->checkAttributeSetMappingExist($attributeCode, $mappingGroupName, $mappingPriority);
                if ($collection && $collection->getSize()) {
                    $allowedOptionIds = [];
                    
                    if ($collection->getFirstItem()->getPriority() > 1) {
                        foreach($collection as $mapping) {
                            $allowedOptionIds[] = $mapping->getAttributeOptionId();
                        }
                    }
                    
                    if ($allowedOptionIds) {
                        $filterLookup = array_flip($allowedOptionIds);
                        $options = array_values(array_filter($options, function ($item) use ($filterLookup) {
                            return isset($filterLookup[$item['value']]) && $item['label'] != "Card Game" && $item['label'] != "Brands" && $item['label'] != "Game";
                        }));
                    }
                }
            }

            if ($sourceParam == "database" && $groupCode != "category-details") {
                // $options = [];
            }

            $meta = $this->arrayManager->merge(
                $configPath,
                $meta,
                [
                    'options' => $this->convertOptionsValueToString($options),
                    'default' => $default
                ]
            );
        }

        if ($this->canDisplayUseDefault($attribute)) {
            $meta = $this->arrayManager->merge(
                $configPath,
                $meta,
                [
                    'service' => [
                        'template' => 'ui/form/element/helper/service',
                    ]
                ]
            );
        }

        if (!$this->arrayManager->exists($configPath . '/componentType', $meta)) {
            $meta = $this->arrayManager->merge($configPath, $meta, ['componentType' => Field::NAME]);
        }

        if (in_array($attributeCode, $this->attributesToDisable)
            || $product->isLockedAttribute($attributeCode)) {
            $meta = $this->arrayManager->merge($configPath, $meta, ['disabled' => true]);
        }

        // TODO: getAttributeModel() should not be used when MAGETWO-48284 is complete
        $childData = $this->arrayManager->get($configPath, $meta, []);
        if ($rules = $this->catalogEavValidationRules->build($this->getAttributeModel($attribute), $childData)) {
            $meta = $this->arrayManager->merge($configPath, $meta, ['validation' => $rules]);
        }

        $meta = $this->addUseDefaultValueCheckbox($attribute, $meta);

        switch ($attribute->getFrontendInput()) {
            case 'boolean':
                $meta = $this->customizeCheckbox($attribute, $meta);
                break;
            case 'textarea':
                $meta = $this->customizeWysiwyg($attribute, $meta);
                break;
            case 'price':
                $meta = $this->customizePriceAttribute($attribute, $meta);
                break;
            case 'gallery':
                // Gallery attribute is being handled by "Images And Videos" section
                $meta = [];
                break;
            case 'datetime':
                $meta = $this->customizeDatetimeAttribute($meta);
                break;
        }

        //Checking access to design config.
        $designDesignGroups = ['design', 'schedule-design-update'];
        if (in_array($groupCode, $designDesignGroups, true)) {
            if (!$this->auth->isAllowed('Magento_Catalog::edit_product_design')) {
                $meta = $this->arrayManager->merge(
                    $configPath,
                    $meta,
                    [
                        'disabled' => true,
                        'validation' => ['required' => false],
                        'required' => false,
                        'serviceDisabled' => true,
                    ]
                );
            }
        }

        return $meta;
    }

    /**
     * Returns attribute default value, based on db setting or setting in the system configuration.
     *
     * @param ProductAttributeInterface $attribute
     * @return null|string
     */
    private function getAttributeDefaultValue(ProductAttributeInterface $attribute)
    {
        if ($attribute->getAttributeCode() === 'page_layout') {
            $defaultValue = $this->scopeConfig->getValue(
                'web/default_layouts/default_product_layout',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $this->storeManager->getStore()
            );
            if ($defaultValue !== null) {
                $attribute->setDefaultValue($defaultValue);
            }
        }
        return $attribute->getDefaultValue();
    }

    /**
     * Convert options value to string.
     *
     * @param array $options
     * @return array
     */
    private function convertOptionsValueToString(array $options) : array
    {
        array_walk(
            $options,
            function (&$value) {
                if (isset($value['value']) && is_scalar($value['value'])) {
                    $value['value'] = (string)$value['value'];
                }
            }
        );

        return $options;
    }

    /**
     * Adds 'use default value' checkbox.
     *
     * @param ProductAttributeInterface $attribute
     * @param array $meta
     * @return array
     */
    private function addUseDefaultValueCheckbox(ProductAttributeInterface $attribute, array $meta)
    {
        $canDisplayService = $this->canDisplayUseDefault($attribute);
        if ($canDisplayService) {
            $meta['arguments']['data']['config']['service'] = [
                'template' => 'ui/form/element/helper/service',
            ];

            $meta['arguments']['data']['config']['disabled'] = !$this->scopeOverriddenValue->containsValue(
                \Magento\Catalog\Api\Data\ProductInterface::class,
                $this->locator->getProduct(),
                $attribute->getAttributeCode(),
                $this->locator->getStore()->getId()
            );
        }
        return $meta;
    }

    /**
     * Setup attribute container meta
     *
     * @param ProductAttributeInterface $attribute
     * @return array
     */
    public function setupAttributeContainerMeta(ProductAttributeInterface $attribute)
    {
        $containerMeta = $this->arrayManager->set(
            'arguments/data/config',
            [],
            [
                'formElement' => 'container',
                'componentType' => 'container',
                'breakLine' => false,
                'label' => $attribute->getDefaultFrontendLabel(),
                'required' => $attribute->getIsRequired(),
            ]
        );

        if ($attribute->getIsWysiwygEnabled()) {
            $containerMeta = $this->arrayManager->merge(
                'arguments/data/config',
                $containerMeta,
                [
                    'component' => 'Magento_Ui/js/form/components/group',
                    'label' => false,
                    'required' => false,
                ]
            );
        }

        return $containerMeta;
    }

    /**
     * Setup attribute data
     *
     * @param ProductAttributeInterface $attribute
     * @return mixed|null
     */
    public function setupAttributeData(ProductAttributeInterface $attribute)
    {
        $product = $this->locator->getProduct();
        // if ($attribute->getAttributeCode() == "mtg_card_foil") {
            // dd($attribute->getData());
            // }
            // dd($product->getData());
        $productId = $product->getId();
        if ($productId == 401) {
            // dd($product->getData());
            // dd($product->getData());
        }
        $prevSetId = $this->getPreviousSetId();
        $notUsed = !$prevSetId
            || ($prevSetId && !in_array($attribute->getAttributeCode(), $this->getPreviousSetAttributes()));

        if ($productId && $notUsed) {
            return $this->getValue($attribute);
        }

        return null;
    }

    /**
     * Customize checkboxes
     *
     * @param ProductAttributeInterface $attribute
     * @param array $meta
     * @return array
     */
    private function customizeCheckbox(ProductAttributeInterface $attribute, array $meta)
    {
        if ($attribute->getFrontendInput() === 'boolean') {
            $meta['arguments']['data']['config']['prefer'] = 'toggle';
            $meta['arguments']['data']['config']['valueMap'] = [
                'true' => '1',
                'false' => '0',
            ];
        }

        return $meta;
    }

    /**
     * Customize attribute that has price type
     *
     * @param ProductAttributeInterface $attribute
     * @param array $meta
     * @return array
     */
    private function customizePriceAttribute(ProductAttributeInterface $attribute, array $meta)
    {
        if ($attribute->getFrontendInput() === 'price') {
            $meta['arguments']['data']['config']['addbefore'] = $this->locator->getStore()
                ->getBaseCurrency()
                ->getCurrencySymbol();
        }

        return $meta;
    }

    /**
     * Add wysiwyg properties
     *
     * @param ProductAttributeInterface $attribute
     * @param array $meta
     * @return array
     */
    private function customizeWysiwyg(ProductAttributeInterface $attribute, array $meta)
    {
        if (!$attribute->getIsWysiwygEnabled()) {
            return $meta;
        }

        $meta['arguments']['data']['config']['formElement'] = WysiwygElement::NAME;
        $meta['arguments']['data']['config']['wysiwyg'] = true;
        $meta['arguments']['data']['config']['wysiwygConfigData'] = $this->wysiwygConfigProcessor->process($attribute);

        return $meta;
    }

    /**
     * Customize datetime attribute
     *
     * @param array $meta
     * @return array
     */
    private function customizeDatetimeAttribute(array $meta): array
    {
        $meta['arguments']['data']['config']['options']['showsTime'] = 1;

        return $meta;
    }

    /**
     * Retrieve form element
     *
     * @param string $value
     * @return mixed
     */
    private function getFormElementsMapValue($value)
    {
        $valueMap = $this->formElementMapper->getMappings();

        return $valueMap[$value] ?? $value;
    }

    /**
     * Retrieve attribute value
     *
     * @param ProductAttributeInterface $attribute
     * @return mixed
     */
    private function getValue(ProductAttributeInterface $attribute)
    {
        /** @var Product $product */
        $product = $this->locator->getProduct();

        // CUSTOM TCG COLLECTIVE | Handle multiselect attribute value to array
        $value = $product->getData($attribute->getAttributeCode());
        if ($value) {
            if ($attribute->getFrontendInput() === "multiselect") {
                return explode(",", $value);
            }
        }

        return $value;
    }

    /**
     * Retrieve scope label
     *
     * @param ProductAttributeInterface $attribute
     * @return \Magento\Framework\Phrase|string
     */
    private function getScopeLabel(ProductAttributeInterface $attribute)
    {
        if ($this->storeManager->isSingleStoreMode()
            || $attribute->getFrontendInput() === AttributeInterface::FRONTEND_INPUT
        ) {
            return '';
        }

        switch ($attribute->getScope()) {
            case ProductAttributeInterface::SCOPE_GLOBAL_TEXT:
                return __('[GLOBAL]');
            case ProductAttributeInterface::SCOPE_WEBSITE_TEXT:
                return __('[WEBSITE]');
            case ProductAttributeInterface::SCOPE_STORE_TEXT:
                return __('[STORE VIEW]');
        }

        return '';
    }

    /**
     * Whether attribute can have default value
     *
     * @param ProductAttributeInterface $attribute
     * @return bool
     */
    private function canDisplayUseDefault(ProductAttributeInterface $attribute)
    {
        $attributeCode = $attribute->getAttributeCode();
        if ($attributeCode == "price") {
            return false;
        }
        /** @var Product $product */
        $product = $this->locator->getProduct();
        if ($product->isLockedAttribute($attributeCode)) {
            return false;
        }

        if (isset($this->canDisplayUseDefault[$attributeCode])) {
            return $this->canDisplayUseDefault[$attributeCode];
        }

        return $this->canDisplayUseDefault[$attributeCode] = (
            ($attribute->getScope() != ProductAttributeInterface::SCOPE_GLOBAL_TEXT)
            && $product
            && $product->getId()
            && $product->getStoreId()
        );
    }

    /**
     * Check if attribute scope is global.
     *
     * @param ProductAttributeInterface $attribute
     * @return bool
     */
    private function isScopeGlobal($attribute)
    {
        return $attribute->getScope() === ProductAttributeInterface::SCOPE_GLOBAL_TEXT;
    }

    /**
     * Load attribute model by attribute data object.
     *
     * TODO: This method should be eliminated when all missing service methods are implemented
     *
     * @param ProductAttributeInterface $attribute
     * @return EavAttribute
     */
    private function getAttributeModel($attribute)
    {
        // The statement below solves performance issue related to loading same attribute options on different models
        if ($attribute instanceof EavAttribute) {
            return $attribute;
        }
        $attributeId = $attribute->getAttributeId();

        if (!array_key_exists($attributeId, $this->attributesCache)) {
            $this->attributesCache[$attributeId] = $this->eavAttributeFactory->create()->load($attributeId);
        }

        return $this->attributesCache[$attributeId];
    }

    /**
     * Calculate group code based on group name.
     *
     * TODO: This logic is copy-pasted from \Magento\Eav\Model\Entity\Attribute\Group::beforeSave
     * TODO: and should be moved to a separate service, which will allow two-way conversion groupName <=> groupCode
     * TODO: Remove after MAGETWO-48290 is complete
     *
     * @param AttributeGroupInterface $group
     * @return string
     */
    private function calculateGroupCode(AttributeGroupInterface $group)
    {
        $attributeGroupCode = $group->getAttributeGroupCode();

        if ($attributeGroupCode === 'images') {
            $attributeGroupCode = 'image-management';
        }

        return $attributeGroupCode;
    }
}
