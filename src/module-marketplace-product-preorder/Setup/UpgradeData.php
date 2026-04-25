<?php
namespace TCGCollective\MarketplaceProductPreorder\Setup;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;

class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    public function __construct(
        EavSetupFactory $eavSetupFactory
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function upgrade(
        ModuleDataSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        if (version_compare($context->getVersion(), "1.0.4", "<")) {
            $setup->startSetup();

            /** @var EavSetup $eavSetup */
            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

            // Pre Order Payment Type (select)
            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'pre_order_payment_type',
                [
                    'type' => 'int',
                    'label' => 'Pre Order Payment Type',
                    'input' => 'select',
                    'source' => \Magento\Eav\Model\Entity\Attribute\Source\Table::class,
                    'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                    'visible' => true,
                    'required' => false,
                    'user_defined' => true,
                    'option' => [
                        'values' => ['Full Payment', 'Deposit'],
                    ],
                    'used_in_product_listing' => true,
                    'group' => 'Pre Order'
                ]
            );

            // Pre Order Deposit Amount (price input)
            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'pre_order_deposit',
                [
                    'type' => 'decimal',
                    'label' => 'Pre Order Deposit Amount',
                    'input' => 'price',
                    'backend' => \Magento\Catalog\Model\Product\Attribute\Backend\Price::class,
                    'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                    'visible' => true,
                    'required' => false,
                    'user_defined' => true,
                    'used_in_product_listing' => true,
                    'group' => 'Pre Order'
                ]
            );

            // Pre Order Date (date input)
            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'pre_order_date',
                [
                    'type' => 'datetime',
                    'label' => 'Pre Order Date',
                    'input' => 'date',
                    'backend' => \Magento\Eav\Model\Entity\Attribute\Backend\Datetime::class,
                    'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                    'visible' => true,
                    'required' => false,
                    'user_defined' => true,
                    'used_in_product_listing' => false,
                    'group' => 'Pre Order'
                ]
            );

            // Pre Order Notes (text input)
            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'pre_order_notes',
                [
                    'type' => 'varchar',
                    'label' => 'Pre Order Notes',
                    'input' => 'text',
                    'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                    'visible' => true,
                    'required' => false,
                    'user_defined' => true,
                    'used_in_product_listing' => false,
                    'group' => 'Pre Order'
                ]
            );

            $setup->endSetup();
        }

        if (version_compare($context->getVersion(), "1.0.5", "<")) {
            $setup->startSetup();

            /** @var EavSetup $eavSetup */
            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

            $eavSetup->updateAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'pre_order_deposit',
                'frontend_input',
                'text'
            );

            $eavSetup->updateAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'pre_order_deposit',
                'frontend_input',
                'text'
            );

            $eavSetup->updateAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'pre_order_deposit',
                'backend_model',
                ''
            );

            $eavSetup->updateAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'pre_order_deposit',
                'backend_type',
                'varchar'
            );

            $setup->endSetup();
        }
    }
}
