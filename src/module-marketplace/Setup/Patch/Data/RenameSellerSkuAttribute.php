<?php

namespace TCGCollective\MarketPlace\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Catalog\Model\Product;

class RenameSellerSkuAttribute implements DataPatchInterface
{
    private $moduleDataSetup;
    private $eavSetupFactory;

    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);

        // ✅ Get entity type ID with helper
        $entityTypeId = $eavSetup->getEntityTypeId(Product::ENTITY);

        // ✅ Gunakan moduleDataSetup untuk akses nama tabel
        $this->moduleDataSetup->getConnection()->update(
            $this->moduleDataSetup->getTable('eav_attribute'),
            ['attribute_code' => 'supplier_sku'],
            [
                'attribute_code = ?' => 'seller_sku',
                'entity_type_id = ?' => $entityTypeId
            ]
        );

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    public static function getDependencies(): array
    {
        return [];
    }

    public function getAliases(): array
    {
        return [];
    }
}
