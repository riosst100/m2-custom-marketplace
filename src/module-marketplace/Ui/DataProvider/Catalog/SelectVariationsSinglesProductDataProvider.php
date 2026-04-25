<?php

namespace TCGCollective\MarketPlace\Ui\DataProvider\Catalog;

use Lof\MarketPlace\Model\ResourceModel\SellerProduct\Grid\CollectionFactory;

class SelectVariationsSinglesProductDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    protected $helper;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        \TCGCollective\MarketPlace\Helper\Catalog $helper,
        array $addFieldStrategies = [],
        array $addFilterStrategies = [],
        array $meta = [],
        array $data = []
    ) {
        $this->helper = $helper;
        
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $addFieldStrategies,
            $addFilterStrategies,
            $meta,
            $data
        );

        $this->collection = $collectionFactory->create();
        $this->addFieldStrategies = $addFieldStrategies;
        $this->addFilterStrategies = $addFilterStrategies;

        $this->helper->filterSelectVariationsSellerCatalogProductDataProvider($this->collection, 'singles');
    }

    public function addFilter(\Magento\Framework\Api\Filter $filter) 
    {
        if ($this->helper->customAddFilter($this->getCollection(), $filter)) {
            return $this;
        }

        return parent::addFilter($filter);
    }
}
