<?php
namespace TCGCollective\MarketPlace\Model\Source\Catalog\GamesWorkshop;

class GwMiniatureSet extends \TCGCollective\MarketPlace\Model\Source\Catalog\Catalog
{
    public function toOptionArray()
    {
        if (!$this->options) {
            $this->options = $this->catalogHelper->getFilterOptions('miniature_set', true);
        }

        return $this->options;
    }
}
