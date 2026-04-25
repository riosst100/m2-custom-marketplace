<?php

namespace TCGCollective\ProductAttributesLink\Controller\Marketplace\ToysSets;

class Index extends \TCGCollective\ProductAttributesLink\Controller\Marketplace\Index
{
    public function execute()
    {
        $attrId = $this->getRequest()->getParam('toys_brand');
        $title = $this->helper->getOptionLabelById('toys_brand', $attrId);

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set($title . ' | Sets/Release');
        return $resultPage;
    }
}
