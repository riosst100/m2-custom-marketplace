<?php

namespace TCGCollective\MarketplaceProductImportExport\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\DB\Ddl\Table;

class UpgradeSchema implements UpgradeSchemaInterface
{
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        if (version_compare($context->getVersion(), '1.0.1', '<')) 
        {
            $sellerTable = $installer->getTable('importexport_importdata');

            $installer->getConnection()->addColumn(
                $sellerTable,
                'seller_id',
                [
                    'type' => Table::TYPE_INTEGER,
                    'nullable' => true,
                    'unsigned' => true,
                    'comment' => 'Seller ID'
                ]
            );
        }

        if (version_compare($context->getVersion(), '1.0.2', '<')) 
        {
            $sellerTable = $installer->getTable('media_gallery_asset');

            $installer->getConnection()->addColumn(
                $sellerTable,
                'seller_id',
                [
                    'type' => Table::TYPE_INTEGER,
                    'nullable' => true,
                    'unsigned' => true,
                    'comment' => 'Seller ID'
                ]
            );
        }

        $installer->endSetup();
    }
}
