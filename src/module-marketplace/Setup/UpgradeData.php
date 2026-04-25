<?php

namespace TCGCollective\MarketPlace\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Catalog\Model\Product;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Lofmp\SellerMembership\Model\Product\Type\SellerMembership;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Eav\Model\Config as EavConfig;

class UpgradeData implements UpgradeDataInterface
{
    /**
     * EAV setup factory
     *
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var CollectionFactory
     */
    private $customerAttributeCollectionFactory;

    /**
     * Customer collection factory
     *
     * @var \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory
     */
    private $_customerCollectionFactory;

    /**
     * @var \Magento\Catalog\Setup\CategorySetupFactory
     */
    private $_categorySetupFactory;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory
     */
    protected $attributeSetCollectionFactory;

    protected $productCollectionFactory;
    protected $eavConfig;
    protected $productAction;
    protected $attributeCollectionFactory;

    /**
     * Constructor
     *
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param CollectionFactory $customerAttributeCollectionFactory
     * @param \Magento\Catalog\Setup\CategorySetupFactory $categorySetupFactory
     * @param EavSetupFactory $eavSetupFactory
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $attributeSetCollectionFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        CollectionFactory $customerAttributeCollectionFactory,
        \Magento\Catalog\Setup\CategorySetupFactory $categorySetupFactory,
        EavSetupFactory $eavSetupFactory,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $attributeSetCollectionFactory,
        ProductCollectionFactory $productCollectionFactory,
        EavConfig $eavConfig,
        \Magento\Catalog\Model\Product\Action $productAction,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory $attributeCollectionFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->customerAttributeCollectionFactory = $customerAttributeCollectionFactory;
        $this->_categorySetupFactory = $categorySetupFactory;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->attributeSetCollectionFactory = $attributeSetCollectionFactory;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->eavConfig = $eavConfig;
        $this->productAction = $productAction;
        $this->attributeCollectionFactory = $attributeCollectionFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function upgrade(
        ModuleDataSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        if (version_compare($context->getVersion(), "1.0.4", "<")) {
            //Your upgrade script
            $connection = $this->moduleDataSetup->getConnection();

            $categorySetup = $this->_categorySetupFactory->create(
                ['setup' => $setup]
            );
            $setup->startSetup();

            $categorySetup->addAttribute(
                Product::ENTITY,
                'seller_featured_package',
                [
                    'group' => 'Product Details',
                    'label' => 'Featured Package',
                    'type' => 'varchar',
                    'input' => 'boolean',
                    'position' => 6,
                    'visible' => true,
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                    'default' => '',
                    'visible' => true,
                    'required' => false,
                    'user_defined' => false,
                    'default' => '',
                    'visible_on_front' => false,
                    'unique' => false,
                    'is_configurable' => false,
                    'used_for_promo_rules' => false,
                    'is_used_in_grid' => false,
                    'is_visible_in_grid' => false,
                    'is_filterable_in_grid' => false,
                    'used_in_product_listing' => true,
                    'apply_to'=> 'seller_membership',
                ]
            );
             $categorySetup->addAttribute(
                 Product::ENTITY,
                 'seller_membership_order',
                 [
                    'group' => 'Product Details',
                    'label' => 'Sort Order',
                    'type' => 'static',
                    'input' => 'text',
                    'position' => 7,
                    'visible' => true,
                    'default' => '',
                    'visible' => true,
                    'required' => false,
                    'user_defined' => false,
                    'default' => '',
                    'visible_on_front' => false,
                    'unique' => false,
                    'is_configurable' => false,
                    'used_for_promo_rules' => true,
                    'is_used_in_grid' => false,
                    'is_filterable_in_grid' => false,
                    'used_in_product_listing' => true
                 ]
             );

            /*make sure these attributes are applied for membership product type only*/
            $attributes = [
                'seller_duration',
                'seller_featured_package',
                'seller_membership_order',
            ];
            foreach ($attributes as $attributeCode) {
                $attribute = $categorySetup->getAttribute(Product::ENTITY, $attributeCode);
                $categorySetup->updateAttribute(Product::ENTITY, $attributeCode, 'apply_to', SellerMembership::TYPE_CODE);
            }

