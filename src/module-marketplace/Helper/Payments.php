<?php

namespace TCGCollective\MarketPlace\Helper;

use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\CacheInterface;

class Payments extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $redirect;
    protected $request;
    protected $serializer;
    protected $resource;
    protected $sellerPaymentAccounts;
    protected $sellerPaymentTransactions;
    protected $messageManager;
    protected $helper;
    protected $platformFeeFactory;
    protected $_scopeConfig;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Serialize\SerializerInterface $serializer,
        \Magento\Framework\App\ResourceConnection $resource,
        \TCGCollective\MarketPlace\Model\SellerPaymentAccountsFactory $sellerPaymentAccounts,
        \TCGCollective\MarketPlace\Model\SellerPaymentTransactionsFactory $sellerPaymentTransactions,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Lof\MarketPlace\Helper\Data $helper,
        \TCGCollective\MarketPlace\Model\PlatformFeeFactory $platformFeeFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $_scopeConfig
    ) {
        $this->redirect = $redirect;
        $this->request = $request;
        $this->urlBuilder = $context->getUrlBuilder();
        $this->serializer = $serializer;
        $this->resource = $resource;
        $this->sellerPaymentAccounts = $sellerPaymentAccounts;
        $this->sellerPaymentTransactions = $sellerPaymentTransactions;
        $this->messageManager = $messageManager;
        $this->helper = $helper;
        $this->platformFeeFactory = $platformFeeFactory;
        $this->_scopeConfig = $_scopeConfig;
        parent::__construct(
            $context
        );
    }

    public function getSeller() 
    {
        $customerId = $this->helper->getCustomerId();
        if ($customerId) {
            return $this->helper->getSellerByCustomerId($customerId);
        }

        return null;
    }

    public function setWebhookUrl(
        $paymentMethod,
        $webhookType = 'invoice',
        $account_id = null,
        $account = null
    ) {
        $result = [];

        $webhookUrl = 'https://seller-preprod.tcgcollective.co/xendit/webhook/invoices';

        $url = "https://api.xendit.co/callback_urls/" . $webhookType;
        $apiKey = "xnd_development_2s8rPPBLUOfhbYLktuYCpBXEFbMwSw2X8lRGg6SCD7Lm4mjSkBBsZlYAyLAvk5DC"; // ganti dengan API Key kamu

        $data = [
            "url" => $webhookUrl
        ];

        $payload = json_encode($data);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json",
            'for-user-id: ' . $account_id,
            'type: ' . $webhookType,
            "Authorization: Basic " . base64_encode($apiKey . ":")
        ]);

        $response = curl_exec($ch);
        if(curl_errno($ch)){
            // dd("Error: " . curl_error($ch));
            $result['error_message'] = __('Oops, something went wrong. Please contact our admin for assistance so we can help you resolve this issue.');

            return $result;
        } else {
            $responseArr = json_decode($response, true);
            if ($responseArr && isset($responseArr['status']) && $responseArr['status'] == "SUCCESSFUL") {
                if (!$account) {
                    $collection = $this->sellerPaymentAccounts->create()
                    ->getCollection()
                    ->addFieldToFilter('account_id', $account_id);
                    if ($collection->getSize()) {
                        $account = $collection->getFirstItem();
                    }
                }

                $account->setInvoiceWebhookUrl($webhookUrl)
                ->save();
            }
        }

        curl_close($ch);

        return $result;
    }

    public function updatePaymentMethodStatus($method, $status) 
    {
        $section = 'payment';
        $groups = [];
        
        $seller_id = (int)$this->helper->getSellerId();
        try {
            $groups[$method]['active'] = $status;

            $this->helper->savePaymentData($section, $groups, $seller_id);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        return true;
    }

    public function xenditAccountUpdatedWebhookSimulation($method, $status, $accountId) 
    {
        $result = [];

        $eventType = 'account.registered';
        if ($status == "LIVE") {
            $eventType = 'account.live';
        }
        // dd('ok');
        $this->accountUpdatedXenditWebhook([
            'created' => time(),
            'data' => [
                'user_id' => $accountId
            ],
            'event_type' => $eventType
        ]);

        // if ($status && $accountId) {
        //         $account = $this->getPaymentAccount($method, $accountId);
        //         if ($account) {
        //             $account->setStatus($status);
        //             $account->save();

        //             return true;
        //         }
        //     }
        // }

        return [
            'success_message' => __('Status updated to %1.', $status)
        ];
    }

    public function testPayment($params) 
    {
        $result = [];
//          array:18 [▼
//   "reference_id" => "order_123456_3ds"
//   "type" => "PAY"
//   "country" => "ID"
//   "currency" => "IDR"
//   "request_amount" => "100000"
//   "capture_method" => "AUTOMATIC"
//   "channel_code" => "CARDS"
//   "card_number" => "4000000000001091"
//   "expiry_month" => "12"
//   "expiry_year" => "2025"
//   "cvn" => "123"
//   "cardholder_first_name" => "John"
//   "cardholder_last_name" => "Doe"
//   "cardholder_email" => "john.doe@example.com"
//   "cardholder_phone_number" => "+628123456789"
//   "description" => "Payment for Order #123456"
//   "order_id" => "123456"
//   "customer_type" => "premium"
// ]
        $method = $params['payment_method'] ?? null;
        $request_amount = $params['request_amount'] ?? null;
        $currency = $params['currency'] ?? null;
        $account_id = $params['account_id'] ?? null;
        // dd('ok');
        // create_transaction.php

        // API Key Xendit kamu (gunakan yang test dulu)
        $apiKey = "xnd_development_2s8rPPBLUOfhbYLktuYCpBXEFbMwSw2X8lRGg6SCD7Lm4mjSkBBsZlYAyLAvk5DC"; 

        // Data transaksi
        
        // Xendit API Key
        // $apiKey = "xnd_development_O46JfOtygef9kMNsK+ZPGT+ZZ9b3ooF4w3Dn+R1k+2fT/7GlCAN3jg==";
        $transId = 'TCGCOLLECTIVE_TEST_' . uniqid();

        $data = [
            "external_id" => $transId,
            "currency" => $currency,
            "amount" => $request_amount,
            "description" => "TCG Collective Seller Test Payment",
            "success_redirect_url" => $this->urlBuilder->getUrl(
                'marketplace/catalog/payment/methods/id/xendit/transaction_id/' . $transId . '/status/success'
            ),
            "failure_redirect_url" => $this->urlBuilder->getUrl(
                'marketplace/catalog/payment/methods/id/xendit/transaction_id/' . $transId . '/status/failure'
            )
        ];

        // dd($data);

        // Init curl
        $ch = curl_init("https://api.xendit.co/v2/invoices");

        // Set curl options
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // curl_setopt($ch, CURLOPT_HTTPHEADER, [
        //     "Content-Type: application/json"
        // ]);
        // curl_setopt($ch, CURLOPT_USERPWD, $apiKey . ":");
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'api-version: 2024-11-11',
            'for-user-id: ' . $account_id,
            'Authorization: Basic ' . base64_encode($apiKey . ':')
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        // Execute
        $response = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($response === false) {
            dd("cURL Error: " . curl_error($ch));
        } else {
            // echo "HTTP Status: " . $httpcode . "\n";
            // dd("Response: " . $response);
            $responseArr = json_decode($response,true);
            // dd($responseArr);
            if ($responseArr) {
                $invoice_url = $responseArr['invoice_url'] ?? null;
                if ($invoice_url) {
                    $result['redirect_url'] = $invoice_url;

                    $account = $this->sellerPaymentAccounts->create()
                    ->getCollection()
                    ->addFieldToFilter('account_id', $account_id)
                    ->getFirstItem();

                    $trans = $this->sellerPaymentTransactions->create();
                    $trans->setRedirectUrl($invoice_url);
                    $trans->setTransactionId($transId);
                    $trans->setSellerId($account->getSellerId());
                    $trans->setStatus('PENDING');
                    $trans->setPaymentMethod($method);
                    $trans->setResponse($response);
                    $trans->save();
                    
                    $account->setTransactionId($trans->getId())
                    ->save();
                }
            }
        }

        curl_close($ch);

        return $result;
    }

    public function createPlatformFeeRule($account = null) 
    {
       if ($account) {
            $result = [];

            $url = "https://api.xendit.co/fee_rules";
            $apiKey = "xnd_development_2s8rPPBLUOfhbYLktuYCpBXEFbMwSw2X8lRGg6SCD7Lm4mjSkBBsZlYAyLAvk5DC"; // ganti dengan API Key kamu

            $sellerId = $this->helper->getSellerId();

            $platformFeeUnit = $this->_scopeConfig->getValue(
                'marketplace_platform_fee/platform_fee_config/unit',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );

            $platformFeeAmount = $this->_scopeConfig->getValue(
                'marketplace_platform_fee/platform_fee_config/amount',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );

            $currency = $this->_scopeConfig->getValue(
                'marketplace_platform_fee/platform_fee_config/currency',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );

            $data = [
                "name" => "TCG Collective Platform Fees",
                "description" => "Platform fee for a TCG Collective Marketplace",
                "routes" => [
                    [    
                        "amount" => $platformFeeAmount,
                        "unit" => $platformFeeUnit,
                        "currency" => $currency,
                        // "destination_account_id" => null,
                        "reference_id" => "seller-platform-fee-" . $sellerId
                    ]
                ]
            ];

            $payload = json_encode($data);

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "Content-Type: application/json",
                "Authorization: Basic " . base64_encode($apiKey . ":")
            ]);

            $response = curl_exec($ch);
            if(curl_errno($ch)){
                dd("Error: " . curl_error($ch));
            } else {
                $responseArr = json_decode($response, true);
                if ($responseArr) {
                    // dd($responseArr);
                    $ruleId = $responseArr['id'] ?? null;
                    if ($ruleId) {
                        $name = $responseArr['name'] ?? null;
                        $description = $responseArr['description'] ?? null;

                        $routes = $responseArr['routes'] ?? null;
                        if ($routes) {
                            $destinationAccountId = $routes['id'] ?? null;
                            $amount = $routes['amount'] ?? null;
                            $unit = $routes['unit'] ?? null;
                            $currency = $routes['currency'] ?? null;
                            $referenceId = $routes['reference_id'] ?? null;

                            $platformFee = $this->platformFeeFactory->create()
                            ->setRuleId($ruleId)
                            ->setSellerId($sellerId)
                            ->setName($name)
                            ->setDescription($description)
                            ->setDestinationAccountId($destinationAccountId)
                            ->setAmount($amount)
                            ->setUnit($unit)
                            ->setCurrency($currency)
                            ->setReferenceId($referenceId)
                            ->setPaymentMethod('xendit')
                            ->save();

                            $account->setPlatformFeeRuleId($ruleId)->save();
                        }
                    }
                }
            }

            curl_close($ch);
       }

        return true;
    }

    public function invoiceSuccessXenditWebhook($requestData) 
    {
        // dd($requestData);

        $transId = $requestData['external_id'] ?? null;
        if ($transId) {
            $trans = $this->sellerPaymentTransactions->create()
            ->getCollection()
            ->addFieldToFilter('transaction_id', $transId)
            ->getFirstItem();
            
            $trans->setStatus($requestData['status']);
            $trans->save();
        }

        return true;
    }

    public function accountUpdatedXenditWebhook($requestData) {
        $method = 'xendit';

        if ($requestData && isset($requestData['created']) && isset($requestData['data'])) {
            $eventType = $requestData['event_type'] ?? null;
            if ($eventType) {
                $status = null;

                if ($eventType == "account.registered") {
                    $status = 'REGISTERED';
                }

                if ($eventType == "AWAITING_DOCS") {
                    $status = 'AWAITING_DOCS';
                }

                if ($eventType == "account.live") {
                    $status = 'LIVE';
                }

                if ($eventType == "SUSPENDED") {
                    $status = 'SUSPENDED';
                }

                if ($status) {
                    $accountId = $requestData['data']['user_id'] ?? null;
                    if ($accountId) {
                        $account = $this->getPaymentAccount($method, $accountId);
                        if ($account) {
                            if ($eventType == "account.live") {
                                $status = 'LIVE';

                                $setWebhookUrlResult = $this->setWebhookUrl(
                                    $method,
                                    'invoice',
                                    $accountId,
                                    $account
                                );
                            }
                            
                            $account->setStatus($status);
                            $account->save();

                            return true;
                        }
                    }
                }
            }
        }

        return false;
    }

    public function getPaymentTestTransaction($method) 
    {
        $sellerId = $this->helper->getSellerId();

        $collection = $this->sellerPaymentTransactions->create()
        ->getCollection();
        
        if ($sellerId) {
            $collection->addFieldToFilter('seller_id', $sellerId);
        }
        
        $collection->addFieldToFilter('payment_method', $method);

        if ($collection->getSize()) {
            return $collection->getFirstItem();
        }

        return null;
    }

    public function getPaymentAccount($method, $accountId = null) 
    {
        $sellerId = null;

        if (!$accountId) {
            $sellerId = $this->helper->getSellerId();
        }

        $collection = $this->sellerPaymentAccounts->create()
        ->getCollection();
        
        if ($sellerId) {
            $collection->addFieldToFilter('seller_id', $sellerId);
        }
        
        $collection->addFieldToFilter('payment_method', $method);

        if ($accountId) {
            $collection->addFieldToFilter('account_id', $accountId);
        }

        if ($collection->getSize()) {
            return $collection->getFirstItem();
        }

        return null;
    }

    public function getSellerPaymentAccount($method, $sellerId) 
    {
        $collection = $this->sellerPaymentAccounts->create()
        ->getCollection();
        
        if ($sellerId) {
            $collection->addFieldToFilter('seller_id', $sellerId);
        }
        
        $collection->addFieldToFilter('payment_method', $method);
        
        if ($collection->getSize()) {
            return $collection->getFirstItem();
        }

        return null;
    }

    public function createPaymentAccount(
        $paymentMethod, 
        $email, 
        $business_name
    ) {
        $result = [];

        $url = "https://api.xendit.co/v2/accounts";
        $apiKey = "xnd_development_2s8rPPBLUOfhbYLktuYCpBXEFbMwSw2X8lRGg6SCD7Lm4mjSkBBsZlYAyLAvk5DC"; // ganti dengan API Key kamu

        $data = [
            "email" => $email,
            "type" => 'MANAGED',
            "public_profile" => [
                "business_name" => $business_name
            ]
        ];

        $payload = json_encode($data);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json",
            "Authorization: Basic " . base64_encode($apiKey . ":")
        ]);

        $response = curl_exec($ch);
        if(curl_errno($ch)){
            // dd("Error: " . curl_error($ch));
            $result['error_message'] = __('Oops, something went wrong. We couldn\'t process your Xendit account request at the moment. Please contact our admin for assistance so we can help you resolve this issue.');

            return $result;
        } else {
            $responseArr = json_decode($response, true);
            if ($responseArr) {
                $errorCode = $responseArr['error_code'] ?? null;
                $message = $responseArr['message'] ?? null;
                if ($errorCode && $message) {
                    $result['error_message'] = __($message);
                    return $result;
                }

                $sellerId = $this->helper->getSellerId();

                $data = [
                    'seller_id' => $sellerId,
                    'payment_method' => $paymentMethod,
                    'email' => $email,
                    'business_name' => $business_name,
                    'account_id' => $responseArr['id'] ?? '',
                    'status' => $responseArr['status'] ?? '',
                    'country' => $responseArr['country'] ?? '',
                    'created_date' => $responseArr['created'] ?? '',
                    'create_account_response' => $response
                ];

                $paymentAccount = $this->sellerPaymentAccounts->create();
                $paymentAccount->setData($data)
                ->save();

                $result['success_message'] = __('We\'ve sent an email to the email address you provided with a link to create your Xendit account. Please check your inbox and click the link to continue setting up your account.');
            }
        }

        curl_close($ch);

        return $result;
    }
}