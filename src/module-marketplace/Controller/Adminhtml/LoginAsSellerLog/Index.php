<?php

namespace TCGCollective\MarketPlace\Controller\Adminhtml\LoginAsSellerLog;

use Magento\Backend\App\Action;
use Magento\Framework\View\Result\PageFactory;

class Index extends \TCGCollective\MarketPlace\Controller\Adminhtml\SellerGroup
{
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('TCGCollective_MarketPlace::loginasseller_log');
        $resultPage->getConfig()->getTitle()->prepend(__('Login as Seller Log'));

        return $resultPage;
    }
}
