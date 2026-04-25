<?php
namespace TCGCollective\ProductAttributesLink\Model\Source\CardGame;

use Magento\Framework\Option\ArrayInterface;
use TCGCollective\ProductAttributesLink\Model\ResourceModel\CardGamesSets\CollectionFactory;
use Psr\Log\LoggerInterface as Logger;

class CardSet implements ArrayInterface
{
    protected $request;


    /**
     * @var array
     */
    private $options;

    /**
     * @var Logger
     */
    private $logger;
    
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @param Logger $logger
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        Logger $logger,
        CollectionFactory $collectionFactory
    ) {
        $this->logger = $logger;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    $this->request = $objectManager->get(\Magento\Framework\App\Request\Http::class);

        if (!$this->options) {
            $this->options = [];

            $setId = $this->request->getParam('set_id');
            // die('ok'.$cardGameAttrId);
            if ($setId) {
                $collection = $this->collectionFactory->create();
                $collection->addFieldToFilter('entity_id', $setId);
                if ($collection->getSize()) {
                    foreach ($collection as $model) {
                        $this->options[] = [
                            'label' => $model->getCardSet(),
                            'value' => $model->getEntityId()
                        ];
                    }
                }
            }
        }

        return $this->options;
    }
}