<?php

namespace TCGCollective\ProductAttributesLink\Controller\Marketplace\ToysSingles;

class Index extends \TCGCollective\ProductAttributesLink\Controller\Marketplace\Index
{
    public function execute()
    {
        $setId = $this->getRequest()->getParam('set_id');
        $sets = $this->helper->getSetsById('actiontoys', $setId);
        $attrId = $sets->getData('toys_brand');
        $title = $this->helper->getOptionLabelById('toys_brand', $attrId).' | '.$sets->getData('set_name');

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set($title . ' | ' . 'Singles');
        return $resultPage;
    }
}
