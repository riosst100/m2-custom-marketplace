<?php

namespace TCGCollective\ProductAttributesLink\Controller\Marketplace\SportCardsSingles;

class Index extends \TCGCollective\ProductAttributesLink\Controller\Marketplace\Index
{   
    public function execute()
    {
        $setId = $this->getRequest()->getParam('set_id');
        $sets = $this->helper->getSportCardSetsById($setId);
        $attrId = $sets->getData('sc_categories');
        $title = $this->helper->getOptionLabelById('sc_categories', $attrId).' | '.$sets->getData('sc_set_name');

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set($title . ' | ' . 'Singles');
        return $resultPage;
    }
}
