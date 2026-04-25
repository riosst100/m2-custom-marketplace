<?php

namespace TCGCollective\ProductAttributesLink\Controller\Marketplace\CardGameSets;

class View extends \TCGCollective\ProductAttributesLink\Controller\Marketplace\Index
{   
    public function execute()
    {
        $setId = $this->getRequest()->getParam('entity_id');
        $sets = $this->helper->getCardGameSetsById($setId);
        $title = $sets->getData('card_set');

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set($title);
        return $resultPage;
    }
}
