<?php
namespace TCGCollective\ProductAttributesLink\Model\Source;

use Magento\Framework\Option\ArrayInterface;
use TCGCollective\ProductAttributesLink\Helper\Data;
use Psr\Log\LoggerInterface as Logger;

class AttributeOptions implements ArrayInterface
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
            $this->options = [
                [
                    'label' => ' ',
                    'value' => ''
                ]
            ];
            
            // try {
            //     $options = $this->helper->getAttributeOptions();
            //     foreach ($options as $option) {
            //         $this->options[] = [
            //             'label' => $options->getLabel(),
            //             'value' => $options->getValue()
            //         ];
            //     }
            // } catch (\Exception $exception) {
            //     $this->logger->critical($exception->getMessage());
            // }
        }

        return $this->options;
    }
}