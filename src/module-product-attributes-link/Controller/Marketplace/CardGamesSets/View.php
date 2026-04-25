<?php

namespace TCGCollective\ProductAttributesLink\Controller\Marketplace\CardGamesSets;

class View extends \TCGCollective\ProductAttributesLink\Controller\Marketplace\Index
{   
    public function execute()
    {
        $setId = $this->getRequest()->getParam('entity_id');
        $sets = $this->helper->getActionToySetsById($setId);
        $title = $sets->getData('set_name');

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set($title);
        return $resultPage;
    }
}
