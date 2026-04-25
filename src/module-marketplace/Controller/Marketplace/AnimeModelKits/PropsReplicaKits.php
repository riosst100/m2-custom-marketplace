<?php
namespace TCGCollective\MarketPlace\Controller\Marketplace\AnimeModelKits;

class PropsReplicaKits extends \TCGCollective\MarketPlace\Controller\Marketplace\AbstractMyProducts
{
    public function execute() {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('My Products | Anime Model Kits | Props & Replica Kits'));
        return $resultPage;
    }
}
