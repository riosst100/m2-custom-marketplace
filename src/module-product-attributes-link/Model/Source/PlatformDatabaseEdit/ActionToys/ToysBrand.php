<?php
namespace TCGCollective\ProductAttributesLink\Model\Source\PlatformDatabaseEdit\ActionToys;

use Magento\Framework\Option\ArrayInterface;
use TCGCollective\ProductAttributesLink\Helper\Data;
use Psr\Log\LoggerInterface as Logger;

class ToysBrand implements ArrayInterface
{
    protected $request;


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
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    $this->request = $objectManager->get(\Magento\Framework\App\Request\Http::class);

        if (!$this->options) {
            $this->options = [];

            $cardGame = $this->request->getParam('toys_brand') ?? null;
            
            try {
                $attrOptions = $this->helper->getAttributeOptions('toys_brand');
                foreach ($attrOptions as $option) {
                    if (!$cardGame || $cardGame && $option->getValue() == $cardGame) {
                        $this->options[] = [
                            'label' => $option->getLabel(),
                            'value' => $option->getValue()
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