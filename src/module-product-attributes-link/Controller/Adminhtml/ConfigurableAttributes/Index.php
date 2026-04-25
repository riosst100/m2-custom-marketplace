<?php

namespace TCGCollective\ProductAttributesLink\Controller\Adminhtml\ConfigurableAttributes;

class Index extends \TCGCollective\ProductAttributesLink\Controller\Adminhtml\Index
{
    /**
     * Attribute Set Mapping list page
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Variations Mapping'));
        return $resultPage;
    }
}
