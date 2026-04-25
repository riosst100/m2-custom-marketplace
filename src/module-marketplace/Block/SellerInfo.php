<?php

namespace TCGCollective\MarketPlace\Block;

class SellerInfo extends \Magento\Framework\View\Element\Template
{
    protected $urlBuilder;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        $data = []
    ) {
        $this->urlBuilder = $context->getUrlBuilder();
        parent::__construct(
            $context,
            $data
        );
    }
}
