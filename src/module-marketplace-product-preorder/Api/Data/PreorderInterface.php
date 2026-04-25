<?php

namespace TCGCollective\MarketplaceProductPreorder\Api\Data;

interface PreorderInterface
{
    const PRE_ORDER_PAYMENT_TYPE = 'pre_order_payment_type';
    const PRE_ORDER_DEPOSIT = 'pre_order_deposit';

    public function getPreOrderPaymentType();

    public function setPreOrderPaymentType($value);

    public function getPreOrderDeposit();

    public function setPreOrderDeposit($value);
}
