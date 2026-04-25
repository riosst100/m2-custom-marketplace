<?php

namespace TCGCollective\ProductAttributesLink\Controller\Marketplace\CardGameSingles;

class View extends \TCGCollective\ProductAttributesLink\Controller\Marketplace\Index
{   
    public function execute()
    {
        $cardId = $this->getRequest()->getParam('entity_id');
        $card = $this->helper->getCardGameCardById($cardId);
        $title = $card->getCardName();

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set($title);
        return $resultPage;
    }
}
