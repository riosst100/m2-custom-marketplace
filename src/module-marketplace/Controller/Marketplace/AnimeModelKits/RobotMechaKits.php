<?php
namespace TCGCollective\MarketPlace\Controller\Marketplace\AnimeModelKits;

class RobotMechaKits extends \TCGCollective\MarketPlace\Controller\Marketplace\AbstractMyProducts
{
    public function execute() {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('My Products | Anime Model Kits | Robot/Mecha Kits'));
        return $resultPage;
    }
}
