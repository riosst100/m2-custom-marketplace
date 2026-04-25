<?php

namespace TCGCollective\MarketPlace\Controller\Marketplace\Products\Grid;

class ProductsData extends \Magento\Framework\App\Action\Action
{
    protected $productsHelper;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \TCGCollective\MarketPlace\Helper\Products $productsHelper
    ) {
        $this->productsHelper = $productsHelper;
        parent::__construct($context);
    }

    public function execute() {
        return $this->productsHelper->getSellerProductsListingData();
    }
}