<?php
namespace TCGCollective\MarketplacePaymentAirwallex\Controller\Marketplace\TestPayment;

use Magento\Framework\App\Action\Context;

class Success extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $jsonFactory;
    protected $testingPaymentHelper;

    public function __construct(
        Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \TCGCollective\MarketplacePaymentAirwallex\Helper\TestingPayment $testingPaymentHelper
    ) {
        $this->jsonFactory = $jsonFactory;
        $this->testingPaymentHelper = $testingPaymentHelper;
        parent::__construct(
            $context
        );
    }
    
	public function execute()
	{
        // dd('ok');
        $resultJson = $this->jsonFactory->create();
        $resultJson->setHttpResponseCode(200);

        // $productTypeCode = $this->getRequest()->getParam('code');
        // $productTypeVal = $this->getRequest()->getParam('val');

        // $entityCode = $this->getRequest()->getParam('entity_code');
        // $entityVal = $this->getRequest()->getParam('entity_val');

        // $data = [];

        // $productType = $this->getRequest()->getParam('product_type');
        // $source = $this->getRequest()->getParam('source');
        // // dd($source);
        // if ($productType == "configurable" || $source != "database") {
        // // if ($source != "database") {
        //     $data['is_custom_table_exist'] = false;
        //     $data['is_singles'] = false;
        //     $data['is_sealedproducts'] = false;
        //     $data['is_database'] = false;
        // } else {
        //     $data = $this->mappingHelper->getCustomTableDetails($productTypeCode, $productTypeVal, $entityCode, $entityVal, $productType);
        // }
        $success = false;

        // SELLER_TEST_AIRWALLEX_PAYMENT

        $testResult = $this->testingPaymentHelper->testSuccessPayment();
        dd($testResult);
        if ($testResult) {
            $url = $this->_url->getUrl('checkout', ['_secure' => true]);
            return $this->goBack($url);
        //     $success = true;
        }
        // dd('ok');
        // dd($resultJson);
        

        return $this->goBack();
    }
}