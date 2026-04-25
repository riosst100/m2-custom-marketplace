<?php

namespace TCGCollective\MarketPlace\Model\Category\Attribute\Source;

class ShopBy extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    protected $_options;


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
     * @inheritdoc
     */
    public function getAllOptions()
    {
        if ($this->_options === null) {
            foreach ($this->helper->getAllProductAttributes() as $attribute) {
                $this->_options[] = [
                    'label' => __($attribute->getFrontendLabel()),
                    'value' => $attribute->getAttributeCode()
                ];
            }
        }
        return $this->_options;
    }
}
