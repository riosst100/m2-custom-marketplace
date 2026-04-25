<?php
namespace TCGCollective\ProductAttributesLink\Model\Source;

use Magento\Framework\Option\ArrayInterface;
use TCGCollective\ProductAttributesLink\Helper\SetsReleaseHelper;

class CardSeries implements ArrayInterface
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
            $this->options = $this->setsReleaseHelper->getFilterOptions('card_series');
        }

        return $this->options;
    }
}