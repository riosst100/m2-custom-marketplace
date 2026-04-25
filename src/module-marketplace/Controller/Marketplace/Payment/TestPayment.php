<?php

namespace TCGCollective\MarketPlace\Controller\Marketplace\Payment;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;

class TestPayment extends Action
{
    protected $resultJsonFactory;
    protected $paymentHelper;

    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        \TCGCollective\MarketPlace\Helper\Payments $paymentHelper
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->paymentHelper = $paymentHelper;
    }

    public function execute()
    {
        $result = $this->resultJsonFactory->create();

        $message = '';

        $method  = $this->getRequest()->getParam('payment_method');

        $testPaymentResult = $this->paymentHelper->testPayment(
            $this->getRequest()->getParams()
        );

        // dd($testPaymentResult);

        if ($testPaymentResult) {
            $html = $this->_view->getLayout()
            ->createBlock(\TCGCollective\MarketPlace\Block\Payment\Activate::class)
            ->setTemplate('TCGCollective_MarketPlace::payment/activate.phtml')
            ->setData('method', $method)
            ->toHtml();
            
            $data = [
                'success' => true,
                'data' => [
                    'redirect_url' => $testPaymentResult['redirect_url'] ?? null
                ],
                'error_message' => $testPaymentResult['error_message'] ?? null,
                'success_message' => $testPaymentResult['success_message'] ?? null,
                'html' => $html
            ];

            return $result->setData($data);
        }

        return $result->setData([
            'success' => false,
            'error_message' => $message,
            'html' => ''
        ]);
    }
}