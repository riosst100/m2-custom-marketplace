<?php

namespace TCGCollective\MarketPlace\Controller\Adminhtml\SellerGroup;

use Magento\Backend\App\Action;
use Magento\Framework\View\Result\PageFactory;

class Index extends \TCGCollective\MarketPlace\Controller\Adminhtml\SellerGroup
{
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('TCGCollective_MarketPlace::seller_groups');
        $resultPage->getConfig()->getTitle()->prepend(__('Seller Groups'));

        return $resultPage;
    }
}
