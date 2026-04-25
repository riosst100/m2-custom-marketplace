<?php

namespace TCGCollective\MarketPlace\Controller\Marketplace\AnimeModelKits;

class Index extends \TCGCollective\MarketPlace\Controller\Marketplace\AbstractMyProducts
{
    public function execute() {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('My Products | Anime Model Kits | Character Kits'));
        return $resultPage;
    }
}
