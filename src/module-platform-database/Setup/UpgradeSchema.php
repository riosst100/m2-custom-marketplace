<?php

namespace TCGCollective\PlatformDatabase\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{

    /**
     * {@inheritdoc}
     * @throws \Zend_Db_Exception
     */
    public function upgrade(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $setup->startSetup();
        
        if (version_compare($context->getVersion(), '1.0.1', '<')) {

            $tableName = 'marketplace_lego_sets_singles';

            $toDelete = ['bricks_singles_names','bricks_singles_item_number'];

            foreach($toDelete as $col) {
                $setup->getConnection()->dropColumn(
                    $setup->getTable($tableName),
                    $col
                );
            }

            $toChange = [
                'bricks_minifigures_name' => 'bricks_singles_names',
                'bricks_minifigures_number' => 'bricks_singles_item_number'
            ];

            foreach($toChange as $oldCol => $newCol) {
                $setup->getConnection()->changeColumn(
                    $setup->getTable($tableName),
                    $oldCol,
                    $newCol,
                    [
                        'type' => Table::TYPE_TEXT,
                        'length' => 255,
                        'nullable' => true,
                        'comment' => ''
                    ]
                );
            }
        }

        if (version_compare($context->getVersion(), '1.0.2', '<')) {

            $tableName = 'marketplace_lego_sets_singles';

            $toDelete = [
                'bricks_set_name',
                'bricks_set_item_number'
            ];

            foreach($toDelete as $col) {
                $setup->getConnection()->dropColumn(
                    $setup->getTable($tableName),
                    $col
                );
            }

            $tableName = 'marketplace_lego_sets';

            $toDelete = [
                'bricks_set_item_number',
                'bricks_singles_names',
                'bricks_singles_item_number'
            ];

            foreach($toDelete as $col) {
                $setup->getConnection()->dropColumn(
                    $setup->getTable($tableName),
                    $col
                );
            }

            $toChange = [
                'bricks_set_number' => 'bricks_set_item_number'
            ];

            foreach($toChange as $oldCol => $newCol) {
                $setup->getConnection()->changeColumn(
                    $setup->getTable($tableName),
                    $oldCol,
                    $newCol,
                    [
                        'type' => Table::TYPE_TEXT,
                        'length' => 255,
                        'nullable' => true,
                        'comment' => ''
                    ]
                );
            }
        }

        $setup->endSetup();
    }
}
