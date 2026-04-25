<?php
namespace TCGCollective\ProductAttributesLink\Model\Source\ActionToy;

use Magento\Framework\Option\ArrayInterface;
use TCGCollective\ProductAttributesLink\Model\CustomAttributesFactory as ModelFactory;
use Psr\Log\LoggerInterface as Logger;

class ToysSubLine implements ArrayInterface
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
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @var \TCGCollective\ProductAttributesLink\Helper\Data
     */
    private $mappingHelper;

    /**
     * @param Logger $logger
     * @param ModelFactory $modelFactory
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \TCGCollective\ProductAttributesLink\Helper\Data $mappingHelper
     */
    public function __construct(
        Logger $logger,
        ModelFactory $modelFactory,
        \Magento\Framework\App\RequestInterface $request,
        \TCGCollective\ProductAttributesLink\Helper\Data $mappingHelper
    ) {
        $this->logger = $logger;
        $this->modelFactory = $modelFactory;
        $this->request = $request;
        $this->mappingHelper = $mappingHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        if (!$this->options) {
            $this->options = [];

            $attrCode = 'toys_sub_line';
            $mainAttrCode = 'toys_brand';

            $brand = $this->request->getParam($mainAttrCode);
            $brand = $this->mappingHelper->getOptionLabelById($mainAttrCode, $brand);
            
            try {
                $collection = $this->modelFactory->create()
                ->getCollection()
                ->addFieldToFilter('attribute_code', $attrCode);
                // ->addFieldToFilter('toys_brand', $brand);
                foreach ($collection as $model) {
                    $this->options[] = [
                        'label' => $model->getData('attribute_value'),
                        'value' => $model->getData('attribute_value')
                    ];
                }
            } catch (\Exception $exception) {
                $this->logger->critical($exception->getMessage());
            }
        }

        return $this->options;
    }
}