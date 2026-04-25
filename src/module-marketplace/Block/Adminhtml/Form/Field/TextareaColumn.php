<?php

namespace TCGCollective\MarketPlace\Block\Adminhtml\Form\Field;

use Magento\Framework\View\Element\Template;

class TextareaColumn extends Template
{
    protected $_template = 'TCGCollective_MarketPlace::form/field/textarea-column.phtml';
    protected $catalogHelper;

    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \TCGCollective\MarketPlace\Helper\Catalog $catalogHelper,
        array $data = []
    ) {
        $this->catalogHelper = $catalogHelper;
        parent::__construct($context, $data);
    }

    public function getColumnName()
    {
        return $this->getData('column_name');
    }

    public function getValue($columnName) 
    {
        // die('<pre>'.print_r($this->getData(),true));

        // $inputName = $this->getInputName();
        // // die($inputName);
        // $configValueArr = $this->catalogHelper->getArraySerializedConfigValue('filters/side_filters/sequence');
        // // die('<pre>'.print_r($configValueArr,true));
        // return $value;
    }
}