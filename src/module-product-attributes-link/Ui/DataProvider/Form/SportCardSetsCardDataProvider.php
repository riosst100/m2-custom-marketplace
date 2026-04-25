<?php

namespace TCGCollective\ProductAttributesLink\Ui\DataProvider\Form;

use TCGCollective\ProductAttributesLink\Model\ResourceModel\SportCardsSetsCard\CollectionFactory;
    
class SportCardSetsCardDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
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

        $this->collection->getSelect()->joinLeft(
            ['sets' => 'marketplace_sport_cards_sets'],
            'sets.entity_id=main_table.set_id',
            [
                'sc_categories' => 'sets.sc_categories',
                'sc_product_type' => 'sets.sc_product_type',
                'sc_release' => 'sets.sc_release',
                'sc_release_jp' => 'sets.sc_release_jp',
                'sc_league' => 'sets.sc_league',
                'sc_set_type' => 'sets.sc_set_type',
                'sc_set_name' => 'sets.sc_set_name',
                'sc_brand' => 'sets.sc_brand',
                'sc_brand_line' => 'sets.sc_brand_line',
                'release_date' => 'sets.release_date',
                'release_year' => 'sets.release_year',
                'sc_card_manufacturer' => 'sets.sc_card_manufacturer',
                'language' => 'sets.language',
                'bar_code' => 'sets.bar_code',
                'bar_code_no' => 'sets.bar_code_no',
                'age_level' => 'sets.age_level',
                'number_of_cards' => 'sets.number_of_cards'
            ]
        );
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