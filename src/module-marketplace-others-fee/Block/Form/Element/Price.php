<?php

namespace TCGCollective\MarketplaceOthersFee\Block\Form\Element;

class Price extends \Magento\Framework\Data\Form\Element\Text
{
    public function getElementHtml()
    {
        $html = parent::getElementHtml();
        $currencySymbol = '$'; // bisa ambil dari store config
        $html = '<span class="currency-symbol">'.$currencySymbol.'</span>' . $html;
        return $html;
    }
}
