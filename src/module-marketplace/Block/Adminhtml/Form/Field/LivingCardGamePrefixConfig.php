<?php 
namespace TCGCollective\MarketPlace\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
 
class LivingCardGamePrefixConfig extends AbstractFieldArray
{
    protected function _prepareToRender()
    {
        $this->addColumn('category', ['label' => __('Living Card Game'), 'class' => 'required-entry']);
        $this->addColumn('prefix', ['label' => __('Prefix'), 'class' => 'required-entry']);        
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add New');
    }
}