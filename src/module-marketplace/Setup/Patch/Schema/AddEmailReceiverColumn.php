<?php
namespace TCGCollective\MarketPlace\Setup\Patch\Schema;

use Magento\Framework\Setup\Patch\SchemaPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class AddEmailReceiverColumn implements SchemaPatchInterface
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

        if (!$this->moduleDataSetup->getConnection()->tableColumnExists($table, 'email_receiver')) {
            $this->moduleDataSetup->getConnection()->addColumn(
                $table,
                'email_receiver',
                [
                    'type' => Table::TYPE_TEXT,
                    'length' => 32,
                    'nullable' => false,
                    'default' => 'admin',
                    'comment' => 'Email Receiver (admin/seller)'
                ]
            );
        }

        $this->moduleDataSetup->endSetup();
    }

    public static function getDependencies() { return []; }
    public function getAliases() { return []; }
}
