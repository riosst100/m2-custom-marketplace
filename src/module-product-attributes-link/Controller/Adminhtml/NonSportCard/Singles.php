<?php

namespace TCGCollective\ProductAttributesLink\Controller\Adminhtml\NonSportCard;

class Singles extends \TCGCollective\ProductAttributesLink\Controller\Adminhtml\Index
{
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__('Singles Database | Non Sport Cards'));
        return $resultPage;
    }
}
