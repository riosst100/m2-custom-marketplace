<?php

namespace TCGCollective\MarketPlace\Controller\Marketplace\Payment;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;

class Activate extends Action
{
    protected $resultJsonFactory;
    protected $session;
    protected $sellerFactory;
    protected $paymentHelper;

    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        \Magento\Customer\Model\Session $session,
        \Lof\MarketPlace\Model\SellerFactory $sellerFactory,
        \TCGCollective\MarketPlace\Helper\Payments $paymentHelper
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->session = $session;
        $this->sellerFactory = $sellerFactory;
        $this->paymentHelper = $paymentHelper;
    }

    public function execute()
    {
        $confirmed = $this->getRequest()->getParam('confirmed');
        if ($confirmed) {
            $method  = $this->getRequest()->getParam('id');

            $customerId = $this->session->getId();
            $seller = $this->sellerFactory->create()->load($customerId, 'customer_id');
            if ($seller->getRegistrationStep() != "finish") {
                $seller->setRegistrationStep('membership')
                ->setNextStep('membership')
                ->save();
            }

            $this->paymentHelper->updatePaymentMethodStatus($method, 1);

            $this->messageManager->addSuccessMessage(__('Payment has been activated.'));
            return $this->_redirect('lofmpsellermembership/membership/index');
        }

        $result = $this->resultJsonFactory->create();

        $method  = $this->getRequest()->getParam('method');

        $html = $this->_view->getLayout()
        ->createBlock(\TCGCollective\MarketPlace\Block\Payment\Activate::class)
        ->setTemplate('TCGCollective_MarketPlace::payment/activate.phtml')
        ->setData('method', $method)
        ->toHtml();

        // TODO: ambil collection sesuai filter
        // Contoh dummy response
        $data = [
            'success' => true,
            'html' => $html
        ];

        return $result->setData($data);
    }
}