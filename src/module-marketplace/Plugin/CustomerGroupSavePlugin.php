<?php

namespace TCGCollective\MarketPlace\Plugin;

use Magento\Customer\Model\Group;
use Magento\Framework\App\RequestInterface;

class CustomerGroupSavePlugin
{
    protected RequestInterface $request;

    public function __construct(RequestInterface $request)
    {
        $this->request = $request;
    }

    public function beforeSave(Group $subject)
    {
        $fullActionName = $this->request->getFullActionName();

        // Only affect seller group pages
        if (str_contains($fullActionName, 'marketplace_sellergroup')) {
            $subject->setData('is_seller', 1);
        }
    }
}
