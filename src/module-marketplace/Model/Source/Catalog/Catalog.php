<?php
namespace TCGCollective\MarketPlace\Model\Source\Catalog;

use Magento\Framework\Option\ArrayInterface;
use TCGCollective\MarketPlace\Helper\Catalog as CatalogHelper;

class Catalog implements ArrayInterface
{
    /**
     * @var array
     */
    protected $options;
    
    /**
     * @var CatalogHelper
     */
    protected $catalogHelper;

    /**
     * @param CatalogHelper $catalogHelper
     */
    public function __construct(
        CatalogHelper $catalogHelper
    ) {
        $this->catalogHelper = $catalogHelper;
    }

    public function toOptionArray()
    {
        return $this->options;
    }
}