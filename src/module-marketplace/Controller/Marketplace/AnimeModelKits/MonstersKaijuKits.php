<?php
namespace TCGCollective\MarketPlace\Controller\Marketplace\AnimeModelKits;

class MonstersKaijuKits extends \TCGCollective\MarketPlace\Controller\Marketplace\AbstractMyProducts
{
    public function execute() {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('My Products | Anime Model Kits | Monsters/Kaiju Kits'));
        return $resultPage;
    }
}
