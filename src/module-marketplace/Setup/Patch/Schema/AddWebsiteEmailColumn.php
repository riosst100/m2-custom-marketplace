<?php
namespace TCGCollective\MarketPlace\Setup\Patch\Schema;

use Magento\Framework\Setup\Patch\SchemaPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class AddWebsiteEmailColumn implements SchemaPatchInterface
{
    private ModuleDataSetupInterface $moduleDataSetup;

    public function __construct(ModuleDataSetupInterface $moduleDataSetup)
    {
        $this->moduleDataSetup = $moduleDataSetup;
    }

    public function apply()
    {
        $this->moduleDataSetup->startSetup();

        $table = $this->moduleDataSetup->getTable('email_template');

        if (!$this->moduleDataSetup->getConnection()->tableColumnExists($table, 'website_id')) {
            $this->moduleDataSetup->getConnection()->addColumn(
                $table,
                'website_id',
                [
                    'type'     => Table::TYPE_SMALLINT,
                    'nullable' => true,
                    'comment'  => 'Website ID'
                ]
            );
        }

        $this->moduleDataSetup->endSetup();
    }

    public static function getDependencies() { return []; }
    public function getAliases() { return []; }
}
