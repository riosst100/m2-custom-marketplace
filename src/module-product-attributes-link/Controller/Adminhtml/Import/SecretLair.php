<?php

namespace TCGCollective\ProductAttributesLink\Controller\Adminhtml\Import;

class SecretLair extends \TCGCollective\ProductAttributesLink\Controller\Adminhtml\Index
{
    public function execute()
    {
        $this->messageManager->addNotice(
            'Please make sure you uploaded the "<b>Sets</b>" first.'
        );

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__('Upload Secret Lair'));
        return $resultPage;
    }
}