            $fieldList = [
                'tax_class_id',
            ];

            // make these attributes applicable to vendor membership products
            foreach ($fieldList as $field) {
                $applyTo = explode(
                    ',',
                    $categorySetup->getAttribute(Product::ENTITY, $field, 'apply_to')
                );
                if (!in_array(SellerMembership::TYPE_CODE, $applyTo)) {
                    $applyTo[] = SellerMembership::TYPE_CODE;
                    $categorySetup->updateAttribute(
                        \Magento\Catalog\Model\Product::ENTITY,
                        $field,
                        'apply_to',
                        implode(',', $applyTo)
                    );
                }
            }

            $setup->endSetup();
        }

        if (version_compare($context->getVersion(), "1.0.5", "<")) {            
            $setup->startSetup();

            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'term_and_conditions',
                [
                    'type' => 'text',
                    'backend' => '',
                    'frontend' => '',
                    'label' => 'Term & Conditions',
                    'input' => 'textarea',
                    'class' => '',
                    'source' => '',
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                    'visible' => true,
                    'required' => false,
                    'user_defined' => true,
                    'default' => '',
                    'searchable' => false,
                    'filterable' => false,
                    'comparable' => false,
                    'visible_on_front' => true,
                    'used_in_product_listing' => true,
                    'unique' => false,
                    'group' => 'Product Details'
                ]
            );
        }

        if (version_compare($context->getVersion(), "1.0.7", "<")) {            
            $setup->startSetup();

            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Category::ENTITY,
                'shop_by',
                [
                    'type' => 'text',
                    'label' => 'Shop By',
                    'input' => 'multiselect',
                    'source' => \TCGCollective\MarketPlace\Model\Category\Attribute\Source\ShopBy::class,
                    'backend' => '',
                    'sort_order' => 20,
                    'input_renderer' => '',
                    'required' => false,
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                    'group' => 'General Information'
                ]
            );
        }

        if (version_compare($context->getVersion(), "1.0.8", "<")) {            
            $setup->startSetup();

            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Category::ENTITY,
                'hide_on_product_creation',
                [
                    'type' => 'int',
                    'label' => 'Hide on Product Creation',
                    'input' => 'select',
                    'source' => \Magento\Eav\Model\Entity\Attribute\Source\Boolean::class,
                    'sort_order' => 20,
                    'default' => 0,
                    'required' => false,
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                    'group' => 'General Information'
                ]
            );
        }

        if (version_compare($context->getVersion(), "1.1.2", "<")) {            
            $setup->startSetup();

            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Category::ENTITY,
                'custom_landing_page',
                [
                    'type' => 'varchar',
                    'label' => 'Custom Landing Page',
                    'input' => 'select',
                    'source' => \TCGCollective\MarketPlace\Model\Category\Attribute\Source\CustomLandingPage::class,
                    'sort_order' => 20,
                    'default' => null,
                    'required' => false,
                    'used_in_product_listing' => true, // for category pages
                    'visible_on_front' => true, // for frontend??
                    'is_used_in_grid' => true, // for category pages
                    'is_visible_in_grid' => true, // for category pages
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                    'group' => 'General Information'
                ]
            );
        }

        if (version_compare($context->getVersion(), "1.1.3", "<")) {            
            $setup->startSetup();

            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Category::ENTITY,
                'allowed_filters',
                [
                    'type' => 'text',
                    'label' => 'Allowed Filters',
                    'input' => 'multiselect',
                    'source' => \TCGCollective\MarketPlace\Model\Category\Attribute\Source\Filters::class,
                    'backend' => '',
                    'sort_order' => 30,
                    'input_renderer' => '',
                    'required' => false,
                    'used_in_product_listing' => true, // for category pages
                    'visible_on_front' => true, // for frontend??
                    'is_used_in_grid' => true, // for category pages
                    'is_visible_in_grid' => true, // for category pages
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                    'group' => 'General Information'
                ]
            );
        }

        if (version_compare($context->getVersion(), "1.1.5", "<")) {            
            $setup->startSetup();

            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Category::ENTITY,
                'hide_filters',
                [
                    'type' => 'int',
                    'label' => 'Hide Filters',
                    'input' => 'select',
                    'source' => \Magento\Eav\Model\Entity\Attribute\Source\Boolean::class,
                    'sort_order' => 40,
                    'default' => 0,
                    'required' => false,
                    'used_in_product_listing' => true, // for category pages
                    'visible_on_front' => true, // for frontend??
                    'is_used_in_grid' => true, // for category pages
                    'is_visible_in_grid' => true, // for category pages
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                    'group' => 'General Information'
                ]
            );
        }

        if (version_compare($context->getVersion(), "1.1.6", "<")) {            
            $setup->startSetup();

            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'auction',
                [
                    'type' => 'int',
                    'backend' => '',
                    'frontend' => '',
                    'label' => 'Auction',
                    'input' => 'boolean',
                    'class' => '',
                    'source' => '',
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                    'visible' => true,
                    'required' => false,
                    'user_defined' => true,
                    'default' => '',
                    'searchable' => false,
                    'filterable' => false,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'used_in_product_listing' => true,
                    'unique' => false,
                    'group' => 'Product Details'
                ]
            );

            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'sale',
                [
                    'type' => 'int',
                    'backend' => '',
                    'frontend' => '',
                    'label' => 'Sale',
                    'input' => 'boolean',
                    'class' => '',
                    'source' => '',
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                    'visible' => true,
                    'required' => false,
                    'user_defined' => true,
                    'default' => '',
                    'searchable' => false,
                    'filterable' => false,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'used_in_product_listing' => true,
                    'unique' => false,
                    'group' => 'Product Details'
                ]
            );
        }

        // if (version_compare($context->getVersion(), "1.1.7", "<")) {           
        //     $setup->startSetup();

        //     $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        //     $eavSetup->addAttribute(
        //         \Magento\Catalog\Model\Category::ENTITY,
        //         'default_subcategory',
        //         [
        //             'type' => 'text',
        //             'label' => 'Default Sub Category',
        //             'input' => 'select',
        //             'source' => \TCGCollective\MarketPlace\Model\Category\Attribute\Source\SubCategory::class,
        //             'backend' => '',
        //             'sort_order' => 30,
        //             'input_renderer' => '',
        //             'required' => false,
        //             'used_in_product_listing' => true, // for category pages
        //             'visible_on_front' => true, // for frontend??
        //             'is_used_in_grid' => true, // for category pages
        //             'is_visible_in_grid' => true, // for category pages
        //             'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
        //             'group' => 'General Information'
        //         ]
        //     );
        // }

        if (version_compare($context->getVersion(), "1.1.7", "<")) {           
            $setup->startSetup();

            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

            // dd(get_class_methods($eavSetup));

            // get default attribute set id
            // $attributeSetIds = $eavSetup->getDefaultAttributeSetId(\Magento\Catalog\Model\Product::ENTITY);
            $attributeGroupName = 'Category Details';

            $attributeCodes = ['category_ids','card_game','card_product_type'];

            
            /** @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection $attributeSetCollection */
            $attributeSetCollection = $this->attributeSetCollectionFactory->create();
            $attributeSets = $attributeSetCollection->getItems();
            /** @var \Magento\Eav\Model\Entity\Attribute\Set $attributeSet */
            foreach($attributeSets as $attributeSet) {
                $attributeSetId = $attributeSet->getAttributeSetId();
                // your custom attribute group/tab
                $eavSetup->addAttributeGroup(
                    \Magento\Catalog\Model\Product::ENTITY,
                    $attributeSetId,
                    $attributeGroupName, // attribute group name
                    1 // sort order
                );

                // add attribute to group
                foreach($attributeCodes as $index => $attributeCode) {
                    $eavSetup->addAttributeToGroup(
                        \Magento\Catalog\Model\Product::ENTITY,
                        $attributeSetId,
                        $attributeGroupName, // attribute group
                        $attributeCode, // attribute code
                        $index+1 // sort order
                    );
                }
            }

            $setup->endSetup();
        }

        if (version_compare($context->getVersion(), "1.2.4", "<")) {
            $setup->startSetup();

            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'publish_status',
                [
                    'type' => 'int',
                    'backend' => '',
                    'frontend' => '',
                    'label' => 'Publish Product',
                    'input' => 'select',
                    'class' => '',
                    'source' => 'Magento\Catalog\Model\Product\Attribute\Source\Status',
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_WEBSITE,
                    'visible' => false,
                    'required' => false,
                    'user_defined' => true,
                    'default' => '',
                    'searchable' => true,
                    'filterable' => true,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'used_in_product_listing' => false,
                    'unique' => false,
                    'group' => 'Product Details'
                ]
            );

            $setup->endSetup();
        }

        if (version_compare($context->getVersion(), "1.2.5", "<")) {
            $setup->startSetup();

            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
            
            $eavSetup->updateAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'lego_minifigures_name',
                'frontend_input',
                'multiselect'
            );

            $eavSetup->updateAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'lego_set_name',
                'frontend_input',
                'multiselect'
            );

            $setup->endSetup();
        }

        if (version_compare($context->getVersion(), "1.3.2", "<")) {
            $setup->startSetup();

            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
            
            $eavSetup->updateAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'mtg_card_foil',
                'frontend_input',
                'multiselect'
            );

            $eavSetup->updateAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'mtg_card_foil',
                'backend_type',
                'text'
            );

            $eavSetup->updateAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'mtg_card_foil',
                'backend_model',
                'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend'
            );

            $eavSetup->updateAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'mtg_card_foil',
                'source_model',
                'Magento\Eav\Model\Entity\Attribute\Source\Table'
            );

            $setup->endSetup();
        }

        if (version_compare($context->getVersion(), "1.3.3", "<")) {
            $setup->startSetup();

            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
            
            $eavSetup->updateAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'lego_set_name',
                [
                    'backend_type' => 'text',
                    'source_model' => '',
                    'backend_model' => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend'
                ]
            );

            $eavSetup->updateAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'lego_minifigures_name',
                [
                    'backend_type' => 'text',
                    'source_model' => '',
                    'backend_model' => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend'
                ]
            );

            $setup->endSetup();
        }

        if (version_compare($context->getVersion(), "1.3.4", "<")) {
            $setup->startSetup();

            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

            $multiselectAttributes = [
                'lego_minifigures_number',
                'lego_set_number',
                'release_year',
                'age_level',
                'condition',
                'lego_interest',
                'lego_features',
                'lego_color'
            ];
            
            foreach($multiselectAttributes as $attrCode) {
                $eavSetup->updateAttribute(
                    \Magento\Catalog\Model\Product::ENTITY,
                    $attrCode,
                    [
                        'backend_type' => 'text',
                        'frontend_input' => 'multiselect',
                        'source_model' => '',
                        'backend_model' => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend'
                    ]
                );
            }

            $setup->endSetup();
        }

        if (version_compare($context->getVersion(), "1.3.5", "<")) {
            $setup->startSetup();

            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'source',
                [
                    'type' => 'varchar',
                    'backend' => '',
                    'frontend' => '',
                    'label' => 'Source',
                    'input' => 'text',
                    'class' => '',
                    'source' => '',
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                    'visible' => false,
                    'required' => false,
                    'user_defined' => false,
                    'default' => '',
                    'searchable' => true,
                    'filterable' => true,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'used_in_product_listing' => false,
                    'is_used_in_grid' => false,
                    'is_visible_in_grid' => false,
                    'is_filterable_in_grid' => false,
                    'unique' => false,
                    'group' => 'Product Details'
                ]
            );

            $setup->endSetup();
        }

        if (version_compare($context->getVersion(), "1.3.6", "<")) {
            $setup->startSetup();

            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

            $multiselectAttributes = [
                'lego_set_name',
                'lego_interest',
                'release_year',
                'condition',
                'age_level',
                'mtg_card_foil',
                'lego_set_number',
                'lego_minifigures_name',
                'lego_minifigures_number',
                'lego_features',
                'lego_color',
                'action_toys_characters',
                'action_toys_series',
                'action_toys_franchise',
                'sculptor',
                'toy_brand',
                'baseball_sc_card_feature',
                'baseball_sc_feature',
                'basketball_sc_feature',
                'basketball_sc_players',
                'mtg_game_format',
                'hocg_card_set',
                'ucg_card_set',
                'ucg_card_name',
                'hocg_card_name',
                'vg_card_set',
                'vg_card_name',
                'ua_card_set',
                'ua_card_name',
                'ws_card_set',
                'ws_card_name',
                'dbf_set',
                'di_card_name',
                'swu_card_set',
                'swu_card_name'
            ];
            
            foreach($multiselectAttributes as $attrCode) {
                $eavSetup->updateAttribute(
                    \Magento\Catalog\Model\Product::ENTITY,
                    $attrCode,
                    [
                        'backend_type' => 'int',
                        'frontend_input' => 'select',
                        'source_model' => 'Magento\Eav\Model\Entity\Attribute\Source\Table',
                        'backend_model' => ''
                    ]
                );
            }

            $setup->endSetup();
        }

        if (version_compare($context->getVersion(), '1.4.0', '<')) {
            $this->ensureDefaultValues('publish_status');
        }

        if (version_compare($context->getVersion(), '1.4.2', '<')) {
            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

            $obj = \Magento\Framework\App\ObjectManager::getInstance();
            $searchCriteriaBuilder = $obj->get(\Magento\Framework\Api\SearchCriteriaBuilder::class);
            $attributeSetRepository = $obj->get(\Magento\Eav\Api\AttributeSetRepositoryInterface::class);
            $attributeGroupRepository = $obj->get(\Magento\Eav\Api\AttributeGroupRepositoryInterface::class);
            
            $attributeCodes = ['lof_preorder','preorder_availability','preorder_msg','product_rma'];

            foreach($attributeCodes as $attributeCode) {
                $attributeId = $eavSetup->getAttributeId(Product::ENTITY, $attributeCode);

                if ($attributeId) {
                    $connection = $setup->getConnection();
                    $table = $setup->getTable('eav_entity_attribute');

                    // Hapus relasi attribute dari semua attribute set/group
                    $connection->delete($table, ['attribute_id = ?' => $attributeId]);
                }
            }

            
            // $eavConfig = $obj->get(\Magento\Eav\Model\Config::class);
            // $attributeRepository = $obj->get(\Magento\Eav\Api\AttributeRepositoryInterface::class);
            // $attributeManagement = $obj->get(\Magento\Eav\Api\AttributeManagementInterface::class);

            // // Ganti ini dengan kode atribut yang ingin dihapus dari semua set
            // $attributeCodes = ['lof_preorder','preorder_availability','preorder_msg','product_rma'];

            // // Mendapatkan semua attribute set dari entity type 'catalog_product'
            // $entityTypeId = \Magento\Catalog\Model\Product::ENTITY;
            // $attributeSets = $obj->create(\Magento\Eav\Api\AttributeSetRepositoryInterface::class)
            //     ->getList($obj->create(\Magento\Framework\Api\SearchCriteriaBuilder::class)->create());

            // foreach ($attributeSets->getItems() as $attributeSet) {
            //     $setId = $attributeSet->getAttributeSetId();

            //     try {
            //         // Hapus attribute dari set ini
            //         foreach($attributeCodes as $attributeCode) {
            //             $attributeManagement->remove($entityTypeId, $setId, $attributeCode);
            //             echo "Attribute '$attributeCode' removed from attribute set ID $setId\n";
            //         }
            //     } catch (\Exception $e) {
            //         echo "Error on set ID $setId: " . $e->getMessage() . "\n";
            //     }
            // }
        }

        if (version_compare($context->getVersion(), "1.4.3", "<")) {
            $setup->startSetup();

            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

            $multiselectAttributes = [
                'mtg_card_feature'
            ];
            
            foreach($multiselectAttributes as $attrCode) {
                $eavSetup->updateAttribute(
                    \Magento\Catalog\Model\Product::ENTITY,
                    $attrCode,
                    [
                        'backend_type' => 'text',
                        'frontend_input' => 'multiselect',
                        'source_model' => '',
                        'backend_model' => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend'
                    ]
                );
            }

            $setup->endSetup();
        }

        if (version_compare($context->getVersion(), "1.4.4", "<")) {
            $setup->startSetup();

            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

            $multiselectAttributes = [
                'mtg_card_feature'
            ];
            
            foreach($multiselectAttributes as $attrCode) {
                $eavSetup->updateAttribute(
                    \Magento\Catalog\Model\Product::ENTITY,
                    $attrCode,
                    [
                        'backend_type' => 'int',
                        'frontend_input' => 'select',
                        'source_model' => 'Magento\Eav\Model\Entity\Attribute\Source\Table',
                        'backend_model' => ''
                    ]
                );
            }

            $setup->endSetup();
        }

        if (version_compare($context->getVersion(), "1.4.5", "<")) {
            $setup->startSetup();

            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

            // Ambil entity_type_id untuk produk
            $entityTypeId = $eavSetup->getEntityTypeId(\Magento\Catalog\Model\Product::ENTITY);

            // Ambil semua attribute yang berakhiran card_feature atau card_artist
            $attributeCollection = $this->attributeCollectionFactory->create()
                ->addFieldToFilter('entity_type_id', $entityTypeId)
                ->addFieldToFilter('attribute_code', [
                    ['like' => '%card_feature'],
                    ['like' => '%card_artist']
                ]);

            foreach ($attributeCollection as $attribute) {
                $attrCode = $attribute->getAttributeCode();

                // Update attribute agar jadi multiselect
                $eavSetup->updateAttribute(
                    \Magento\Catalog\Model\Product::ENTITY,
                    $attrCode,
                    [
                        'backend_type' => 'text',
                        'frontend_input' => 'multiselect',
                        'source_model' => '',
                        'backend_model' => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend'
                    ]
                );
            }

            $setup->endSetup();
        }

        if (version_compare($context->getVersion(), "1.4.8", "<")) {
            $setup->startSetup();

            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'pre_order_notes',
                [
                    'type' => 'varchar',
                    'backend' => '',
                    'frontend' => '',
                    'label' => 'Pre Order Notes',
                    'input' => 'text',
                    'class' => '',
                    'source' => '',
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                    'visible' => true,
                    'required' => false,
                    'user_defined' => true,
                    'default' => '',
                    'searchable' => true,
                    'filterable' => true,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'used_in_product_listing' => false,
                    'is_used_in_grid' => false,
                    'is_visible_in_grid' => false,
                    'is_filterable_in_grid' => false,
                    'unique' => false
                ]
            );

            $setup->endSetup();
        }

        if (version_compare($context->getVersion(), "1.4.9", "<")) {
            $setup->startSetup();

            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'pre_order_deposit',
                [
                    'type' => 'decimal',
                    'label' => 'Pre Order Deposit Amount',
                    'input' => 'price',
                    'backend' => \Magento\Catalog\Model\Product\Attribute\Backend\Price::class,
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                    'visible' => true,
                    'required' => false,
                    'user_defined' => true,
                    'default' => '',
                    'searchable' => true,
                    'filterable' => true,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'used_in_product_listing' => false,
                    'is_used_in_grid' => false,
                    'is_visible_in_grid' => false,
                    'is_filterable_in_grid' => false,
                    'unique' => false
                ]
            );

            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'pre_order_date',
                [
                    'type' => 'datetime',
                    'label' => 'Pre Order Date',
                    'input' => 'date',
                    'backend' => \Magento\Eav\Model\Entity\Attribute\Backend\Datetime::class,
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                    'visible' => true,
                    'required' => false,
                    'user_defined' => true,
                    'default' => '',
                    'searchable' => true,
                    'filterable' => true,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'used_in_product_listing' => false,
                    'is_used_in_grid' => false,
                    'is_visible_in_grid' => false,
                    'is_filterable_in_grid' => false,
                    'unique' => false
                ]
            );

            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'pre_order_payment_type',
                [
                    'type' => 'int',
                    'label' => 'Pre Order Payment Type',
                    'input' => 'select',
                    'source' => \Magento\Eav\Model\Entity\Attribute\Source\Table::class,
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                    'visible' => true,
                    'required' => false,
                    'user_defined' => true,
                    'default' => '',
                    'searchable' => true,
                    'filterable' => true,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'used_in_product_listing' => false,
                    'is_used_in_grid' => false,
                    'is_visible_in_grid' => false,
                    'is_filterable_in_grid' => false,
                    'unique' => false,
                    'option' => [
                        'values' => ['Full Payment', 'Deposit'],
                    ],
                ]
            );

            $setup->endSetup();
        }

        if (version_compare($context->getVersion(), "1.5.0", "<")) {           
            $setup->startSetup();

            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
            $attributeGroupName = 'Pre Orders';

            $attributeCodes = ['pre_order_payment_type','pre_order_deposit','pre_order_date','pre_order_notes'];
            
            $attributeSetCollection = $this->attributeSetCollectionFactory->create();
            $attributeSets = $attributeSetCollection->getItems();

            /** @var \Magento\Eav\Model\Entity\Attribute\Set $attributeSet */
            foreach ($attributeSets as $attributeSet) {
                $setName = $attributeSet->getAttributeSetName();

                // hanya untuk attribute set tertentu
                if (strpos($setName, 'Pre Orders | Sealed Products') !== false ||
                    strpos($setName, 'Pre Orders | Singles') !== false) {

                    $attributeSetId = $attributeSet->getAttributeSetId();

                    // buat group Pre Order kalau belum ada
                    $eavSetup->addAttributeGroup(
                        \Magento\Catalog\Model\Product::ENTITY,
                        $attributeSetId,
                        $attributeGroupName,
                        100
                    );

                    // assign attribute ke group
                    foreach ($attributeCodes as $index => $attributeCode) {
                        $eavSetup->addAttributeToGroup(
                            \Magento\Catalog\Model\Product::ENTITY,
                            $attributeSetId,
                            $attributeGroupName,
                            $attributeCode,
                            $index + 1
                        );
                    }
                }
            }

            $setup->endSetup();
        }

        if (version_compare($context->getVersion(), "1.5.2", "<")) {           
            $setup->startSetup();

            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
            $attributeGroupName = 'Pre Orders';

            $attributeSetCollection = $this->attributeSetCollectionFactory->create();
            $attributeSets = $attributeSetCollection->getItems();

            /** @var \Magento\Eav\Model\Entity\Attribute\Set $attributeSet */
            foreach ($attributeSets as $attributeSet) {
                $setName = $attributeSet->getAttributeSetName();

                // hanya untuk attribute set tertentu
                if (strpos($setName, 'Pre Orders | Sealed Products') !== false ||
                    strpos($setName, 'Pre Orders | Singles') !== false) {

                    $attributeSetId = $attributeSet->getAttributeSetId();
                    
                    $eavSetup->updateAttributeGroup(
                        \Magento\Catalog\Model\Product::ENTITY,
                        $attributeSetId,
                        $attributeGroupName,
                        [
                            'sort_order' => 1
                        ]
                    );
                }
            }

            $setup->endSetup();
        }

        if (version_compare($context->getVersion(), "1.5.4", "<")) {           
            $setup->startSetup();

            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
            
            $eavSetup->updateAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'pre_order_payment_type',
                'source_model',
                'TCGCollective\MarketplaceProductPreorder\Model\Attribute\Source\PreOrder\PaymentType'
            );

            $eavSetup->updateAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'pre_order_payment_type',
                'backend_type',
                'varchar'
            );

            $eavSetup->updateAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'pre_order_payment_type',
                'backend_model',
                'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend'
            );

            $setup->endSetup();
        }

        if (version_compare($context->getVersion(), "1.5.9", "<")) {
            $setup->startSetup();

            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
            
            $attributes = [
                'pre_order_notes',
                'pre_order_deposit',
                'pre_order_date',
                'pre_order_payment_type',
                'source'
            ];

            $fieldsToUpdate = [
                'is_searchable'          => 0,
                'is_filterable'          => 0,
                'is_filterable_in_search'=> 0,
                'is_used_in_grid'        => 0,
                'is_visible_in_grid'     => 0,
                'is_filterable_in_grid'  => 0
            ];

            foreach ($attributes as $attributeCode) {
                foreach ($fieldsToUpdate as $field => $value) {
                    $eavSetup->updateAttribute(
                        \Magento\Catalog\Model\Product::ENTITY,
                        $attributeCode,
                        $field,
                        $value
                    );
                }
            }

            $setup->endSetup();
        }

        if (version_compare($context->getVersion(), "1.6.8", "<")) {
            $categorySetup = $this->_categorySetupFactory->create(
                ['setup' => $setup]
            );
            
            $setup->startSetup();

            $categorySetup->addAttribute(
                Product::ENTITY,
                'ship_from',
                [
                    'group' => 'Product Details',
                    'label' => 'Ship From Country',
                    'type' => 'varchar',
                    'input' => 'select',
                    'source' => 'TCGCollective\MarketPlace\Model\Config\Source\ShipFrom',
                    'position' => 6,
                    'visible' => true,
                    'default' => '',
                    'required' => false,
                    'user_defined' => false,
                    'default' => '',
                    'visible_on_front' => true,
                    'unique' => false,
                    'is_configurable' => false,
                    'used_for_promo_rules' => false,
                    'is_used_in_grid' => false,
                    'is_visible_in_grid' => false,
                    'is_filterable_in_grid' => false,
                    'used_in_product_listing' => true
                ]
            );

            $setup->endSetup();
        }

        if (version_compare($context->getVersion(), "1.6.9", "<")) {
            $categorySetup = $this->_categorySetupFactory->create(
                ['setup' => $setup]
            );
            
            $setup->startSetup();
            
            $categorySetup->updateAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'ship_from',
                'backend_type',
                'int'
            );

            $setup->endSetup();
        }

        if (version_compare($context->getVersion(), "1.7.0", "<")) {
            $categorySetup = $this->_categorySetupFactory->create(
                ['setup' => $setup]
            );
            
            $setup->startSetup();
            
            $categorySetup->updateAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'ship_from',
                'frontend_label',
                'Shipped From'
            );

            $setup->endSetup();
        }
    }

    private function ensureDefaultValues($attributeCode)
    {
        $attribute = $this->eavConfig->getAttribute('catalog_product', $attributeCode);
        $attributeId = $attribute->getId(); // ID atribut yang akan dicek

        $collection = $this->productCollectionFactory->create();

        $collection->getSelect()->joinLeft(
            ['pei' => 'catalog_product_entity_int'],
            'pei.entity_id = e.entity_id AND pei.attribute_id = ' . $attributeId . ' AND pei.store_id = 0',
            []
        )->where('pei.entity_id IS NULL');

        foreach ($collection as $product) {
            try {
                $this->productAction->updateAttributes([$product->getId()], [
                    'publish_status'=> 0
                ], 0);
            } catch (\Exception $e) {}
        }
    }
}

