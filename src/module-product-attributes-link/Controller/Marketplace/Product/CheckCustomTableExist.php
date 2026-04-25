<?php
namespace TCGCollective\ProductAttributesLink\Controller\Marketplace\Product;

use Magento\Framework\App\Action\Context;

class CheckCustomTableExist extends \Magento\Framework\App\Action\Action
{
    protected $jsonFactory;
    protected $platformDbHelper;
    
    public function __construct(
        Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \TCGCollective\PlatformDatabase\Helper\Data $platformDbHelper
    ) {
        $this->jsonFactory = $jsonFactory;
        $this->platformDbHelper = $platformDbHelper;
        parent::__construct(
            $context
        );
    }
    
	public function execute()
	{
        $resultJson = $this->jsonFactory->create();
        $resultJson->setHttpResponseCode(200);

        $resultJson->setData([
            'success' => false,
            'data' => []
        ]);

        $data = $this->platformDbHelper->getProductCreationCustomTableType();
        if ($data) {
            $resultJson->setData([
                'success' => true,
                'data' => $data
            ]);
        }

        return $resultJson;
    }
}