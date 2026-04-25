<?php

namespace TCGCollective\ProductAttributesLink\Controller\Marketplace\BricksSingles;

class Index extends \TCGCollective\ProductAttributesLink\Controller\Marketplace\Index
{
    public function execute()
    {
        $setId = $this->getRequest()->getParam('set_id');
        $sets = $this->helper->getLegoSetsById($setId);
        $attrId = $sets->getData('bricks_brand');
        $title = $this->helper->getOptionLabelById('bricks_brand', $attrId).' | '.$sets->getData('bricks_set_name');

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set($title . ' | Singles');
        return $resultPage;
    }
}
