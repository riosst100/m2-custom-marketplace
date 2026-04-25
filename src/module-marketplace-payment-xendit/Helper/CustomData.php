<?php

namespace TCGCollective\MarketplacePaymentXendit\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\DataObject;

class CustomData extends AbstractHelper
{

    const XML_XENDIT_SUCCESS_URL    = "payment/xendit/success_urls";
    const XML_XENDIT_CART_URL       = "payment/xendit/failure_urls";

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig, 
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Store\Model\StoreManagerInterface $storeManagerInterface,
        \Magento\Quote\Model\QuoteIdToMaskedQuoteIdInterface $quoteIdToMaskedQuoteId,
        \Magento\Framework\Serialize\Serializer\Json $json
    )
    {
        $this->scopeConfig = $scopeConfig;
        $this->json = $json;
        $this->moduleManager = $moduleManager;
        $this->quoteIdToMaskedQuoteId = $quoteIdToMaskedQuoteId;
        $this->storeManagerInterface = $storeManagerInterface;
    }

    public function getPreferredMethod( $payment ){
        $listPayment = [
            "cc"                => "CREDIT_CARD",
            "cc_subscription"   => "CC_SUBSCRIPTION", // redirect to xendit/checkout/redirect
            "bcava"             => "BCA",
            "bniva"             => "BNI",
            "briva"             => "BRI",
            "mandiriva"         => "MANDIRI",
            "permatava"         => "Permata",
            "alfamart"          => "Alfamart",
            "ovo"               => "OVO",
            "dana"              => "DANA",
            "linkaja"           => "LINKAJA",
            "indomaret"         => "Indomaret",
            "qr_codes"          => "QRCODES",
            "dd_bri"            => "DD_BRI",
            "kredivo"           => "KREDIVO"
        ];

        $response = FALSE;
        if (!!array_key_exists($payment, $listPayment)) {
            $response = $listPayment[$payment];
        }

        return $response; 
    }

    public function successUrl($origin = 'web', $orderId = null)
    {
        $urlConfigs = $this->scopeConfig->getValue(self::XML_XENDIT_SUCCESS_URL, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        if (empty($urlConfigs)) {
            return null;
        }

        try {
            $urlConfigs = $this->json->unserialize($urlConfigs);
        } catch (\Throwable $th) {
            return null;
        }

        if (is_array($urlConfigs) && count($urlConfigs) > 0) {
            foreach ($urlConfigs as $config) {
                if (
                    $config['origin'] == $origin &&
                    filter_var($config['url'], FILTER_VALIDATE_URL)
                ) {
                    $url = $config['url'];
                    if ((bool) (int) $config['param']) {
                        $url .= '?orderId=' . $orderId;
                    }
                    return $url;
                }
            }
        }
    }

    public function cartUrl($origin = 'web', $orderId = null , $quoteId = "")
    {
        $urlConfigs = $this->scopeConfig->getValue(self::XML_XENDIT_CART_URL, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $cartId = "";
        if(!empty($quoteId)){
            try {
                $cartId = $this->quoteIdToMaskedQuoteId->execute($quoteId);
            } catch (NoSuchEntityException $exception) {
                $cartId = "";
            }
        }

        if (empty($urlConfigs)) {
            return null;
        }

        try {
            $urlConfigs = $this->json->unserialize($urlConfigs);
        } catch (\Throwable $th) {
            return null;
        }

        if (is_array($urlConfigs) && count($urlConfigs) > 0) {
            foreach ($urlConfigs as $config) {
                if (
                    $config['origin'] == $origin &&
                    filter_var($config['url'], FILTER_VALIDATE_URL)
                ) {
                    $url = $config['url'];
                    if ((bool) (int) $config['param']) {
                        $url .= '?orderId=' . $orderId ;
                        
                        if($origin == "pwa"){
                            $url .= '&paymentFailed=true'.'&cart_id='.$cartId ;
                        }
                    }
                    return $url;
                }
            }
        }
    }

    public function customPaymentInformation($order , $invoice){
        $payment = $order->getPayment();
        $payment->setAdditionalInformation('transaction_time', $this->setDateTime($invoice['created']));
        if(isset($invoice["invoice_url"]) && !empty($invoice["invoice_url"])){
            $payment->setAdditionalInformation('invoice_url', $invoice['invoice_url']);
        }
        if (isset($invoice['id'])) {
            $payment->setAdditionalInformation('transaction_id', $invoice['id']);
        }
        if (isset($invoice['qr_string']) && !empty($invoice['qr_string'])) {
            $payment->setAdditionalInformation('qr_string', $invoice['qr_string']);
        }
        if (isset($invoice['expiry_date'])) {
            $payment->setAdditionalInformation('due_date', $this->setDateTime($invoice['expiry_date']));
        }
        if(!empty($invoice["available_banks"][0])){
            $detail = $invoice["available_banks"][0];
            $payment->setAdditionalInformation('xendit_virtual_account', true);
        }
        if(!empty($invoice["available_retail_outlets"][0])){
            $detail = $invoice["available_retail_outlets"][0];
            $payment->setAdditionalInformation('xendit_retail_outlet', true);
        }
        $order->save();
    }

    private function setDateTime($date){
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();   
        $this->timezone = $objectManager->get('\Magento\Framework\Stdlib\DateTime\TimezoneInterface');
        return $this->timezone->date(new \DateTime($date))->format("Y-m-d H:i:s");
    }
    
    public function getSuccessUrl($orderId ,$isMultishipping = false)
    {
        // $baseUrl = $this->storeManagerInterface->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::DEFAULT_URL_TYPE) . 'xendit/checkout/success';
        $baseUrl = $this->scopeConfig->getValue('payment/xendit/pwa_success_url', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $params = [] ;
        if ($isMultishipping) $params[] = 'type=multishipping';
        if (!empty($orderId)) $params[] = 'orderNumber='.$orderId ;

        if(!empty($params)){
            $baseUrl .= '?'.implode("&",$params);
        }
        return $baseUrl;
    }
}
