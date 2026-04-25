<?php

namespace TCGCollective\MarketPlace\Plugin;

use Magento\Customer\Model\ResourceModel\Group\Collection;
use Magento\Framework\App\RequestInterface;

class FilterCustomerGroupCollection
{
    protected RequestInterface $request;

    public function __construct(RequestInterface $request)
    {
        $this->request = $request;
    }

    public function beforeLoad(Collection $collection)
    {
        // Avoid double filtering
        if ($collection->isLoaded()) {
            return;
        }

        $fullActionName = $this->request->getFullActionName();

        // UI grid namespace (AJAX-safe)
        $namespace = (string) $this->request->getParam('namespace');

        $isSellerGrid = $namespace === 'seller_group_listing';

        if ($isSellerGrid) {
            $collection->addFieldToFilter('is_seller', 1);
        } else {
            if (in_array($fullActionName, [
                'catalog_rule_promo_catalog_new',
                'catalog_rule_promo_catalog_edit',
                'sales_rule_promo_quote_new',
                'sales_rule_promo_quote_edit'
            ])) {
                $collection->addFieldToFilter('is_seller', 1);
            } else {
                // $collection->addFieldToFilter('is_seller', 0);
            }            
        }
    }
}
