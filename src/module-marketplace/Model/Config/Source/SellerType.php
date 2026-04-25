<?php

namespace TCGCollective\MarketPlace\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;
use Magento\Customer\Model\ResourceModel\Group\CollectionFactory as GroupCollectionFactory;

class SellerType implements ArrayInterface
{
    /**
     * @var GroupCollectionFactory
     */
    protected $groupCollectionFactory;

    public function __construct(
        GroupCollectionFactory $groupCollectionFactory
    ) {
        $this->groupCollectionFactory = $groupCollectionFactory;
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];

        /** Load customer group */
        $groupCollection = $this->groupCollectionFactory->create();

        foreach ($groupCollection as $group) {
            $options[] = [
                'label' => $group->getCustomerGroupCode(),
                'value' => $group->getCustomerGroupId(),
            ];
        }

        return $options;
    }

    /**
     * Key-value array
     */
    public function toArray()
    {
        $result = [];
        foreach ($this->toOptionArray() as $option) {
            $result[$option['value']] = $option['label'];
        }
        return $result;
    }
}
