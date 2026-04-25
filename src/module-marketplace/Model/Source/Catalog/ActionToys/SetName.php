<?php
namespace TCGCollective\MarketPlace\Model\Source\Catalog\ActionToys;

use Magento\Framework\Option\ArrayInterface;
use TCGCollective\MarketPlace\Helper\Catalog as CatalogHelper;

class SetName implements ArrayInterface
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
            $this->options = $this->catalogHelper->getFilterOptions('set_name', true);
        }

        return $this->options;
    }
}