<?php

namespace TCGCollective\MarketPlace\Block\Payment;

class Activate extends \Magento\Framework\View\Element\Template
{
    private $paymentHelper;
    protected $scopeConfig;
    
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \TCGCollective\MarketPlace\Helper\Payments $paymentHelper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->paymentHelper = $paymentHelper;
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context);
    }

    public function isSandboxMode() 
    {
        $xenditEnv = $this->scopeConfig->getValue('payment/xendit/xendit_env', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if ($xenditEnv == "live") {
            return false;
        }
        return true;
    }

    public function getSeller() {
        return $this->paymentHelper->getSeller();
    }

    public function getPaymentAccount($method)
    {
        if ($method) {
            return $this->paymentHelper->getPaymentAccount($method);
        }

        return false;
    }

    public function getPaymentTestTransaction($method)
    {
        if ($method) {
            return $this->paymentHelper->getPaymentTestTransaction($method);
        }

        return false;
    }
}
