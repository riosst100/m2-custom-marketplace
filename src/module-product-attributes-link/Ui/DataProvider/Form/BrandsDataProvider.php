<?php
namespace TCGCollective\ProductAttributesLink\Ui\DataProvider\Form;
 
use TCGCollective\ProductAttributesLink\Model\ResourceModel\Brands\CollectionFactory;
 
class BrandsDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
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

        // $this->collection->getSelect()->joinLeft(
        //     ['mpalg' => 'marketplace_product_attributes_link_group'],
        //     'mpalg.entity_id=main_table.group_id',
        //     ['group_name' => 'mpalg.name']
        // );

        $items = $this->collection->getItems();
        if ($items) {
            foreach ($items as $mapping) {
                // $mapping->setEntityType($mapping->getCategoryId() ? 'category' : 'attribute');

                // $mapping->setGroupId($mapping->getGroupName());

                $data = $mapping->getData();
                if ($data) {
                    foreach($data as $index => $val) {
                        if (in_array($index, ["product_line","series","scale","character","brands","franchise","category_ids"])) {
                            $newVal = $val ? explode(",", $val) : [];
                            if (!is_array($newVal)) {
                                $newVal = [$newVal];
                            }

                            $data[$index] = $newVal;
                        }
                    }
                }

                $this->_loadedData[$mapping->getId()] = $data;
            }

            return $this->_loadedData;
        }

        return null;
    }
}