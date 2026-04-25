<?php

namespace TCGCollective\SellerMembership\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Catalog\Model\Product;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Lofmp\SellerMembership\Model\Product\Type\SellerMembership;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;

class UpgradeData implements UpgradeDataInterface
{
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
     * Constructor
     *
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param CollectionFactory $customerAttributeCollectionFactory
     * @param \Magento\Catalog\Setup\CategorySetupFactory $categorySetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        CollectionFactory $customerAttributeCollectionFactory,
        \Magento\Catalog\Setup\CategorySetupFactory $categorySetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->customerAttributeCollectionFactory = $customerAttributeCollectionFactory;
        $this->_categorySetupFactory = $categorySetupFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function upgrade(
        ModuleDataSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        if (version_compare($context->getVersion(), "1.0.1", "<")) {
            //Your upgrade script
            $connection = $this->moduleDataSetup->getConnection();

            $categorySetup = $this->_categorySetupFactory->create(
                ['setup' => $setup]
            );
            $setup->startSetup();

            $categorySetup->addAttribute(
                Product::ENTITY,
                'membership_type',
                [
                    'group' => 'Product Details',
                    'label' => 'Membership Type',
                    'type' => 'varchar',
                    'input' => 'select',
                    'source' => 'TCGCollective\SellerMembership\Model\Config\Source\MembershipType',
                    'position' => 6,
                    'visible' => true,
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

            /*make sure these attributes are applied for membership product type only*/
            $attributes = [
                'membership_type',
            ];
            foreach ($attributes as $attributeCode) {
                $attribute = $categorySetup->getAttribute(Product::ENTITY, $attributeCode);
                $categorySetup->updateAttribute(Product::ENTITY, $attributeCode, 'apply_to', SellerMembership::TYPE_CODE);
            }

            $setup->endSetup();
        }

        if (version_compare($context->getVersion(), "1.0.2", "<")) {
            //Your upgrade script
            $connection = $this->moduleDataSetup->getConnection();

            $categorySetup = $this->_categorySetupFactory->create(
                ['setup' => $setup]
            );
            $setup->startSetup();

            $categorySetup->addAttribute(
                Product::ENTITY,
                'membership_country',
                [
                    'group' => 'Product Details',
                    'label' => 'Membership Country',
                    'type' => 'varchar',
                    'input' => 'select',
                    'source' => 'TCGCollective\SellerMembership\Model\Config\Source\MembershipCountry',
                    'position' => 6,
                    'visible' => true,
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

            /*make sure these attributes are applied for membership product type only*/
            $attributes = [
                'membership_country',
            ];
            foreach ($attributes as $attributeCode) {
                $attribute = $categorySetup->getAttribute(Product::ENTITY, $attributeCode);
                $categorySetup->updateAttribute(Product::ENTITY, $attributeCode, 'apply_to', SellerMembership::TYPE_CODE);
            }

            $setup->endSetup();
        }
    }
}

