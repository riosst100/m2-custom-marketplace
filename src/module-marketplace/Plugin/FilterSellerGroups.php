<?php

namespace TCGCollective\MarketPlace\Plugin;

use Magento\Customer\Model\ResourceModel\Group\Collection;
use Magento\Framework\App\RequestInterface;

class FilterSellerGroups
{
    private RequestInterface $request;

    public function __construct(RequestInterface $request)
    {
        $this->request = $request;
    }

    public function beforeLoad(Collection $subject, $printQuery = false, $logQuery = false)
    {
        // Only apply on Catalog Price Rule pages
        $fullActionName = $this->request->getFullActionName();

        if (in_array($fullActionName, [
            'catalog_rule_promo_catalog_new',
            'catalog_rule_promo_catalog_edit'
        ])) {
            $subject->addFieldToFilter('is_seller', 1);
        }
        // dd($subject->getSelect()->__toString());
        return [$printQuery, $logQuery];
    }
}
