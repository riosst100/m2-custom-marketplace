<?php

namespace TCGCollective\MarketPlace\Controller\Marketplace\Payment;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;

class CreateAccount extends Action
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

        $refererUrl = $this->_redirect->getRefererUrl();

        $email = $this->getRequest()->getParam('email');
        if (!$email) {
            $message = __('Please enter your email address.');
            return $result->setData([
                'success' => false,
                'error_message' => $message,
                'html' => ''
            ]);
        }

        $business_name = $this->getRequest()->getParam('business_name');
        if (!$business_name) {
            $message = __('Please enter your business name.');
            return $result->setData([
                'success' => false,
                'error_message' => $message,
                'html' => ''
            ]);
        }

        $paymentMethod = $this->getRequest()->getParam('payment_method');
        if (!$paymentMethod) {
            $message = __('Please select payment method to activate.');
            return $result->setData([
                'success' => false,
                'error_message' => $message,
                'html' => ''
            ]);
        }

        $createAccountResult = $this->paymentHelper->createPaymentAccount(
            $paymentMethod, 
            $email, 
            $business_name
        );

        // dd($createAccountResult);

        if ($createAccountResult) {
            $html = $this->_view->getLayout()
            ->createBlock(\TCGCollective\MarketPlace\Block\Payment\Activate::class)
            ->setTemplate('TCGCollective_MarketPlace::payment/activate.phtml')
            ->setData('method', $method)
            ->toHtml();

            // TODO: ambil collection sesuai filter
            // Contoh dummy response
            $data = [
                'success' => true,
                'error_message' => $createAccountResult['error_message'] ?? null,
                'success_message' => $createAccountResult['success_message'] ?? null,
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
