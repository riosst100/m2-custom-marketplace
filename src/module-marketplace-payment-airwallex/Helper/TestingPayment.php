<?php

namespace TCGCollective\MarketplacePaymentAirwallex\Helper;

class TestingPayment
{
    protected $testSuccessPaymentApi;
    protected $storeManager;
    protected $productRepository;
    protected $cart;
    protected $airwallexOrderService;
    protected $paymentInterface;

    public function __construct(
        \TCGCollective\MarketplacePaymentAirwallex\Model\Client\Request\TestSuccessPayment $testSuccessPaymentApi,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Checkout\Model\Cart $cart,
        \Airwallex\Payments\Model\OrderService $airwallexOrderService,
        \Magento\Quote\Api\Data\PaymentInterface $paymentInterface
    ) {
        $this->testSuccessPaymentApi = $testSuccessPaymentApi;
        $this->storeManager = $storeManager;
        $this->productRepository = $productRepository;
        $this->cart = $cart;
        $this->airwallexOrderService = $airwallexOrderService;
        $this->paymentInterface = $paymentInterface;
    }

    public function _initProduct() 
    {
        $sku = 'SELLER_TEST_AIRWALLEX_PAYMENT';

        $storeId = $this->storeManager->getStore()->getId();

        try {
            return $this->productRepository->get($sku, false, $storeId);
        } catch (NoSuchEntityException $e) {
            return false;
        }

        return false;
    }
    
    public function testSuccessPayment() {
        try {
            // $product = $this->_initProduct();
            // // dd($product);
            // if ($product) {
            //     $this->clearCart($this->cart);
                
            //     $params = [];

            //     $this->cart->addProduct($product, $params);
            //     $this->cart->save();

            //     $quote = $this->cart->getQuote();
                
            //     $payment = $this->paymentInterface;
            //     $payment->setMethod('airwallex_payments_card');

            //     $paymentMethod = $quote->setPayment($payment);

            //     // Example billing address data (replace with real customer data)
            //     $billingData = [
            //         'firstname'   => 'John',
            //         'lastname'    => 'Doe',
            //         'street'      => ['123 Test Street'],
            //         'city'        => 'Los Angeles',
            //         'postcode'    => '90001',
            //         'telephone'   => '1234567890',
            //         'country_id'  => 'US',
            //         'region'      => 'California'
            //     ];

                // Set billing address
                // $billingAddress = $quote->getBillingAddress();

                // $billingAddress->setFirstname('ok');
                // $billingAddress->setLastname('ok');
                // $billingAddress->setData('street', ['ok']);
                // $billingAddress->setData('city', 'Los Angeles');
                // $billingAddress->setData('country_id', 'US');
                // $billingAddress->setData('postcode', '90001');
                // $billingAddress->setData('telephone', '1234567890');
                // $billingAddress->setData('region', 'California');
                // // $billingAddress->save();
                // $quote->setBillingAddress($billingAddress);
                // // $billingAddress->addData($billingData);

                // // dd($billingAddress);


                // $quote->collectTotals();
                // $quote->save();

                // $placeorderResponse = $this->airwallexOrderService->airwallexPlaceOrder(
                //     $this->cart->getQuote()->getId(),
                //     $paymentMethod,
                //     $billingAddress,
                //     $intentId = '',
                //     $paymentMethodId = '',
                //     $from = 'card_with_saved'
                // );

                // dd($placeorderResponse);
                
                // PlaceOrderResponseInterface

                

                $authCode = '123312';
                $cardNumber = '4242424242424242';
                $merchantCategoryCode = '0742';
                $merchantInfo = 'Example';
                $amount = 1;
                $currency = 'AUD';

                $result = $this->testSuccessPaymentApi->setRequestParams(
                    $authCode, 
                    $cardNumber, 
                    $merchantCategoryCode, 
                    $merchantInfo, 
                    $amount, 
                    $currency
                )->send();

                // dd($result);
        //     }
        } catch (\Exception $e) {
            dd($e->getMessage());
        }

        return false;
    }

    public function clearCart($cart) 
    {
        $cart->truncate();
        $cart->saveQuote();

        return true;
    }
}
