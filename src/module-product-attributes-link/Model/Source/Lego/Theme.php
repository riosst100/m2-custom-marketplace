<?php
namespace TCGCollective\ProductAttributesLink\Model\Source\Lego;

use Magento\Framework\Option\ArrayInterface;
use TCGCollective\ProductAttributesLink\Helper\Data;
use Psr\Log\LoggerInterface as Logger;

class Theme implements ArrayInterface
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
            
            try {
                $options = $this->helper->getAttributeOptions('lego_theme');
                foreach ($options as $option) {
                    if ($option->getLabel()) {
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