<?php
namespace TCGCollective\ProductAttributesLink\Ui\DataProvider\Form;
 
use TCGCollective\ProductAttributesLink\Model\ResourceModel\CardGamesSetsCard\CollectionFactory;
 
class CardGamesCardDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
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

        $this->collection->getSelect()->joinLeft(
            ['sets' => 'marketplace_card_games_sets'],
            'sets.entity_id=main_table.set_id',
            ['card_game' => 'sets.card_game','card_release' => 'sets.card_release','language_version' => 'sets.language_version']
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

        $this->_loadedData = [];

        

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    $this->request = $objectManager->get(\Magento\Framework\App\Request\Http::class);

    $setId = $this->request->getParam('set_id');

        $items = $this->collection->getItems();
        if ($items) {
            foreach ($items as $mapping) {
                $data = $mapping->getData();
                 // die('ok'.$cardGameAttrId);
                if ($setId) {
                    $data['set_id'] = $setId;
                }
                $this->_loadedData[$mapping->getId()] = $data;
            }
        } 
        // else {
        //     $data = [];
        //     if ($setId) {
        //         $data['entity_id'] = 1;
        //         $data['set_id'] = $setId;
        //     }
        //     $this->_loadedData[1] = $data;

        //     // dd($this->_loadedData);

        //     return $this->_loadedData;
        // }
        if (!$this->_loadedData) {
            $this->_loadedData['']['set_id'] = $setId;
        }

        return $this->_loadedData;
    }
}