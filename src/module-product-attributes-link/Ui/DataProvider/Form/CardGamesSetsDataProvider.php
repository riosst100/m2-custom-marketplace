<?php
namespace TCGCollective\ProductAttributesLink\Ui\DataProvider\Form;
 
use TCGCollective\ProductAttributesLink\Model\ResourceModel\CardGamesSets\CollectionFactory;
 
class CardGamesSetsDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
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
        \Magento\Framework\App\RequestInterface $request,
        array $meta = [],
        array $data = []
    ) {
        $this->request = $request;
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

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->request = $objectManager->get(\Magento\Framework\App\Request\Http::class);

        $setId = $this->request->getParam('card_game');

        $items = $this->collection->getItems();
        if ($items) {
            foreach ($items as $mapping) {
                $data = $mapping->getData();
                 // die('ok'.$cardGameAttrId);
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
        // if (isset($this->_loadedData)) {
        //     return $this->_loadedData;
        // }
        // // $ispopup = 0;
        // // if ($this->request->getParam('popup')) {
        // //     $ispopup = 1;
        // // }
        // $items = $this->collection->getItems();

        // // dd($items)
        // if ($items) {
        //     foreach ($items as $data) {
        //         $dataList = $data->getData();
        //         // $dataList['popup'] = 1;
        //         $this->_loadedData[$data->getId()] = $dataList;
        //     }
        //     // dd($this->_loadedData);
        //     return $this->_loadedData;
        // }

        // return null;
    }
}