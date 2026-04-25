<?php

namespace TCGCollective\MarketPlace\Controller\Adminhtml\ProductListing\Configurations;

use Magento\Backend\App\Action;
use Magento\Framework\View\Result\PageFactory;

class Index extends \TCGCollective\ProductAttributesLink\Controller\Adminhtml\Index
{
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('TCGCollective_MarketPlace::product_listing_categories_config');
        $resultPage->getConfig()->getTitle()->prepend(__('Product Listing Configurations'));

        return $resultPage;
    }
}
