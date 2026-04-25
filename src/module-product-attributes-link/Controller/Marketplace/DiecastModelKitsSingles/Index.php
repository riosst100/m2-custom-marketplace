<?php

namespace TCGCollective\ProductAttributesLink\Controller\Marketplace\DiecastModelKitsSingles;

class Index extends \TCGCollective\ProductAttributesLink\Controller\Marketplace\Index
{
    public function execute()
    {
        $setId = $this->getRequest()->getParam('set_id');
        $sets = $this->helper->getSetsById('diecastmodelkits', $setId);
        $attrId = $sets->getData('diecast_model_kits_brand');
        $title = $this->helper->getOptionLabelById('diecast_model_kits_brand', $attrId).' | '.$sets->getData('model');

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set($title . ' | ' . 'Singles');
        return $resultPage;
    }
}
