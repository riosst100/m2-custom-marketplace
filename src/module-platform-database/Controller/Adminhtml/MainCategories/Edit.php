<?php

namespace TCGCollective\PlatformDatabase\Controller\Adminhtml\MainCategories;

class Edit extends \TCGCollective\PlatformDatabase\Controller\Adminhtml\Index
{   
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__('Edit Main Categories'));
        return $resultPage;
    }
}
