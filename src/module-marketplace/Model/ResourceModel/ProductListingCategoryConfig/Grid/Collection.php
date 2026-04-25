<?php

namespace TCGCollective\MarketPlace\Model\ResourceModel\ProductListingCategoryConfig\Grid;

use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;

class Collection extends SearchResult
{
    protected function _construct()
    {
        $this->_init(
            \TCGCollective\MarketPlace\Model\ProductListingCategoryConfig::class,
            \TCGCollective\MarketPlace\Model\ResourceModel\ProductListingCategoryConfig::class
        );

        $this->setMainTable('marketplace_product_listing_category_config');
    }
}