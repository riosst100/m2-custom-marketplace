<?php

namespace TCGCollective\MarketPlace\Ui\DataProvider;

use Magento\Ui\DataProvider\AbstractDataProvider;
use TCGCollective\MarketPlace\Model\ResourceModel\ProductListingCategoryConfig\CollectionFactory;
use Magento\Eav\Model\Config as EavConfig;

class ProductListingCategoryConfigDataProvider extends AbstractDataProvider
{
    protected $eavConfig;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        EavConfig $eavConfig,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create();
        $this->eavConfig = $eavConfig;

        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $meta,
            $data
        );

        $this->prepareCollection();
    }

    protected function prepareCollection()
    {
        $categoryEntityType = $this->eavConfig->getEntityType('catalog_category');
        $nameAttribute = $this->eavConfig
            ->getAttribute($categoryEntityType->getEntityTypeCode(), 'name');

        $attributeId = (int)$nameAttribute->getAttributeId();

        $this->collection->getSelect()->joinLeft(
            ['category_name_table' => $this->collection->getTable('catalog_category_entity_varchar')],
            'main_table.category_id = category_name_table.entity_id 
             AND category_name_table.attribute_id = ' . $attributeId . '
             AND category_name_table.store_id = 0',
            ['category_name' => 'category_name_table.value']
        );
    }
}