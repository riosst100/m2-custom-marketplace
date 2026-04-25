<?php

namespace TCGCollective\MarketPlace\Controller\Adminhtml\SidebarFilters;

class NewAction extends \TCGCollective\ProductAttributesLink\Controller\Adminhtml\Index
{
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__('Add Attribute to Sidebar Filters'));
        return $resultPage;
    }
}
