<?php

namespace TCGCollective\MarketPlace\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;

class SaveCategoryAttributes implements ObserverInterface
{
    protected $request;

    public function __construct(RequestInterface $request)
    {
        $this->request = $request;
    }

    public function execute(Observer $observer)
    {
        $shopBy = $this->request->getParam('shop_by', []);
        $allowedFilters = $this->request->getParam('allowed_filters', []);

        $category = $observer->getEvent()->getCategory();
        // $shopBy = $category->getData('shop_by');
        // $allowedFilters = $category->getData('allowed_filters');
        // dd($allowedFilters);

        // Proses nilai sebelum penyimpanan (jika perlu)
        if (is_array($shopBy)) {
            $category->setData('shop_by', implode(',', $shopBy));
        }
        
        if (is_array($allowedFilters)) {
            $category->setData('allowed_filters', implode(',', $allowedFilters));
        }
    }
}
