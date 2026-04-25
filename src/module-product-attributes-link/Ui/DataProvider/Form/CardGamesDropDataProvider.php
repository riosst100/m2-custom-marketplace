<?php
namespace TCGCollective\ProductAttributesLink\Ui\DataProvider\Form;
 
use TCGCollective\ProductAttributesLink\Model\ResourceModel\CardGamesDrop\CollectionFactory;
 
class CardGamesDropDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    protected $collection;
    protected $_loadedData;
    protected $request;


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

        // $this->collection->getSelect()->joinLeft(
        //     ['sets' => 'marketplace_card_games_sets'],
        //     'sets.entity_id=main_table.set_id',
        //     ['card_game' => 'sets.card_game']
        // );

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->request = $objectManager->get(\Magento\Framework\App\Request\Http::class);

        $card_game = $this->request->getParam('card_game');

        $items = $this->collection->getItems();
        if ($items) {
            foreach ($items as $mapping) {
                $data = $mapping->getData();
                 // die('ok'.$cardGameAttrId);
                if ($card_game) {
                    $data['card_game'] = $card_game;
                }
                $this->_loadedData[$mapping->getId()] = $data;
            }
        }

        if (!$this->_loadedData) {
            // $this->_loadedData[''] = '';
            $this->_loadedData['']['card_game'] = $card_game;
        }

        return $this->_loadedData;
    }
}