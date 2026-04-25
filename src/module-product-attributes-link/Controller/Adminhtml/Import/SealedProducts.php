<?php

namespace TCGCollective\ProductAttributesLink\Controller\Adminhtml\Import;

class SealedProducts extends \TCGCollective\ProductAttributesLink\Controller\Adminhtml\Index
{
    public function execute()
    {
        // $this->messageManager->addNotice(
        //     'Please make sure you uploaded the "<b>Sets</b>" first or "<b>Drop</b>" for Secret Lair.'
        // );

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__('Upload Sealed Products'));
        return $resultPage;
    }
}
