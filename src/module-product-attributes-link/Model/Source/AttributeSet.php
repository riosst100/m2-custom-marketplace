<?php
namespace TCGCollective\ProductAttributesLink\Model\Source;

use Magento\Framework\Option\ArrayInterface;
use TCGCollective\ProductAttributesLink\Helper\Data;
use Psr\Log\LoggerInterface as Logger;

class AttributeSet implements ArrayInterface
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
                $attributeSets = $this->helper->getAllAttributeSet();
                foreach ($attributeSets as $attributeset) {
                    $this->options[] = [
                        'label' => $attributeset['label'],
                        'value' => $attributeset['value']
                    ];
                }
            } catch (\Exception $exception) {
                $this->logger->critical($exception->getMessage());
            }

            if (count($this->options)) {
                usort($this->options, fn($a, $b) => $a['label'] <=> $b['label']);
            }
        }

        return $this->options;
    }
}