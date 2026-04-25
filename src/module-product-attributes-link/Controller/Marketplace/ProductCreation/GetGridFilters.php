<?php
namespace TCGCollective\ProductAttributesLink\Controller\Marketplace\ProductCreation;

use Magento\Framework\App\Action\Context;

class GetGridFilters extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $jsonFactory;

    /**
     * @var \TCGCollective\ProductAttributesLink\Helper\SetsReleaseHelper
     */
    protected $setsReleaseHelper;

    /**
     * Save constructor.
     * @param Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
     * @param \TCGCollective\ProductAttributesLink\Helper\SetsReleaseHelper $setsReleaseHelper
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \TCGCollective\ProductAttributesLink\Helper\SetsReleaseHelper $setsReleaseHelper
    ) {
        $this->jsonFactory = $jsonFactory;
        $this->setsReleaseHelper = $setsReleaseHelper;
        parent::__construct(
            $context
        );
    }
    
	public function execute()
	{
        $resultJson = $this->jsonFactory->create();
        $resultJson->setHttpResponseCode(200);

        $filters = $this->getRequest()->getParam('filter_codes');
        
        $data = $this->setsReleaseHelper->getProductCreationGridFilters($filters);
        $resultJson->setData([
            'success' => true,
            'data' => $data
        ]);

        return $resultJson;
    }
}