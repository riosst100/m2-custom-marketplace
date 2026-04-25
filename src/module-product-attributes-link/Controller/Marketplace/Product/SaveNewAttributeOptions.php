<?php
namespace TCGCollective\ProductAttributesLink\Controller\Marketplace\Product;

use Magento\Framework\App\Action\Context;

class SaveNewAttributeOptions extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $jsonFactory;

    /**
     * @var \TCGCollective\ProductAttributesLink\Helper\Data
     */
    protected $mappingHelper;

    /**
     * Save constructor.
     * @param Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
     * @param \TCGCollective\ProductAttributesLink\Helper\Data $mappingHelper
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \TCGCollective\ProductAttributesLink\Helper\Data $mappingHelper
    ) {
        $this->jsonFactory = $jsonFactory;
        $this->mappingHelper = $mappingHelper;
        parent::__construct(
            $context
        );
    }
    
	public function execute()
	{
        $resultJson = $this->jsonFactory->create();
        $resultJson->setHttpResponseCode(200);

        $code = $this->getRequest()->getParam('code');
        $val = $this->getRequest()->getParam('val') ? ucwords($this->getRequest()->getParam('val')) : '';

        $data = [
            'options' => null,
            'selected_option' => null
        ];

        if ($code && $val) {
            $optionId = $this->mappingHelper->getAttributeOptionId($code, $val);
            if ($optionId) {
                // dd($optionId);
                $newOptions = [];

                $options = $this->mappingHelper->getAttributeOptions($code);
                if ($options) {
                    // dd($options);
                    foreach($options as $option) {
                        // dd($option);
                        if ($option->getLabel() && $option->getValue()) {
                            $newOptions[] = [
                                'label' => $option->getLabel(),
                                'value' => $option->getValue()
                            ];
                        }
                    }
                }

                $data['selected_option'] = $optionId;
                $data['options'] = $newOptions;
            }
        }

        $resultJson->setData([
            'success' => true, 
            'data' => $data
        ]);

        return $resultJson;
    }
}