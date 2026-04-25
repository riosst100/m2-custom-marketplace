<?php
namespace TCGCollective\MarketPlace\Setup\Patch\Schema;

use Magento\Framework\Setup\Patch\SchemaPatchInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class AddWebsiteIdToAdminUser implements SchemaPatchInterface
{
    public function __construct(
        private SchemaSetupInterface $schemaSetup
    ) {}

    public function apply()
    {
        $this->schemaSetup->startSetup();

        $this->schemaSetup->getConnection()->addColumn(
            $this->schemaSetup->getTable('admin_user'),
            'website_id',
            [
                'type' => Table::TYPE_INTEGER,
                'nullable' => true,
                'comment' => 'Restricted Website ID (NULL = All Websites)'
            ]
        );

        $this->schemaSetup->endSetup();
    }

    public static function getDependencies() { return []; }
    public function getAliases() { return []; }
}
