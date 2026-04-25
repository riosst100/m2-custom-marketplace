<?php
namespace TCGCollective\ProductAttributesLink\Model\Source\Generic;

use Magento\Framework\Option\ArrayInterface;
use TCGCollective\ProductAttributesLink\Helper\Data;
use Psr\Log\LoggerInterface as Logger;

class Language implements ArrayInterface
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
                $attrOptions = $this->helper->getAttributeOptions('language');
                foreach ($attrOptions as $option) {
                    if ($option->getLabel() && $option->getLabel() != ' ') {
                        $this->options[] = [
                            'label' => $option->getLabel(),
                            'value' => $option->getLabel()
                        ];
                    }
                }
            } catch (\Exception $exception) {
                $this->logger->critical($exception->getMessage());
            }
        }

        return $this->options;
    }
}