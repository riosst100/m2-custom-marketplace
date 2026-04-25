<?php

namespace TCGCollective\ProductAttributesLink\Controller\Marketplace\DiecastModelKitsSets;

class Index extends \TCGCollective\ProductAttributesLink\Controller\Marketplace\Index
{
    public function execute()
    {
        $attrId = $this->getRequest()->getParam('diecast_model_kits_brand');
        $title = $this->helper->getOptionLabelById('diecast_model_kits_brand', $attrId);

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set($title . ' | Sets/Release');
        return $resultPage;
    }
}
