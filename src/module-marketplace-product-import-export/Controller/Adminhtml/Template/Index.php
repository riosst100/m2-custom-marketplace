<?php

namespace TCGCollective\MarketplaceProductImportExport\Controller\Adminhtml\Template;

class Index extends \TCGCollective\MarketplaceProductImportExport\Controller\Adminhtml\Index 
{
    /**
     * Attribute Option Mapping list page
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Template'));
        return $resultPage;
    }
}
