<?php

namespace TCGCollective\MarketplaceProductImportExport\Controller\Adminhtml\Template;

class NewAction extends \TCGCollective\MarketplaceProductImportExport\Controller\Adminhtml\Index 
{
    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__('Add New Template'));
        return $resultPage;
    }
}
