<?php

namespace TCGCollective\MarketplaceProductImportExport\Ui\DataProvider;

use TCGCollective\MarketplaceProductImportExport\Model\ResourceModel\ImportExportTemplate\CollectionFactory;

class ImportExportTemplateGridDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * Product link collection
     *
     * @var \TCGCollective\MarketplaceProductImportExport\Model\ResourceModel\ImportExportTemplate\CollectionFactory
     */
    protected $collection;

    /**
     * @var \Magento\Ui\DataProvider\AddFieldToCollectionInterface[]
     */
    protected $addFieldStrategies;

    /**
     * @var \Magento\Ui\DataProvider\AddFilterToCollectionInterface[]
     */
    protected $addFilterStrategies;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute
     */
    protected $eavAttribute;

    /**
     * SellerDataProvider constructor.
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute $eavAttribute
     * @param array $addFieldStrategies
     * @param array $addFilterStrategies
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute $eavAttribute,
        array $addFieldStrategies = [],
        array $addFilterStrategies = [],
        array $meta = [],
        array $data = []
    ) {
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $meta,
            $data
        );
        $this->collection = $collectionFactory->create();
        $this->addFieldStrategies = $addFieldStrategies;
        $this->addFilterStrategies = $addFilterStrategies;
        $this->eavAttribute = $eavAttribute;

        $categoryNameAttributeId = $this->getAttributeIdByCode(\Magento\Catalog\Model\Category::ENTITY, 'name');

        // $this->collection->getSelect()->columns(
        //     ['attribute' => "main_table.attribute_code"]
        // );
        
        // $this->collection->addFieldToFilter('main_table.attribute_code', ['neq' => null]);
        // $this->collection->getSelect()->joinLeft(
        //     ['mpcag' => 'marketplace_product_configurable_attributes_group'],
        //     'mpcag.entity_id=main_table.group_id',
        //     ['group' => 'mpcag.name']
        // );
        // $this->collection->getSelect()->joinLeft(
        //     ['eas' => 'eav_attribute_set'],
        //     'eas.attribute_set_id=main_table.target_attribute_set_id',
        //     ['target_attribute_set' => 'eas.attribute_set_name']
        // );
        // $this->collection->getSelect()->joinLeft(
        //     ['ea' => 'eav_attribute'],
        //     'ea.attribute_code=main_table.attribute_code',
        //     ['attribute_name' => 'ea.frontend_label']
        // );
        $this->collection->getSelect()->joinLeft(
            ['eaov' => 'eav_attribute_option_value'],
            'eaov.option_id=main_table.attribute_option_id',
            ['attribute_option' => new \Zend_Db_Expr("COALESCE(eaov.value, '-')")]
        );

        $this->collection->getSelect()->joinLeft(
            ['ccev' => 'catalog_category_entity_varchar'], // Replace 'category_table' with the actual category table name
            'ccev.entity_id=main_table.category_id',
            ['category' => new \Zend_Db_Expr("COALESCE(ccev.value, '-')")]
        )->group('main_table.entity_id');

        // dd($this->collection->getSelect()->__toString());
        
        $this->collection->setOrder('main_table.entity_id', 'ASC');
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        if (!$this->getCollection()->isLoaded()) {
            $this->getCollection()->load();
        }
        
        $items = $this->getCollection()->toArray();

        // dd($items);

        return [
            'totalRecords' => $this->getCollection()->getSize(),
            'items' => array_values($items['items']),
        ];
    }

    public function getAttributeIdByCode($entity, $code) 
    {
        return $this->eavAttribute->getIdByCode(
            $entity,
            $code
        );
    }

    /**
     * Add field to select
     *
     * @param string|array $field
     * @param string|null $alias
     * @return void
     */
    public function addField($field, $alias = null)
    {
        if (isset($this->addFieldStrategies[$field])) {
            $this->addFieldStrategies[$field]->addField($this->getCollection(), $field, $alias);
        } else {
            parent::addField($field, $alias);
        }
    }

    /**
     * @param \Magento\Framework\Api\Filter $filter
     */
    public function addFilter(\Magento\Framework\Api\Filter $filter)
    {
        if (isset($this->addFilterStrategies[$filter->getField()])) {
            $this->addFilterStrategies[$filter->getField()]
                ->addFilter(
                    $this->getCollection(),
                    $filter->getField(),
                    [$filter->getConditionType() => $filter->getValue()]
                );
        } else {
            parent::addFilter($filter);
        }
    }
}
