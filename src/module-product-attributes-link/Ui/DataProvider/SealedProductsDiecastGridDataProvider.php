<?php

namespace TCGCollective\ProductAttributesLink\Ui\DataProvider;

use \Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory;
use TCGCollective\ProductAttributesLink\Helper\Data as MappingHelper;

class SealedProductsDiecastGridDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
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
     * @var MappingHelper
     */
    protected $helper;

    /**
     * SellerDataProvider constructor.
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param MappingHelper $helper
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
        MappingHelper $helper,
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
        $this->helper = $helper;
        
        $this->collection->getSelect()->join(
            ['eav_attribute' => 'eav_attribute'],
            'eav_attribute.attribute_id = main_table.attribute_id',
            []
        )->where('eav_attribute.attribute_code = "diecast_brand"');

        $this->collection->getSelect()->join(
            ['eaov' => 'eav_attribute_option_value'],
            'main_table.option_id = eaov.option_id',
            ['diecast_brand' => 'eaov.value']
        );

        $this->collection->addFilterToMap('diecast_brand', 'eaov.value');
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

        $result = $this->getCollection()->toArray();

        $items = $result['items'] ?? [];

        usort($items, fn($a, $b) => $a['diecast_brand'] <=> $b['diecast_brand']);

        return [
            'totalRecords' => $result['totalRecords'],
            'items' => array_values($items),
        ];
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
