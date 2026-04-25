<?php
namespace TCGCollective\ProductAttributesLink\Model\Source\Import\Drop;

use Magento\Framework\Option\ArrayInterface;

class Entity implements ArrayInterface
{
    public function toOptionArray()
    {
        return [
            ['label' => '', 'value' => ''],
            ['label' => 'Collectible Card Games', 'value' => 'card_game']
        ];
    }
}