<?php
namespace TCGCollective\MarketplaceProductPreorder\Model\Cart\Data;

use Magento\Quote\Model\Cart\Data\CartItem;

class PreorderCartItem extends CartItem
{
    protected $isPreorder = false;
    protected $preOrderPaymentType = null;
    protected $preOrderDeposit = null;

    public function setIsPreorder($value)
    {
        $this->isPreorder = (bool)$value;
        return $this;
    }

    public function getIsPreorder()
    {
        return $this->isPreorder;
    }

    public function setPreOrderPaymentType($value)
    {
        $this->preOrderPaymentType = $value;
        return $this;
    }

    public function getPreOrderPaymentType()
    {
        return $this->preOrderPaymentType;
    }

    public function setPreOrderDeposit($value)
    {
        $this->preOrderDeposit = $value;
        return $this;
    }

    public function getPreOrderDeposit()
    {
        return $this->preOrderDeposit;
    }
}
