<?php
namespace TCGCollective\ProductAttributesLink\Model\Source\SportCard;

use Magento\Framework\Option\ArrayInterface;
use TCGCollective\ProductAttributesLink\Model\SportCardsSetsFactory as ModelFactory;
use Psr\Log\LoggerInterface as Logger;

class Release implements ArrayInterface
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
        }

        return $this->options;
    }
}