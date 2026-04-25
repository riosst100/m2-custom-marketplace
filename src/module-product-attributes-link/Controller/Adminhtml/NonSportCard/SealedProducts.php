<?php

namespace TCGCollective\ProductAttributesLink\Controller\Adminhtml\NonSportCard;

class SealedProducts extends \TCGCollective\ProductAttributesLink\Controller\Adminhtml\Index
{
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__('Sealed Products Database | Non Sport Cards'));
        return $resultPage;
    }
}
