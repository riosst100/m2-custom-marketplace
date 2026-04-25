<?php

namespace TCGCollective\MarketPlace\Controller\Marketplace\Payment\Simulation;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;

class AccountUpdatedWebhook extends Action
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

        $method  = $this->getRequest()->getParam('payment_method');
        $status  = $this->getRequest()->getParam('status');
        $accountId  = $this->getRequest()->getParam('account_id');

        $accountUpdatedWebhookSimulationResult = $this->paymentHelper->xenditAccountUpdatedWebhookSimulation(
            $method,
            $status,
            $accountId
        );

        if ($accountUpdatedWebhookSimulationResult) {
            $html = $this->_view->getLayout()
            ->createBlock(\TCGCollective\MarketPlace\Block\Payment\Activate::class)
            ->setTemplate('TCGCollective_MarketPlace::payment/activate.phtml')
            ->setData('method', $method)
            ->toHtml();
            
            $data = [
                'success' => true,
                'error_message' => $accountUpdatedWebhookSimulationResult['error_message'] ?? null,
                'success_message' => $accountUpdatedWebhookSimulationResult['success_message'] ?? null,
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