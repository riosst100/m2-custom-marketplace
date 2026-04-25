<?php

namespace TCGCollective\MarketPlace\Model\SellerGroup;

use Magento\Customer\Model\ResourceModel\Group\CollectionFactory;
use Magento\Ui\DataProvider\AbstractDataProvider;

class DataProvider extends AbstractDataProvider
{
    protected $collection;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create();
        $this->collection->addFieldToFilter('is_seller', 1);
        
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }
}
