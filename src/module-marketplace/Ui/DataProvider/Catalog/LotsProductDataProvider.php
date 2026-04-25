<?php

namespace TCGCollective\MarketPlace\Ui\DataProvider\Catalog;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;

class LotsProductDataProvider extends \Magento\Catalog\Ui\DataProvider\Product\ProductDataProvider
{
    protected $collection;


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
            $collectionFactory,
            $addFieldStrategies,
            $addFilterStrategies,
            $meta,
            $data
        );
    }
    
    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        $collection = $this->getCollection();
        $items = $this->helper->getMyProductsDataProvider($collection, 'lots');
        $newItems = $this->helper->removeMyProductsDataProviderPrefix($items);

        return [
            'totalRecords' => count($items),
            'items' => $newItems,
        ];
    }

    public function addFilter(\Magento\Framework\Api\Filter $filter) 
    {
        if ($this->helper->applyMyProductsDataProviderFilter(
            $this->getCollection(), 
            $filter
        )) {
            return $this;
        }

        return parent::addFilter($filter);
    }
}
