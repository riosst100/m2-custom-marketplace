<?php
namespace TCGCollective\MarketPlace\Model\Source\Catalog\GamesWorkshop;

class GwMiniatureArmies extends \TCGCollective\MarketPlace\Model\Source\Catalog\Catalog
{
    public function toOptionArray()
    {
        if (!$this->options) {
            $this->options = $this->catalogHelper->getFilterOptions('miniature_armies', true);
        }

        return $this->options;
    }
}
