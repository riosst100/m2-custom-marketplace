<?php
namespace TCGCollective\ProductAttributesLink\Ui\DataProvider\Form;
 
use TCGCollective\ProductAttributesLink\Model\ResourceModel\MiniatureGameSets\CollectionFactory;
 
class MiniatureGameSetsDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    protected $collection;


    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create();
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }
 
    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        if (isset($this->_loadedData)) {
            return $this->_loadedData;
        }
        $items = $this->collection->getItems();
        if ($items) {
            foreach ($items as $data) {
                $dataList = $data->getData();
                $this->_loadedData[$data->getId()] = $dataList;
            }
            return $this->_loadedData;
        }

        return null;
    }
}