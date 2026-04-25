<?php
namespace TCGCollective\ProductAttributesLink\Model\Source;

use Magento\Framework\Option\ArrayInterface;
use TCGCollective\ProductAttributesLink\Model\ResourceModel\CardGamesSealedProducts\CollectionFactory;
use Psr\Log\LoggerInterface as Logger;

class SealedProductsCardRelease implements ArrayInterface
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

            $card_game = $this->request->getParam('card_game');
            // die('ok'.$card_game);
            if ($card_game) {
                $collection = $this->collectionFactory->create();
                $collection->addFieldToFilter('card_game', $card_game);
                $collection->getSelect()->group('card_release');
                // dd($collection->getSelect()->__toString());
                // dd($collection->getData());
                if ($collection->getSize()) {
                    foreach ($collection as $model) {
                        $this->options[] = [
                            'label' => $model->getData('card_release'),
                            'value' => $model->getData('card_release')
                        ];
                    }
                }
            }
        }

        return $this->options;
    }
}