<?php
namespace TCGCollective\MarketplaceConfigurableProduct\Model\Source\ConfigurableVariations;

use Magento\Framework\Option\ArrayInterface;
use TCGCollective\MarketplaceConfigurableProduct\Model\ProductConfigurableAttributesGroupFactory;
use Psr\Log\LoggerInterface as Logger;

class Group implements ArrayInterface
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
     * @var ProductConfigurableAttributesGroupFactory
     */
    private $productConfigurableAttributesGroupFactory;

    /**
     * @param Logger $logger
     * @param ProductConfigurableAttributesGroupFactory $productConfigurableAttributesGroupFactory
     */
    public function __construct(
        Logger $logger,
        ProductConfigurableAttributesGroupFactory $productConfigurableAttributesGroupFactory
    ) {
        $this->logger = $logger;
        $this->productConfigurableAttributesGroupFactory = $productConfigurableAttributesGroupFactory;
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
                $options = $this->getAllGroup();
                foreach ($options as $option) {
                    $this->options[] = [
                        'label' => $option->getName(),
                        'value' => $option->getId()
                    ];
                }
            } catch (\Exception $exception) {
                $this->logger->critical($exception->getMessage());
            }
        }

        return $this->options;
    }

    public function getAllGroup() 
    {
        $collection = $this->productConfigurableAttributesGroupFactory->create()
        ->getCollection();
        if ($collection->getSize()) {
            return $collection;
        }

        return null;
    }
}