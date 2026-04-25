<?php

namespace TCGCollective\MarketPlace\Block;

class Steps extends \Magento\Framework\View\Element\Template
{
    protected $urlBuilder;
    protected $session;
    protected $sellerFactory;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $session,
        \Lof\MarketPlace\Model\SellerFactory $sellerFactory,
        $data = []
    ) {
        $this->urlBuilder = $context->getUrlBuilder();
        $this->session = $session;
        $this->sellerFactory = $sellerFactory;
        parent::__construct(
            $context,
            $data
        );
    }

    public function getCurrentUrl()
    {
        return $this->urlBuilder->getCurrentUrl();
    }

    public function getSteps() 
    {
        $currentUrl = $this->getCurrentUrl();

        $steps = [
        [
            'index' => 1,
            'id' => 'verification',
            'label' => 'Seller Verification',
            'title' => 'Verify your identity and business',
            'description' => 'Upload the required documents so that your store can be verified and trusted by customers.',
            'steps' => [
                'Upload your identity and business documents',
                'Wait for the admin review process',
                'If rejected, re-upload the documents as instructed'
            ],
            'btn_label' => 'Upload Documents',
            'url' => str_contains($currentUrl, 'sellerverification/index') ? '' : $this->getUrl('sellerverification/index')
        ],
        [
            'index' => 2,
            'id' => 'payment',
            'label' => 'Setup Payment',
            'title' => 'Set up your payment method',
            'description' => 'Start onboarding with a payment provider (Xendit, Airwallex, or PayPal) so you can receive sales revenue.',
            'steps' => [
                'Choose your payment provider (Xendit, Airwallex, or PayPal)',
                'Complete onboarding and KYC verification',
                'Wait for payment provider approval',
                'Run a test transaction to finalize your setup'
            ],
            'btn_label' => 'Set Up Payment',
            'url' => str_contains($currentUrl, 'catalog/payment/methods') ? '' : $this->getUrl('catalog/payment/methods')
        ],
        [
            'index' => 3,
            'id' => 'membership',
            'label' => 'Select Plan',
            'title' => 'Choose your subscription plan',
            'description' => 'Select the subscription plan that fits your business needs to unlock more features.',
            'steps' => [
                'Pick the country where you’ll sell your products',
                'Compare the available plans',
                'Select the plan that best suits your business',
                'Complete the payment to activate your plan'
            ],
            'btn_label' => 'Select Plan',
            'url' => str_contains($currentUrl, 'sellermembership/membership/buy') ? '' : $this->getUrl('sellermembership/membership/buy')
        ],
        [
            'index' => 4,
            'id' => 'shipment',
            'label' => 'Setup Shipment',
            'title' => 'Set up your shipment method',
            'description' => 'Start onboarding with a shipment provider (Flat Rate, Raja Ongkir, or Table Rates) so you can receive sales revenue.',
            'steps' => [
                'Choose your shipment provider (Flat Rate, Raja Ongkir, or Table Rates)',
                'Complete the rate setup'                
            ],
            'btn_label' => 'Set Up Shipment',
            'url' => str_contains($currentUrl, 'catalog/shipping/methods') ? '' : $this->getUrl('catalog/shipping/methods')
        ]
    ];

        return $steps;
    }

    public function getNextStep() {
        $customerId = $this->session->getId();

        $nextStep = $this->sellerFactory->create()
        ->load($customerId, 'customer_id')
        ->getData('next_step');

        return $nextStep;

        // $currentStep = $this->getCurrentStep();
        // if ($currentStep) {
        //     $currentStep = $currentStep['id'];
        // }

        // if ($nextStep && $nextStep != $currentStep) {
        //     $nextStep = array_filter($this->getSteps(), function ($step) use ($nextStep) {
        //         return $step['id'] === $nextStep;
        //     });

        //     if ($nextStep) {
        //         // Reset array keys if needed
        //         $nextStep = array_values($nextStep);

        //         // dd($nextStep);

        //         return $nextStep[0];
        //     }
        // }

        return null;
    }

    public function getCurrentStep() 
    {
        $customerId = $this->session->getId();
        $registrationStep = $this->sellerFactory->create()
        ->load($customerId, 'customer_id')
        ->getData('registration_step');

        foreach($this->getSteps() as $index => $step) {
            if ($registrationStep == $step['id']) {
                return $step;
                break;
            }
        }
        
        return null;
    }
}
