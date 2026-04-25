<?php
namespace TCGCollective\ProductAttributesLink\Model\Source;

use Magento\Framework\Option\ArrayInterface;
use TCGCollective\ProductAttributesLink\Model\ResourceModel\CardGamesSetsCard\CollectionFactory;
use Psr\Log\LoggerInterface as Logger;

class ReleaseYearSingles implements ArrayInterface
{
    protected $request;
    protected $registry;


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
        $this->registry = $objectManager->create(\Magento\Framework\Registry::class);

        if (!$this->options) {
            $this->options = [];

            $card_game = $this->request->getParam('card_game');
            if ($card_game) {
                $collection = $this->collectionFactory->create();
                $collection->addFieldToFilter('card_game', $card_game);
                $collection->getSelect()->group('release_year');
                if ($collection->getSize()) {
                    foreach ($collection as $model) {
                        $this->options[] = [
                            'label' => $model->getData('release_year'),
                            'value' => $model->getData('release_year')
                        ];
                    }
                }
            }
        }

        return $this->options;
    }
}