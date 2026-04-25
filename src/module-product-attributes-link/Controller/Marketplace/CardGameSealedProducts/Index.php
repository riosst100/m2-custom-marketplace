<?php

namespace TCGCollective\ProductAttributesLink\Controller\Marketplace\CardGameSealedProducts;

class Index extends \TCGCollective\ProductAttributesLink\Controller\Marketplace\Index
{   
    public function execute()
    {
        $title = $this->helper->getCustomTableListingPageTitle('sealedproducts', 'card_game');

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set($title);
        return $resultPage;
    }
}
