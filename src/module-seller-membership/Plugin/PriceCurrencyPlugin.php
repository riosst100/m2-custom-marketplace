<?php

namespace TCGCollective\SellerMembership\Plugin;

use Magento\Framework\Registry;

class PriceCurrencyPlugin
{
    protected $registry;

    public function __construct(Registry $registry)
    {
        $this->registry = $registry;
    }

    public function aroundConvert(
        \Magento\Framework\Pricing\PriceCurrencyInterface $subject,
        \Closure $proceed,
        $amount,
        $scope = null,
        $currency = null
    ) {
        // ambil current product (hanya ada di product page)
        $product = $this->registry->registry('current_product');

        if ($product && $product->getTypeId() === 'seller_membership') {
            // 🚀 SKIP CONVERSION
            return $amount;
        }

        return $proceed($amount, $scope, $currency);
    }
}