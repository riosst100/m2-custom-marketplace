<?php
namespace TCGCollective\SellerMembership\Plugin;

use Magento\Directory\Model\Currency;
use Magento\Checkout\Model\Session as CheckoutSession;

class CustomCurrencyConvert
{
    protected $checkoutSession;

    public function __construct(CheckoutSession $checkoutSession)
    {
        $this->checkoutSession = $checkoutSession;
    }

    public function aroundConvert(
        Currency $subject,
        callable $proceed,
        $price,
        $toCurrency = null
    ) {
        // $quote = $this->checkoutSession->getQuote();
        // if (!$quote || !$quote->getId()) {
        //     return $proceed($price, $toCurrency);
        // }

        // // foreach ($quote->getAllItems() as $item) {
        // //     if ($item->getProduct()->getData('is_membership')) {
        //         // bypass conversion
                return $price;
        // //     }
        // // }

        return $proceed($price, $toCurrency);
    }

}
