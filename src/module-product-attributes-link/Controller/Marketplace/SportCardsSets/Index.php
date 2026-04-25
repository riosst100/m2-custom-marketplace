<?php

namespace TCGCollective\ProductAttributesLink\Controller\Marketplace\SportCardsSets;

class Index extends \TCGCollective\ProductAttributesLink\Controller\Marketplace\Index
{
    public function execute()
    {
        $attrId = $this->getRequest()->getParam('sc_categories');
        $title = $this->helper->getOptionLabelById('sc_categories', $attrId);

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set($title . ' | Sets/Release');
        return $resultPage;
    }
}
