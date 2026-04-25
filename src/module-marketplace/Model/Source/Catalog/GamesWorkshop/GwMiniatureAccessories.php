<?php
namespace TCGCollective\MarketPlace\Model\Source\Catalog\GamesWorkshop;

class GwMiniatureAccessories extends \TCGCollective\MarketPlace\Model\Source\Catalog\Catalog
{
    public function toOptionArray()
    {
        if (!$this->options) {
            $this->options = $this->catalogHelper->getFilterOptions('gw_miniature_accessories', false);
        }

        return $this->options;
    }
}