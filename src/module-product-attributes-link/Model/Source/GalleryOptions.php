<?php
namespace TCGCollective\ProductAttributesLink\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

class GalleryOptions implements OptionSourceInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => '1', 'label' => __('Gallery Item 1')],
            ['value' => '2', 'label' => __('Gallery Item 2')],
        ];
    }
}