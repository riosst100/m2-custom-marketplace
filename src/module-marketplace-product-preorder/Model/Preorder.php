<?php

namespace TCGCollective\MarketplaceProductPreorder\Model;

use TCGCollective\MarketplaceProductPreorder\Api\Data\PreorderInterface;
use Magento\Framework\Model\AbstractExtensibleModel;

class Preorder extends AbstractExtensibleModel implements PreorderInterface
{
    public function getPreOrderPaymentType()
    {
        return $this->_getData(self::PRE_ORDER_PAYMENT_TYPE);
    }

    public function setPreOrderPaymentType($value)
    {
        return $this->setData(self::PRE_ORDER_PAYMENT_TYPE, $value);
    }

    public function getPreOrderDeposit()
    {
        return $this->_getData(self::PRE_ORDER_DEPOSIT);
    }

    public function setPreOrderDeposit($value)
    {
        return $this->setData(self::PRE_ORDER_DEPOSIT, $value);
    }
}
