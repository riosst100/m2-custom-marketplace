<?php

namespace TCGCollective\MarketPlace\Model;

/**
 * Import history model
 *
 * @api
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.LongVariable)
 * @since 100.0.2
 */
class ProductListingTabColumn extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Initialize history resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\TCGCollective\MarketPlace\Model\ResourceModel\ProductListingTabColumn::class);
    }
}
