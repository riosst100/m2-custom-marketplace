<?php

namespace TCGCollective\ProductAttributesLink\Controller\Adminhtml\AttributeOption;

class Edit extends \TCGCollective\ProductAttributesLink\Controller\Adminhtml\Index
{
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__('Edit Attribute Option Mapping'));
        return $resultPage;
    }
}
