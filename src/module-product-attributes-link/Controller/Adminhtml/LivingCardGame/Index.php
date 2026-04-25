<?php

namespace TCGCollective\ProductAttributesLink\Controller\Adminhtml\LivingCardGame;

class Index extends \TCGCollective\ProductAttributesLink\Controller\Adminhtml\Index
{
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__('Sets / Release Database | Living Card Games'));
        return $resultPage;
    }
}
