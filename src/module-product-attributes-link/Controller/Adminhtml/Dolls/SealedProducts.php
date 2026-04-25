<?php

namespace TCGCollective\ProductAttributesLink\Controller\Adminhtml\Dolls;

class SealedProducts extends \TCGCollective\ProductAttributesLink\Controller\Adminhtml\Index
{
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__('Sealed Products Database | Fashion Dolls'));
        return $resultPage;
    }
}
