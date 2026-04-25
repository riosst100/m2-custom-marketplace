<?php

namespace TCGCollective\MarketPlace\Controller\Adminhtml\SidebarFilters;

class Index extends \TCGCollective\ProductAttributesLink\Controller\Adminhtml\Index
{
    public function execute()
    {
        $this->messageManager->addNoticeMessage(__('The attributes displayed here are used & showing in the sidebar filters. You can add more attributes to show in the sidebar.'));
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Filters Attributes'));
        return $resultPage;
    }
}
