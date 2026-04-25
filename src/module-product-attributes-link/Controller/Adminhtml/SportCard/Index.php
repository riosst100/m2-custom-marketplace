<?php

namespace TCGCollective\ProductAttributesLink\Controller\Adminhtml\SportCard;

class Index extends \TCGCollective\ProductAttributesLink\Controller\Adminhtml\Index
{
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__('Sets / Release Database | Sport Cards'));
        return $resultPage;
    }
}
