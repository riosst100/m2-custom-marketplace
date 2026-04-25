<?php

namespace TCGCollective\ProductAttributesLink\Controller\Adminhtml\Import;

class Sets extends \TCGCollective\ProductAttributesLink\Controller\Adminhtml\Index
{
    public function execute()
    {
        $this->messageManager->addNotice(
            'Please make sure you uploaded the "<b>Images</b>" first (optional).'
        );

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__('Upload Sets/Release'));
        return $resultPage;
    }
}
