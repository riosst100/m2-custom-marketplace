<?php
namespace TCGCollective\ProductAttributesLink\Model\Source\ActionToy;

use Magento\Framework\Option\ArrayInterface;
use TCGCollective\ProductAttributesLink\Model\ActionToySetsFactory as ModelFactory;
use Psr\Log\LoggerInterface as Logger;

class ToyLine implements ArrayInterface
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
                $collection->getSelect()->group('toy_line')->order('toy_line DESC');
                foreach ($collection as $model) {
                    $this->options[] = [
                        'label' => $model->getData('toy_line'),
                        'value' => $model->getData('toy_line')
                    ];
                }
            } catch (\Exception $exception) {
                $this->logger->critical($exception->getMessage());
            }
        }

        return $this->options;
    }
}