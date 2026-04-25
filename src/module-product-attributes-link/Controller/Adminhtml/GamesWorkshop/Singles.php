<?php

namespace TCGCollective\ProductAttributesLink\Controller\Adminhtml\GamesWorkshop;

class Singles extends \TCGCollective\ProductAttributesLink\Controller\Adminhtml\Index
{
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__('Singles Database | Games Workshop'));
        return $resultPage;
    }
}
