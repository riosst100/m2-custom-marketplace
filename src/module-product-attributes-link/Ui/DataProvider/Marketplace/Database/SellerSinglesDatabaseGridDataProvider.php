<?php

namespace TCGCollective\ProductAttributesLink\Ui\DataProvider\Marketplace\Database;

use \Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory;
use TCGCollective\ProductAttributesLink\Helper\SetsReleaseHelper;

class SellerSinglesDatabaseGridDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * Product link collection
     *
     * @var \TCGCollective\ProductAttributesLink\Model\ResourceModel\CardGamesMapping\CollectionFactory
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
     * @var MappingHelper
     */
    protected $setsReleaseHelper;

    /**
     * SellerDataProvider constructor.
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param MappingHelper $setsReleaseHelper
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
        SetsReleaseHelper $setsReleaseHelper,
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
        $this->setsReleaseHelper = $setsReleaseHelper;

        // $this->collection = $this->setsReleaseHelper->filterSetsReleaseCollection('singles', $this->collection);
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
