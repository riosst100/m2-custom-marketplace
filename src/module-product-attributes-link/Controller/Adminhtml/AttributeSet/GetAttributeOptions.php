<?php
namespace TCGCollective\ProductAttributesLink\Controller\Adminhtml\AttributeSet;

class GetAttributeOptions extends \TCGCollective\ProductAttributesLink\Controller\Adminhtml\Index
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $jsonFactory;

    /**
     * @var \TCGCollective\ProductAttributesLink\Helper\Data
     */
    protected $mappingHelper;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * Save constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
     * @param \TCGCollective\ProductAttributesLink\Helper\Data $mappingHelper
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \TCGCollective\ProductAttributesLink\Helper\Data $mappingHelper,
        \Magento\Framework\Registry $registry
    ) {
        $this->jsonFactory = $jsonFactory;
        $this->mappingHelper = $mappingHelper;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct(
            $context,
            $resultPageFactory,
            $registry
        );
    }
    
	public function execute()
	{
        $resultJson = $this->jsonFactory->create();
        $resultJson->setHttpResponseCode(200);

        $data = [];

        $attributeCode = $this->getRequest()->getParam('code');
        if ($attributeCode) {
            $options = $this->mappingHelper->getAttributeOptions($attributeCode);
            if ($options) {
                foreach ($options as $option) {
                    if ($option->getValue()) {
                        $data[] = [
                            'label' => $option->getLabel(),
                            'value' => $option->getValue(),
                            'path' => ''
                        ];
                    }
                }
            }
        }

        $resultJson->setData(['success' => true, 'data' => $data]);
        return $resultJson;
    }
}