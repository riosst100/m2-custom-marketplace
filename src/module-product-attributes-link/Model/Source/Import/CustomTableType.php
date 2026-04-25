<?php
namespace TCGCollective\ProductAttributesLink\Model\Source\Import;

use Magento\Framework\Option\ArrayInterface;

class CustomTableType implements ArrayInterface
{
    public function toOptionArray()
    {
        return [
            ['label' => '', 'value' => ''],
            ['label' => 'Sets / Release Database', 'value' => 'sets'],
            ['label' => 'Sealed Products Database', 'value' => 'sealedproducts'],
            ['label' => 'Singles Database', 'value' => 'singles']
        ];
    }
}