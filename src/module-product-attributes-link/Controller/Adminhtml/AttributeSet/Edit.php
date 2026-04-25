<?php

namespace TCGCollective\ProductAttributesLink\Controller\Adminhtml\AttributeSet;

class Edit extends \TCGCollective\ProductAttributesLink\Controller\Adminhtml\Index
{
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__('Edit Attribute Set Mapping'));
        return $resultPage;
    }
}
