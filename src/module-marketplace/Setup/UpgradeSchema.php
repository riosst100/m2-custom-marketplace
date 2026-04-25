<?php

namespace TCGCollective\MarketPlace\Setup;

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
            $sellerTable = $installer->getTable('lof_marketplace_seller');

            $installer->getConnection()->addColumn(
                $sellerTable,
                'address_line_1',
                [
                    'type' => Table::TYPE_TEXT,
                    'length' => 255,
                    'nullable' => true,
                    'comment' => 'Seller Address Line 1'
                ]
            );

            $installer->getConnection()->addColumn(
                $sellerTable,
                'address_line_2',
                [
                    'type' => Table::TYPE_TEXT,
                    'length' => 255,
                    'nullable' => true,
                    'comment' => 'Seller Address Line 2'
                ]
            );

            $installer->getConnection()->addColumn(
                $sellerTable,
                'company_registration_number',
                [
                    'type' => Table::TYPE_TEXT,
                    'length' => 255,
                    'nullable' => true,
                    'comment' => 'Company Registration Number'
                ]
            );
        }

        if (version_compare($context->getVersion(), '1.0.2', '<')) 
        {
            $sellerTable = $installer->getTable('lof_marketplace_seller');

            $installer->getConnection()->addColumn(
                $sellerTable,
                'term_and_conditions',
                [
                    'type' => Table::TYPE_TEXT,
                    'length' => 255,
                    'nullable' => true,
                    'comment' => 'Term & Conditions'
                ]
            );
        }

        if (version_compare($context->getVersion(), '1.0.3', '<')) 
        {
            $sellerTable = $installer->getTable('lof_marketplace_seller');

            $installer->getConnection()->addColumn(
                $sellerTable,
                'seller_type',
                [
                    'type' => Table::TYPE_TEXT,
                    'length' => 255,
                    'nullable' => true,
                    'comment' => 'Seller Type'
                ]
            );
        }

        if (version_compare($context->getVersion(), '1.0.4', '<')) 
        {
            $sellerTable = $installer->getTable('lof_marketplace_seller');

            $installer->getConnection()->addColumn(
                $sellerTable,
                'ship_to',
                [
                    'type' => Table::TYPE_TEXT,
                    'length' => 255,
                    'nullable' => true,
                    'comment' => 'Seller Ship To'
                ]
            );

            $installer->getConnection()->addColumn(
                $sellerTable,
                'ship_to_country',
                [
                    'type' => Table::TYPE_TEXT,
                    'length' => 255,
                    'nullable' => true,
                    'comment' => 'Seller Ship To Country'
                ]
            );

            $installer->getConnection()->addColumn(
                $sellerTable,
                'website_url',
                [
                    'type' => Table::TYPE_TEXT,
                    'length' => 255,
                    'nullable' => true,
                    'comment' => 'Website URL'
                ]
            );

            $installer->getConnection()->addColumn(
                $sellerTable,
                'operating_hours',
                [
                    'type' => Table::TYPE_TEXT,
                    'nullable' => true,
                    'comment' => 'Operating Hours'
                ]
            );
        }

        if (version_compare($context->getVersion(), '1.0.6', '<')) 
        {
            $table = $installer->getTable('lof_marketplace_group');
            $installer->getConnection()->addColumn(
                $table,
                'can_use_auction',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    'length' => 10,
                    'nullable' => false,
                    'default' => 0,
                    'comment' => 'Can use auction'
                ]
            );
            $installer->getConnection()->addColumn(
                $table,
                'can_use_preorder',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    'length' => 10,
                    'nullable' => false,
                    'default' => 0,
                    'comment' => 'Can use preorder'
                ]
            );
        }

        if (version_compare($context->getVersion(), '1.1.9', '<')) 
        {
            $table = $installer->getTable('import_history');
            $installer->getConnection()->addColumn(
                $table,
                'seller_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'unsigned' => true,
                    'nullable' => true,
                    'comment' => 'Seller Id'
                ]
            );
        }

        if (version_compare($context->getVersion(), '1.2.0', '<')) 
        {
            $table = $installer->getTable('import_history');
            $installer->getConnection()->addColumn(
                $table,
                'validation_summary',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'comment' => 'Validation Summary'
                ]
            );
        }

        if (version_compare($context->getVersion(), '1.3.8', '<')) {
            $connection = $setup->getConnection();
            $table = $setup->getTable('catalog_eav_attribute');

            if (!$connection->tableColumnExists($table, 'is_master_attribute')) {
                $connection->addColumn(
                    $table,
                    'is_master_attribute',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                        'nullable' => false,
                        'default' => '0',
                        'comment' => 'Is Master Attribute'
                    ]
                );
            }
        }

        if (version_compare($context->getVersion(), '1.4.1', '<')) 
        {
            $sellerTable = $installer->getTable('lof_marketplace_seller');

            $installer->getConnection()->addColumn(
                $sellerTable,
                'listing_website_ids',
                [
                    'type' => Table::TYPE_TEXT,
                    'length' => 255,
                    'nullable' => true,
                    'comment' => 'Listing Country'
                ]
            );
        }

        if (version_compare($context->getVersion(), '1.4.6', '<')) {
            // Get module table
            $installer->getConnection()->modifyColumn(
                $installer->getTable('lof_faq_question'),
                'creation_time',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    'nullable' => true
                ]
            );
        }

        if (version_compare($context->getVersion(), '1.5.7', '<')) 
        {
            $sellerTable = $installer->getTable('lof_marketplace_seller');

            $installer->getConnection()->addColumn(
                $sellerTable,
                'default_website_code',
                [
                    'type' => Table::TYPE_TEXT,
                    'length' => 255,
                    'nullable' => true,
                    'comment' => 'Default Country'
                ]
            );
        }

        if (version_compare($context->getVersion(), '1.6.2', '<')) 
        {
            $sellerTable = $installer->getTable('lofmp_favoriteseller_subscription');

            $installer->getConnection()->addColumn(
                $sellerTable,
                'website_id',
                [
                    'type' => Table::TYPE_SMALLINT,
                    'length' => 10,
                    'nullable' => false,
                    'default' => 0,
                    'comment' => 'Website Id'
                ]
            );
        }
        
        if (version_compare($context->getVersion(), '1.6.3', '<')) 
        {
            $sellerTable = $installer->getTable('lof_quickrfq');

            $installer->getConnection()->addColumn(
                $sellerTable,
                'website_id',
                [
                    'type' => Table::TYPE_SMALLINT,
                    'length' => 10,
                    'nullable' => false,
                    'default' => 0,
                    'comment' => 'Website Id'
                ]
            );
        }

        if (version_compare($context->getVersion(), '1.6.7', '<')) 
        {
            $table = $installer->getTable('lof_marketplace_group');
            $installer->getConnection()->addColumn(
                $table,
                'additional_listing',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    'length' => 10,
                    'nullable' => false,
                    'default' => 0,
                    'comment' => 'Can use auction'
                ]
            );
        }

        if (version_compare($context->getVersion(), '1.7.2', '<')) 
        {
            $sellerTable = $installer->getTable('lof_marketplace_seller');

            $installer->getConnection()->addColumn(
                $sellerTable,
                'dob',
                [
                    'type' => Table::TYPE_DATE,
                    'nullable' => true,
                    'comment' => 'Date of Birth'
                ]
            );

            // gender
            $installer->getConnection()->addColumn(
                $sellerTable,
                'gender',
                [
                    'type' => Table::TYPE_TEXT,
                    'length' => 20,
                    'nullable' => true,
                    'comment' => 'Gender'
                ]
            );
        }

        if (version_compare($context->getVersion(), '1.7.3', '<')) 
        {
            $connection = $installer->getConnection();
            $tableName  = $installer->getTable('lof_marketplace_seller');

            if ($connection->isTableExists($tableName)) {
                if ($connection->tableColumnExists($tableName, 'seller_type')) {
                    $connection->dropColumn(
                        $tableName,
                        'seller_type'
                    );
                }
            }
        }

        if (version_compare($context->getVersion(), '1.7.4', '<')) 
        {
            $connection = $installer->getConnection();
            $tableName  = $installer->getTable('lof_faq_category_store');

            if ($connection->isTableExists($tableName)) {
                if (!$connection->tableColumnExists($tableName, 'website_id')) {
                    $installer->getConnection()->addColumn(
                        $tableName,
                        'website_id',
                        [
                            'type' => Table::TYPE_SMALLINT,
                            'length' => 10,
                            'nullable' => false,
                            'default' => 0,
                            'comment' => 'Website Id'
                        ]
                    );
                }
            }
        }

        if (version_compare($context->getVersion(), '1.7.5', '<')) 
        {
            $connection = $installer->getConnection();
            $tableName  = $installer->getTable('lof_faq_question_store');

            if ($connection->isTableExists($tableName)) {
                if (!$connection->tableColumnExists($tableName, 'website_id')) {
                    $installer->getConnection()->addColumn(
                        $tableName,
                        'website_id',
                        [
                            'type' => Table::TYPE_SMALLINT,
                            'length' => 10,
                            'nullable' => false,
                            'default' => 0,
                            'comment' => 'Website Id'
                        ]
                    );
                }
            }
        }

        if (version_compare($context->getVersion(), '1.7.8', '<')) 
        {
            $connection = $installer->getConnection();
            $connection->dropTable($installer->getTable('lof_faq_question_store'));
            $connection->dropTable($installer->getTable('lof_faq_category_store'));

            $table = $installer->getConnection()->newTable(
                $installer->getTable('lof_faq_category_store')
            )->addColumn(
                'category_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['nullable' => false, 'primary' => true],
                'Category ID'
            )->addColumn(
                'website_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Store ID'
            )->addIndex(
                $installer->getIdxName('lof_faq_category_store', ['website_id']),
                ['website_id']
            )->addForeignKey(
                $installer->getFkName('lof_faq_category_store', 'category_id', 'lof_faq_category', 'category_id'),
                'category_id',
                $installer->getTable('lof_faq_category'),
                'category_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )->addForeignKey(
                $installer->getFkName('lof_faq_category_store', 'website_id', 'store_website', 'website_id'),
                'website_id',
                $installer->getTable('store_website'),
                'website_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )->setComment(
                'Lof Faq Category To Store Linkage Table'
            );
            $installer->getConnection()->createTable($table);
            
            $table = $installer->getConnection()->newTable(
                $installer->getTable('lof_faq_question_store')
            )->addColumn(
                'question_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                ['nullable' => false, 'primary' => true],
                'Question ID'
            )->addColumn(
                'website_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Store ID'
            )->addIndex(
                $installer->getIdxName('lof_faq_question_store', ['website_id']),
                ['website_id']
            )->addForeignKey(
                $installer->getFkName('lof_faq_question_store', 'question_id', 'lof_faq_question', 'question_id'),
                'question_id',
                $installer->getTable('lof_faq_question'),
                'question_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )->addForeignKey(
                $installer->getFkName('lof_faq_question_store', 'website_id', 'store_website', 'website_id'),
                'website_id',
                $installer->getTable('store_website'),
                'website_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )->setComment(
                'Lof Faq Question To Store Linkage Table'
            );
            $installer->getConnection()->createTable($table);
        }

        $installer->endSetup();
    }
}
