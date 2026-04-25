<?php
namespace TCGCollective\ProductAttributesLink\Model\Source\Trains;

use Magento\Framework\Option\ArrayInterface;
use TCGCollective\ProductAttributesLink\Helper\Data;
use Psr\Log\LoggerInterface as Logger;

class RailwayBrand implements ArrayInterface
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
     * @var Data
     */
    private $helper;

    /**
     * @param Logger $logger
     * @param Data $helper
     */
    public function __construct(
        Logger $logger,
        Data $helper
    ) {
        $this->logger = $logger;
        $this->helper = $helper;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        if (!$this->options) {
            $this->options = [];
            
            try {
                $attrOptions = $this->helper->getAttributeOptions('railway_brand');
                foreach ($attrOptions as $option) {
                    $this->options[] = [
                        'label' => $option->getLabel(),
                        'value' => $option->getValue()
                    ];
                }
            } catch (\Exception $exception) {
                $this->logger->critical($exception->getMessage());
            }
        }

        return $this->options;
    }
}