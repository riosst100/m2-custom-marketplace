<?php

namespace TCGCollective\MarketPlace\Ui\DataProvider\Catalog;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Api\Filter;

class SinglesProductDataProvider extends \Magento\Catalog\Ui\DataProvider\Product\ProductDataProvider
{
    protected $helper;
    protected $request;

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
        $newItems = [];

        $isAjax = $this->helper->getParam('isAjax');
        if ($isAjax) {
            $filters = $this->helper->getParam('filters');
            // if (isset($filters['card_game'])) {
            if ($filters) {
                $items = $this->helper->getMyProductsDataProvider($this->getCollection(), 'singles');
                $newItems = $this->helper->removeMyProductsDataProviderPrefix($items);
            }
        }

        return [
            'totalRecords' => count($newItems),
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
