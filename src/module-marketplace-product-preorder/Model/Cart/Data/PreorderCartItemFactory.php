<?php
namespace TCGCollective\MarketplaceProductPreorder\Model\Cart\Data;

use Magento\Quote\Model\Cart\Data\CartItem;

class PreorderCartItemFactory extends \Magento\Quote\Model\Cart\Data\CartItemFactory
{
    public function create(array $data): CartItem
    {
        // dd($data);
        $item = new PreorderCartItem();

        // Set Magento-provided fields
        foreach ($data as $key => $value) {
            $setter = 'set' . str_replace(' ', '', ucwords(str_replace('_', ' ', $key)));

            if (method_exists($item, $setter)) {
                $item->{$setter}($value);
            }
        }

        return $item;
    }
}
