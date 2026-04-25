<?php
namespace TCGCollective\MarketplaceConfigurableProduct\Model\Source\Catalog\Variations;

use Magento\Framework\Option\ArrayInterface;
use TCGCollective\MarketPlace\Helper\VariationsProductCreationHelper;

class LanguageVersion implements ArrayInterface
{
    /**
     * @var array
     */
    private $options;
    
    /**
     * @var VariationsProductCreationHelper
     */
    private $variationsProductCreationHelper;

    /**
     * @param VariationsProductCreationHelper $variationsProductCreationHelper
     */
    public function __construct(
        VariationsProductCreationHelper $variationsProductCreationHelper
    ) {
        $this->variationsProductCreationHelper = $variationsProductCreationHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        if (!$this->options) {
            $this->options = $this->variationsProductCreationHelper->getFilterOptions('language_version', false);
        }

        return $this->options;
    }
}