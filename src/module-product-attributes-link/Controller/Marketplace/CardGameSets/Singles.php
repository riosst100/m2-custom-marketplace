<?php

namespace TCGCollective\ProductAttributesLink\Controller\Marketplace\CardGameSets;

class Singles extends \TCGCollective\ProductAttributesLink\Controller\Marketplace\Index
{   
    public function execute()
    {
        $title = $this->helper->getCustomTableListingPageTitle('singles', 'card_game');

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set($title);
        return $resultPage;
    }
}
