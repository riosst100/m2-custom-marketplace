<?php

namespace TCGCollective\MarketPlace\Plugin;

use Magento\Customer\Model\ResourceModel\Group\Grid\Collection;
use Magento\Framework\App\RequestInterface;

class FilterSellerGroupGridCollection
{
    protected RequestInterface $request;

    public function __construct(RequestInterface $request)
    {
        $this->request = $request;
    }

    public function beforeLoad(Collection $collection)
    {
        if ($collection->isLoaded()) {
            return;
        }

        $fullActionName = $this->request->getFullActionName();

        // Seller group page → show seller groups only
        if (str_contains($fullActionName, 'marketplace_sellergroup')) {
            $collection->addFieldToFilter('is_seller', 1);
        }
    }
}
