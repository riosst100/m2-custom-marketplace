<?php
namespace TCGCollective\ProductAttributesLink\Model\Source\ActionToys;

use Magento\Framework\Option\ArrayInterface;
use TCGCollective\ProductAttributesLink\Helper\SetsReleaseHelper;

class ToysLine implements ArrayInterface
{
    /**
     * @var array
     */
    private $options;
    
    /**
     * @var SetsReleaseHelper
     */
    private $setsReleaseHelper;

    /**
     * @param SetsReleaseHelper $setsReleaseHelper
     */
    public function __construct(
        SetsReleaseHelper $setsReleaseHelper
    ) {
        $this->setsReleaseHelper = $setsReleaseHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        if (!$this->options) {
            $this->options = $this->setsReleaseHelper->getFilterOptions('toys_line');
        }

        return $this->options;
    }
}