<?php

namespace TCGCollective\MarketPlace\Model\Category\Attribute\Source;

class Filters extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
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
            $this->_options[] = [
                'label' => 'category_uid',
                'value' => 'category_uid'
            ];
            foreach ($this->helper->getAllProductAttributes() as $attribute) {
                if ($attribute->getFrontendLabel()) {
                    $this->_options[] = [
                        'label' => $attribute->getAttributeCode(),
                        'value' => $attribute->getAttributeCode()
                    ];
                }
            }
        }

        if (count($this->_options)) {
            usort($this->_options, fn($a, $b) => $a['label'] <=> $b['label']);
        }

        return $this->_options;
    }
}
