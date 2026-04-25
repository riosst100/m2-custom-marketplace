<?php

namespace TCGCollective\MarketPlace\Controller\Adminhtml\ProductListing\Configurations;

use Magento\Backend\App\Action;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\LocalizedException;

class Save extends Action
{
    const ADMIN_RESOURCE = 'TCGCollective_MarketPlace::config_save';

    protected ResourceConnection $resource;

    public function __construct(
        Action\Context $context,
        ResourceConnection $resource
    ) {
        $this->resource = $resource;
        parent::__construct($context);
    }

    public function execute()
    {
        $data = $this->getRequest()->getParams();

        if (!$data || empty($data['category_id'])) {
            return $this->_redirect('*/*/');
        }

        $connection = $this->resource->getConnection();

        $configTable = $this->resource->getTableName('marketplace_product_listing_category_config');
        $columnTable = $this->resource->getTableName('marketplace_product_listing_category_column');

        $connection->beginTransaction();

        try {

            /**
             * 1️⃣ CONFIG (UNIQUE PER CATEGORY)
             */
            $configId = $connection->fetchOne(
                $connection->select()
                    ->from($configTable, 'entity_id')
                    ->where('category_id = ?', $data['category_id'])
            );

            if ($configId) {

                $connection->update(
                    $configTable,
                    [
                        'tab_attribute_code' => $data['tab_attribute_code'] ?? '',
                        'is_active'          => $data['is_active'] ?? 1,
                        'updated_at'         => date('Y-m-d H:i:s')
                    ],
                    ['entity_id = ?' => $configId]
                );

            } else {

                $connection->insert($configTable, [
                    'category_id'        => $data['category_id'],
                    'tab_attribute_code' => $data['tab_attribute_code'] ?? '',
                    'is_active'          => $data['is_active'] ?? 1,
                    'created_at'         => date('Y-m-d H:i:s'),
                    'updated_at'         => date('Y-m-d H:i:s')
                ]);

                $configId = $connection->lastInsertId($configTable);
            }

            /**
             * 2️⃣ DEFAULT CATEGORY COLUMNS
             */

            // delete lama dulu biar aman dari unique constraint
            $connection->delete($columnTable, [
                'category_config_id = ?' => $configId
            ]);

            if (!empty($data['product_attributes']) && is_array($data['product_attributes'])) {

                foreach ($data['product_attributes'] as $sortOrder => $attributeCode) {

                    if (empty($attributeCode)) {
                        continue;
                    }

                    $connection->insert($columnTable, [
                        'category_config_id' => $configId,
                        'attribute_code'     => $attributeCode,
                        'sort_order'         => $sortOrder,
                        'is_sortable'        => 0,
                        'is_filterable'      => 0,
                        'is_visible'         => 1
                    ]);
                }
            }

            $connection->commit();

            $this->messageManager->addSuccessMessage(__('Configuration saved successfully.'));

        } catch (\Exception $e) {

            $connection->rollBack();

            $this->messageManager->addErrorMessage(
                __('Error while saving configuration: %1', $e->getMessage())
            );

            
        }

        return $this->_redirect('*/*/index');
    }
}