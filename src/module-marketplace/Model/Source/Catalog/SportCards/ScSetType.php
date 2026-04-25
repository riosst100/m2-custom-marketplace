<?php
namespace TCGCollective\MarketPlace\Model\Source\Catalog\SportCards;

use Magento\Framework\Option\ArrayInterface;
use TCGCollective\MarketPlace\Helper\Catalog as CatalogHelper;

class ScSetType implements ArrayInterface
{
    /**
     * @var array
     */
    private $options;
    
    /**
     * @var CatalogHelper
     */
    private $catalogHelper;

    /**
     * @param CatalogHelper $catalogHelper
     */
    public function __construct(
        CatalogHelper $catalogHelper
    ) {
        $this->catalogHelper = $catalogHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        if (!$this->options) {
            $this->options = $this->catalogHelper->getFilterOptions('sc_set_type', true);
        }

        return $this->options;
    }
}