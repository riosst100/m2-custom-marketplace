<?php
namespace TCGCollective\MarketPlace\Model\Source\Catalog\GamesWorkshop;

class GwMiniatureFaction extends \TCGCollective\MarketPlace\Model\Source\Catalog\Catalog
{
    public function toOptionArray()
    {
        if (!$this->options) {
            $this->options = $this->catalogHelper->getFilterOptions('miniature_faction', true);
        }

        return $this->options;
    }
}
