<?php
namespace TCGCollective\ProductAttributesLink\Controller\Adminhtml\Form;

class GetSetsOptions extends \TCGCollective\ProductAttributesLink\Controller\Adminhtml\Index
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
     * Save constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
     * @param \TCGCollective\ProductAttributesLink\Helper\Data $mappingHelper
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

        $message = '';

        $data = [];

        $code = $this->getRequest()->getParam('code');
        $value = $this->getRequest()->getParam('val');

        $sets = null;
        if ($code == "card_game") {
            $sets = $this->mappingHelper->getCardGameSets($value);
        }

        // die($value.' '.$code);

        if ($sets) {
            foreach($sets as $model) {
                // if ($model->getCardSet()) {
                    $data[] = [
                        'label' => $model->getCardSet(),
                        'value' => $model->getId(),
                        'path' => ''
                    ];
                // }
            }
        }

        $resultJson->setData(['message' => $message, 'success' => true, 'data' => $data]);
        return $resultJson;
    }
}