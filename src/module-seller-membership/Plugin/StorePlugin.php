<?php

namespace TCGCollective\SellerMembership\Plugin;

use Magento\Checkout\Model\Session;

class StorePlugin
{
    protected $checkoutSession;

    public function __construct(Session $checkoutSession)
    {
        $this->checkoutSession = $checkoutSession;
    }

    public function aroundGetBaseCurrencyCode(
        \Magento\Store\Model\Store $subject,
        \Closure $proceed
    ) {
        try {
            $quote = $this->checkoutSession->getQuote();

            if ($quote && $quote->getItemsCount()) {
                foreach ($quote->getAllItems() as $item) {
                    if ($item->getProductType() === 'seller_membership') {

                        // 🚀 FORCE STORE LEVEL
                        $subject->setBaseCurrencyCode('USD');
                        $subject->setCurrentCurrencyCode('USD');

                        return 'USD';
                    }
                }
            }

        } catch (\Exception $e) {
            return $proceed();
        }

        return $proceed();
    }
}