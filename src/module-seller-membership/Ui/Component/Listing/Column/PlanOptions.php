<?php

namespace TCGCollective\SellerMembership\Ui\Component\Listing\Column;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\App\ResourceConnection;

class PlanOptions implements OptionSourceInterface
{
    private ResourceConnection $resource;

    public function __construct(ResourceConnection $resource)
    {
        $this->resource = $resource;
    }

    public function toOptionArray(): array
    {
        $connection = $this->resource->getConnection();
        $table = $this->resource->getTableName(
            'lofmp_marketplace_membership_transaction'
        );

        $select = $connection->select()
            ->from($table, ['package'])
            ->group('package')
            ->order('package ASC');

        $options = [];
        foreach ($connection->fetchCol($select) as $package) {
            if ($package === null || $package === '') {
                continue;
            }

            $options[] = [
                'value' => $package,
                'label' => $package
            ];
        }
        
        return $options;
    }
}
