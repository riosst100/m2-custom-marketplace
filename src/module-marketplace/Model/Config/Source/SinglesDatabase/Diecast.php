<?php

namespace TCGCollective\MarketPlace\Model\Config\Source\SinglesDatabase;

use Magento\Framework\Data\OptionSourceInterface;
use TCGCollective\ProductAttributesLink\Helper\Data;
use Psr\Log\LoggerInterface as Logger;

class Diecast implements OptionSourceInterface
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
            // $this->options = [
            //     [
            //         'label' => '',
            //         'value' => ''
            //     ]
            // ];

            $items = [];
            
            try {
                $options = $this->helper->getAttributeOptions('diecast_product_type', false);
                foreach ($options as $option) {
                    $items[] = [
                        'label' => $option->getLabel(),
                        'value' => $option->getValue()
                    ];
                }

                if ($items) {
                    $this->options = $items;
                }
            } catch (\Exception $exception) {
                $this->logger->critical($exception->getMessage());
            }
        }

        return $this->options;
    }
}
