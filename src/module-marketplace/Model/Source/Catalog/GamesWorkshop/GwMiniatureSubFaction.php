<?php
namespace TCGCollective\MarketPlace\Model\Source\Catalog\GamesWorkshop;

class GwMiniatureSubFaction extends \TCGCollective\MarketPlace\Model\Source\Catalog\Catalog
{
    public function toOptionArray()
    {
        if (!$this->options) {
            $this->options = $this->catalogHelper->getFilterOptions('miniature_sub_faction', true);
        }

        return $this->options;
    }
}
