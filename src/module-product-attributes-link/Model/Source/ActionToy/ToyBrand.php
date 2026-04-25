<?php
namespace TCGCollective\ProductAttributesLink\Model\Source\ActionToy;

use Magento\Framework\Option\ArrayInterface;
use TCGCollective\ProductAttributesLink\Model\ActionToySetsFactory as ModelFactory;
use Psr\Log\LoggerInterface as Logger;

class ToyBrand implements ArrayInterface
{
    /**
     * @var array
     */
    private $options;

    /**
     * @var Logger
     */
    private $logger;
    
    /**
     * @var ModelFactory
     */
    private $modelFactory;

    /**
     * @param Logger $logger
     * @param ModelFactory $modelFactory
     */
    public function __construct(
        Logger $logger,
        ModelFactory $modelFactory
    ) {
        $this->logger = $logger;
        $this->modelFactory = $modelFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        if (!$this->options) {
            $this->options = [];
            
            try {
                $collection = $this->modelFactory->create()
                ->getCollection();
                $collection->getSelect()->group('toys_brand')->order('toys_brand DESC');
                foreach ($collection as $model) {
                    $this->options[] = [
                        'label' => $model->getData('toys_brand'),
                        'value' => $model->getData('toys_brand')
                    ];
                }
            } catch (\Exception $exception) {
                $this->logger->critical($exception->getMessage());
            }
        }

        return $this->options;
    }
}