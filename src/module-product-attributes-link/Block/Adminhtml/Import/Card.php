<?php 

namespace TCGCollective\ProductAttributesLink\Block\Adminhtml\Import;

class Card extends \Magento\Backend\Block\Widget\Form\Container
{
    protected $_objectId;
    protected $_blockGroup;
    protected $_controller;


    protected $_coreRegistry = null;

    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    protected function _construct()
    {
        $this->_objectId = 'entity_id';
        $this->_blockGroup = 'TCGCollective_ProductAttributesLink';
        $this->_controller = 'adminhtml_import_card';
        parent::_construct();
        
        $this->buttonList->remove('back');
        $this->buttonList->update('save', 'label', __('Import'));
        $this->buttonList->remove('reset');
    }
    
    public function getHeaderText()
    {
        return __('Import Card');
    }
    
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }

    
    public function getFormActionUrl()
    {
        if ($this->hasFormActionUrl()) {
            return $this->getData('form_action_url');
        }

        return $this->getUrl('productattributeslink/import/runImport');
    }
}