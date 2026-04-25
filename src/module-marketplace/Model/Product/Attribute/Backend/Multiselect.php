<?php

namespace TCGCollective\MarketPlace\Model\Product\Attribute\Backend;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

class Multiselect extends AbstractSource
{
    protected $optionFactory;

    public function getAllOptions()
    {
        dd('dd');
        $this->_options = [];
        $this->_options[] = ['label' => 'Label 1', 'value' => 'value 1'];
        $this->_options[] = ['label' => 'Label 2', 'value' => 'value 2'];
    
        return $this->_options;
    }
}

