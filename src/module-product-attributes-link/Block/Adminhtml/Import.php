<?php 

namespace TCGCollective\ProductAttributesLink\Block\Adminhtml;

class Import extends \Magento\Backend\Block\Widget\Form\Container
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
        $this->_controller = 'adminhtml_import';
        parent::_construct();
        
        $this->buttonList->remove('back');
        $this->buttonList->update('save', 'label', __('Import'));
        $this->buttonList->remove('reset');

        $this->addButton(
            'backhome',
            [
                'label' => __('Back'),
                'on_click' => sprintf("location.href = '%s';", $this->getUrl('productattributeslink/index/index')),
                'class' => 'back',
                'level' => -2
            ]
        );
    }
    
    public function getHeaderText()
    {
        return __('Import Attributes Mapping');
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

        return $this->getUrl('productattributeslink/index/runImport');
    }
}