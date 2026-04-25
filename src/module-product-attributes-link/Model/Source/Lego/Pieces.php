<?php
namespace TCGCollective\ProductAttributesLink\Model\Source\Lego;

use Magento\Framework\Option\ArrayInterface;
use TCGCollective\ProductAttributesLink\Helper\Data;
use Psr\Log\LoggerInterface as Logger;

class Pieces implements ArrayInterface
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
                $options = $this->helper->getAttributeOptions('number_of_pieces');
                foreach ($options as $option) {
                    $this->options[] = [
                        'label' => $option->getLabel(),
                        'value' => $option->getLabel()
                    ];
                }
            } catch (\Exception $exception) {
                $this->logger->critical($exception->getMessage());
            }
        }

        return $this->options;
    }
}