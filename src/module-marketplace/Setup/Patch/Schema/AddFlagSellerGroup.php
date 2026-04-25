<?php
namespace TCGCollective\MarketPlace\Setup\Patch\Schema;

use Magento\Framework\Setup\Patch\SchemaPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class AddFlagSellerGroup implements SchemaPatchInterface
{
    private ModuleDataSetupInterface $moduleDataSetup;

    public function __construct(ModuleDataSetupInterface $moduleDataSetup)
    {
        $this->moduleDataSetup = $moduleDataSetup;
    }

    public function apply()
    {
        $this->moduleDataSetup->startSetup();

        $table = $this->moduleDataSetup->getTable('customer_group');

        if (!$this->moduleDataSetup->getConnection()->tableColumnExists($table, 'is_seller')) {
            $this->moduleDataSetup->getConnection()->addColumn(
                $table,
                'is_seller',
                [
                    'type' => Table::TYPE_SMALLINT,
                    'nullable' => false,
                    'default' => 0,
                    'comment' => 'Is Seller Group',
                ]
            );
        }

        $this->moduleDataSetup->endSetup();
    }

    public static function getDependencies() { return []; }
    public function getAliases() { return []; }
}
