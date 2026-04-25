<?php

namespace TCGCollective\MarketPlace\Plugin;

use Magento\Customer\Api\Data\GroupInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\App\ResourceConnection;

class CustomerGroupRepositoryPlugin
{
    private ResourceConnection $resource;

    public function __construct(ResourceConnection $resource)
    {
        $this->resource = $resource;
    }

    public function afterSave(
        GroupRepositoryInterface $subject,
        GroupInterface $result
    ) {
        $extensionAttributes = $result->getExtensionAttributes();

        if (!$extensionAttributes || $extensionAttributes->getIsSeller() === null) {
            return $result;
        }

        $connection = $this->resource->getConnection();
        $table = $this->resource->getTableName('customer_group');

        $connection->update(
            $table,
            ['is_seller' => (int)$extensionAttributes->getIsSeller()],
            ['customer_group_id = ?' => (int)$result->getId()]
        );

        return $result;
    }
}
