<?php

namespace TCGCollective\MarketPlace\Model\Config\Source;

class ProductAttributes implements \Magento\Framework\Option\ArrayInterface
{
     /**
     *
     * @var \TCGCollective\ProductAttributesLink\Helper\Data
     */
    protected $helper;

    /**
     * Construct
     *
     * @param \TCGCollective\ProductAttributesLink\Helper\Data $helper
     */
    public function __construct(\TCGCollective\ProductAttributesLink\Helper\Data $helper)
    {
        $this->helper = $helper;
    }
    
    /**
     * Options getter
     *
     * @param bool $addEmpty
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];

        foreach ($this->helper->getAllProductAttributes() as $attribute) {
            $options[] = [
                'label' => __($attribute->getFrontendLabel()),
                'value' => $attribute->getAttributeCode()
            ];
        }

        return $options;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        $array = [];
        foreach ($this->toOptionArray() as $item) {
            $array[$item['value']] = $item['label'];
        }
        return $array;
    }
}
