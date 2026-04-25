<?php
namespace TCGCollective\ProductAttributesLink\Ui\DataProvider\Form;
 
use TCGCollective\ProductAttributesLink\Model\ResourceModel\CardGamesSealedProducts\CollectionFactory;
 
class CardGamesSealedProductsDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
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

        $this->_loadedData = [];

        $this->collection->getSelect()->joinLeft(
            ['sets' => 'marketplace_card_games_sets'],
            'sets.card_release=main_table.card_release',
            ['card_game' => 'sets.card_game']
        )->group('main_table.entity_id');

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->request = $objectManager->get(\Magento\Framework\App\Request\Http::class);

        $setId = $this->request->getParam('card_game');

        $items = $this->collection->getItems();
        if ($items) {
            foreach ($items as $mapping) {
                $data = $mapping->getData();
                if ($setId) {
                    $data['card_game'] = $setId;
                }
                $this->_loadedData[$mapping->getId()] = $data;
            }
        }

        if (!$this->_loadedData) {
            $this->_loadedData['']['card_game'] = $setId;
        }

        return $this->_loadedData;
    }
}