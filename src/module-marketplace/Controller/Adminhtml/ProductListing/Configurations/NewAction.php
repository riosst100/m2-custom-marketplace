<?php

namespace TCGCollective\MarketPlace\Controller\Adminhtml\ProductListing\Configurations;

use Magento\Backend\App\Action;
use Magento\Framework\View\Result\PageFactory;

class NewAction extends \TCGCollective\ProductAttributesLink\Controller\Adminhtml\Index
{
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('TCGCollective_MarketPlace::product_listing_categories_config');
        $resultPage->getConfig()->getTitle()->prepend(__('New Product Listing Configurations'));

        return $resultPage;
    }
}
