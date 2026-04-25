<?php

declare(strict_types=1);

namespace TCGCollective\MarketplacePaymentXendit\Model\Resolver;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Sales\Model\Order;
use Xendit\M2Invoice\Helper\ErrorHandler;
use Xendit\M2Invoice\Helper\ApiRequest;
use Xendit\M2Invoice\Helper\Checkout;
use Magento\Framework\UrlInterface;
use Magento\Framework\Phrase;
use Laminas\Http\Request;
use Psr\Log\LoggerInterface;
use Magento\Directory\Model\CurrencyFactory;
// use Xendit\M2Invoice\Helper\Qrcode;
/**
 * @inheritdoc
 */
class XenditCreateInvoice implements ResolverInterface
{
    protected $paymentHelper;
    protected $_order;
    protected $customData;
    protected $xenditData;
    protected $storeManagerInterface;
    protected $xenditError;
    protected $getApiHelper;
    protected $getLogger;
    protected $checkoutHelper;
    protected $CCSubscription;
    protected $currencyFactory;

    public function __construct(
        Order $order,
        \TCGCollective\MarketplacePaymentXendit\Helper\CustomData $customData,
        \Xendit\M2Invoice\Helper\Data $xenditData,
        ErrorHandler $xenditError,
        ApiRequest $ApiRequest,
        Checkout $checkoutHelper,
        LoggerInterface $LoggerInterface,
        \Magento\Store\Model\StoreManagerInterface $StoreManagerInterface,
        // Qrcode $qrcodeHelper,
        \TCGCollective\MarketplacePaymentXendit\Model\Payment\CCSubscription $CCSubscription,
         CurrencyFactory $currencyFactory,
        \TCGCollective\MarketPlace\Helper\Payments $paymentHelper
    ) {
        $this->_order = $order;
        $this->customData = $customData;
        $this->xenditData = $xenditData;
        $this->storeManagerInterface = $StoreManagerInterface;
        $this->xenditError = $xenditError;
        $this->getApiHelper = $ApiRequest;
        $this->getLogger = $LoggerInterface;
        $this->checkoutHelper = $checkoutHelper;
        // $this->qrcodeHelper = $qrcodeHelper;
        $this->CCSubscription = $CCSubscription;
        $this->currencyFactory = $currencyFactory;
        $this->paymentHelper = $paymentHelper;
    }
    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ){
        if (empty($args['input']['order_id'])) {
            throw new GraphQlInputException(__('Required parameter "order_id" is missing'));
        }
        $storeId = $context->getExtensionAttributes()->getStore();
        $orderId = $args['input']['order_id'];

        $result = $this->getUrlInvoice($orderId);
        $result["mode"] = $this->xenditData->getEnvironment();
        return $result;
    }

    public function getUrlInvoice($orderId)
    {
        $order = $this->_order->loadByIncrementId($orderId);
        if (!$order->getId()) {
            throw new GraphQlInputException(
                __('Cannot find order number "%1"', $orderId)
            );
        }
        $invoice = [];
        $method = $order->getPayment()->getMethod();

        if($method == "cc_subscription" || $method == "qr_codes"){
            // $invoice = $this->CCSubscription->authorize($order);
            return $this->setInvoiceDataFromRedirect($order);
        }

        try {
            $apiData = $this->getApiRequestData($order);
            if ($order->getState() === Order::STATE_NEW) {
                $this->changePendingPaymentStatus($order);
                $invoice = $this->createInvoice($apiData, $order);
                $this->addInvoiceData($order, $invoice);

                return $invoice;
            } elseif ($order->getState() === Order::STATE_CANCELED) {
                throw new LocalizedException(__('Order in unrecognized state: ' . $order->getState()));
            } else {
                $this->getLogger->debug('Order in unrecognized state: ' . $order->getState());
                throw new LocalizedException(__('Order in unrecognized state: ' . $order->getState()));
            }
        } catch (\Exception $e) {
            $message = 'Exception caught on xendit/checkout/invoice: ' . $e->getMessage();

            $this->getLogger->debug('Exception caught on xendit/checkout/invoice: ' . $message);
            $this->getLogger->debug($e->getTraceAsString());

            $this->cancelOrder($order, $e->getMessage());
            throw new GraphQlInputException(__($e->getMessage()));
        }
        return $invoice;
    }

    public function getUrlMerchant($order){
        
    }

    private function getApiRequestData($order)
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/xendit.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info('getApiRequestData');

        $orderCurrency = $order->getOrderCurrencyCode();
        $convertedAmount = $this->convertCurrency($order->getTotalDue(), $orderCurrency, 'IDR');

        if ($order == null) {
            throw new GraphQlInputException(__('Unable to get order data '));
        }

        $orderId = $order->getRealOrderId();
        $preferredMethod = $this->customData->getPreferredMethod($order->getPayment()->getMethod());

        $requestData = [
            'external_id'           => $this->xenditData->getExternalId($orderId),
            'payer_email'           => $order->getCustomerEmail(),
            'description'           => $orderId,
            // 'amount'                => $order->getTotalDue(),
            'amount'                => $convertedAmount,
            'currency'              => 'IDR',
            // 'currency'              => $order->getBaseCurrencyCode(),
            'preferred_method'      => $preferredMethod,            
            'client_type'           => 'INTEGRATION',
            // 'payment_methods'       => json_encode([strtoupper($preferredMethod)]),
            'platform_callback_url' => $this->getXenditCallbackUrl(),
            'success_redirect_url'  => $this->customData->getSuccessUrl($orderId),
            'failure_redirect_url'  => $this->xenditData->getFailureUrl([$orderId])
        ];
        
        $logger->info('Request Data: ' . json_encode($requestData));
        return $requestData;
    }

    private function convertCurrency($amount, $fromCurrency, $toCurrency)
    {
        try {
            $currency = $this->currencyFactory->create()->load($fromCurrency);
            $convertedAmount = $currency->convert($amount, $toCurrency);

            $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/xendit.log');
            $logger = new \Zend_Log();
            $logger->addWriter($writer);
            $logger->info("Converted {$amount} {$fromCurrency} → {$convertedAmount} {$toCurrency}");

            return round($convertedAmount, 2);
        } catch (\Exception $e) {
            return $amount; // fallback if conversion fails
        }
    }

    protected function getXenditCallbackUrl()
    {
        $baseUrl = $this->storeManagerInterface->getStore()->getBaseUrl(UrlInterface::URL_TYPE_LINK);

        return $baseUrl . 'xendit/checkout/notification';
    }

    private function changePendingPaymentStatus($order)
    {
        $order->setState(Order::STATE_PENDING_PAYMENT)->setStatus(Order::STATE_PENDING_PAYMENT);
        $order->addStatusHistoryComment("Pending Xendit payment.");
        $order->save();
    }

    private function createInvoice($requestData, $order)
    {
        // $invoiceUrl = $this->xenditData->getCheckoutUrl() . "/payment/xendit/invoice";
        $invoiceUrl = $this->xenditData->getXenditApiUrl() . "/tpi/payment/xendit/invoice";
        $invoiceMethod = Request::METHOD_POST;
        $invoice = '';

        try {
            if (isset($requestData['preferred_method'])) {

                $sellerId = $this->getSellerIdFromOrder($order);
                if ($sellerId) {
                    $account = $this->paymentHelper->getSellerPaymentAccount('xendit', $sellerId);
                    if ($account) {
                        $ruleId = $account->getPlatformFeeRuleId();
                        $sellerXenditAccountId = $account->getAccountId();

                        $customHeaders = [
                            'for-user-id' => $sellerXenditAccountId,
                            'with-split-rule' => $ruleId
                        ];

                        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/xenditcustomheader.log');
                $logger = new \Zend_Log();
                $logger->addWriter($writer);
                $logger->info("user: ".print_r($customHeaders,true));

                        $invoice = $this->getApiHelper->request(
                            $invoiceUrl, $invoiceMethod, $requestData, false, $requestData['preferred_method'], [], $customHeaders
                        );
                    }
                }
            }
            if (isset($invoice['error_code'])) {
                $message = $this->xenditError->mapInvoiceErrorCode($invoice['error_code']);
                throw new GraphQlInputException(
                    new Phrase($message)
                );
            }

        } catch (LocalizedException $e) {
            throw new GraphQlInputException(
                new Phrase($e->getMessage())
            );
        }

        return $invoice;
    }

    public function getSellerIdFromOrder($order) 
    {
        $sellerId = null;

        foreach ($order->getAllItems() as $item) {
            $product = $item->getProduct()->load($item->getProductId());
            if ($item->getParentItem() || $product->isVirtual()) {
                continue;
            }

            // Identify seller
            $sellerId = $item->getSellerId() ?: $product->getSellerId() ?: null;
            break;
        }

        return $sellerId;
    }

    private function addInvoiceData($order, $invoice)
    {
        $payment = $order->getPayment();
        $payment->setAdditionalInformation('payment_gateway', 'xendit');
        if (isset($invoice['id'])) {
            $payment->setAdditionalInformation('xendit_invoice_id', $invoice['id']);
        }
        if (isset($invoice['expiry_date'])) {
            $payment->setAdditionalInformation('xendit_invoice_exp_date', $invoice['expiry_date']);
        }
        $order->save();
        $this->customData->customPaymentInformation($order , $invoice);

    }

    private function cancelOrder($order, $failureReason)
    {
        $orderState = Order::STATE_CANCELED;

        if ($order->getStatus() != $orderState) {
            $order->setState($orderState)
                    ->setStatus($orderState)
                    ->addStatusHistoryComment("Order #" . $order->getIncrementId() . " was cancelled by Xendit because " . $failureReason);
            $order->save();

            $this->checkoutHelper->cancelOrderById($order->getId(), "Order #" . ($order->getId()) . " was rejected by Xendit");
            $this->checkoutHelper->restoreQuote(); //restore cart
        }

        return;
    }

    private function setInvoiceDataFromRedirect($order){
        try {
            $order = $order;
            $orderId = $order->getRealOrderId();
            $payment = $order->getPayment();

            if ($order->getState() === Order::STATE_NEW || $order->getState() === Order::STATE_PENDING_PAYMENT || $order->getState() === Order::STATE_PAYMENT_REVIEW) {
                $this->changePendingPaymentStatus($order);
            } elseif ($order->getState() === Order::STATE_CANCELED) {
                throw new LocalizedException(__('Order in unrecognized state: ' . $order->getState()));
            } else {
                $this->getLogger->debug('Order in unrecognized state: ' . $order->getState());
                throw new LocalizedException(__('Order in unrecognized state: ' . $order->getState()));
            }

            if ($payment->getAdditionalInformation('xendit_failure_reason') !== null) {
                $failureReason = $payment->getAdditionalInformation('xendit_failure_reason');
                $this->cancelOrder($order, $failureReason);
                throw new GraphQlInputException(__($failureReason));
            }
            // Dana / Kredivo / Linkaja / CC Subscription
            if ($payment->getAdditionalInformation('xendit_redirect_url') !== null) {
                $redirectUrl = $payment->getAdditionalInformation();
                return $redirectUrl;
            }
            // Qrcode
            // if ($payment->getAdditionalInformation('xendit_qrcode_external_id') !== null) {
            //     $args  = [
            //         '_secure'=> true,
            //         'xendit_qrcode_external_id'     => $payment->getAdditionalInformation('xendit_qrcode_external_id'),
            //         'xendit_qr_string'              => $payment->getAdditionalInformation('xendit_qr_string'),
            //         'xendit_qrcode_type'            => $payment->getAdditionalInformation('xendit_qrcode_type'),
            //         'xendit_qrcode_status'          => $payment->getAdditionalInformation('xendit_qrcode_status'),
            //         'xendit_qrcode_amount'          => $payment->getAdditionalInformation('xendit_qrcode_amount'),
            //         'xendit_qrcode_is_multishipping'=> $payment->getAdditionalInformation('xendit_qrcode_is_multishipping')
            //     ];
            //     $qrString = $args["xendit_qr_string"];
            //     $externalId = $args["xendit_qrcode_external_id"];

            //     if(empty($payment->getAdditionalInformation("invoice_url"))){
            //         try {
            //         // generate QRcode Image from QR string
            //             $qrcodeUrl = $this->qrcodeHelper->generateQrcode($qrString, $externalId);
            //             $payment->setAdditionalInformation("invoice_url", $qrcodeUrl);
            //             $payment->setAdditionalInformation("xendit_qrcode", true);
            //             $order->save();
            //         } catch (LocalizedException $e) {
            //             throw new GraphQlInputException(__("Exception occurred while generating Qrcode"));
            //         }
            //     }

            //     return $order->getPayment()->getAdditionalInformation();
            // }

            throw new GraphQlInputException(__("Unable to get order data"));
        } catch (\Exception $e) {
            $this->cancelOrder($order, $e->getMessage());
            throw new GraphQlInputException(__($e->getMessage()));
        }
    }
}