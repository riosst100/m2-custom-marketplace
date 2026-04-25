<?php

namespace TCGCollective\MarketplaceProductPreorder\Plugin\GraphQl;

class CartItemPlugin
{
    public function afterGetData($subject, $result, $cartItem)
    {
        $result['is_preorder'] = $cartItem->getData('is_preorder');
        $result['pre_order_payment_type'] = $cartItem->getData('pre_order_payment_type');
        $result['pre_order_deposit'] = $cartItem->getData('pre_order_deposit');
        return $result;
    }
}
