<?php

namespace TCGCollective\MarketPlace\Block\Adminhtml\ProductListingConfigurations;

use Magento\Backend\Block\Widget\Context;
use Magento\Backend\Block\Widget\Form\Container;
use Magento\Framework\Phrase;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\AbstractBlock;

class Edit extends Container
{
    protected $_objectId;
    protected $_blockGroup;
    protected $_controller;


    /**
     * Core registry
     *
     * @var Registry|null
     */
    protected ?Registry $coreRegistry = null;

    /**
     * Edit constructor.
     * @param Context $context
     * @param Registry $registry
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_objectId = 'form_id';
        $this->_blockGroup = 'TCGCollective_MarketPlace';
        $this->_controller = 'adminhtml_productListingConfigurations';

        parent::_construct();

        // if ($this->isAllowedAction('Lof_Formbuilder::form_save')) {
        //     if ($this->coreRegistry->registry('formbuilder_form')->getId()) {
        //         $this->buttonList->add(
        //             'export_csv',
        //             [
        //                 'label' => __('Export Messages to CSV'),
        //                 'class' => 'save',
        //                 'id' => 'export_csv'
        //             ],
        //             0
        //         );
        //     }
        // }
        if ($this->isAllowedAction('Lof_Formbuilder::form_delete')) {
            $this->buttonList->update('delete', 'label', __('Delete Form'));
        } else {
            $this->buttonList->remove('delete');
        }
    }

    /**
     * Retrieve text for header element depending on loaded page
     *
     * @return Phrase
     */
    public function getHeaderText()
    {
        if ($this->coreRegistry->registry('formbuilder_form')->getId()) {
            return __(
                "Edit Form '%1'",
                $this->escapeHtml($this->coreRegistry->registry('formbuilder_form')->getTitle())
            );
        } else {
            return __('New Form');
        }
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function isAllowedAction(string $resourceId): bool
    {
        return $this->_authorization->isAllowed($resourceId);
    }

    /**
     * Prepare layout
     *
     * @return AbstractBlock
     */
    protected function _prepareLayout()
    {

        $this->_formScripts[] = "
        require([
        'jquery',
        'mage/backend/form'
        ], function(){
            jQuery('#save-duplicate-button').click(function(){
                var actionUrl = jQuery('#edit_form').attr('action') + 'duplicate/1';
                jQuery('#edit_form').attr('action', actionUrl);
                jQuery('#edit_form').submit();
            });

            jQuery('#save-new-button').click(function(){
                var actionUrl = jQuery('#edit_form').attr('action') + 'new/1';
                jQuery('#edit_form').attr('action', actionUrl);
                jQuery('#edit_form').submit();
            });

            jQuery('#export_csv').click(function(){
                var actionUrl = jQuery('#edit_form').attr('action') + 'export_csv/1';
                jQuery('#edit_form').attr('action', actionUrl);
                jQuery('#edit_form').submit();
                actionUrl = actionUrl.replace('export_csv/1','');
                jQuery('#edit_form').attr('action', actionUrl);
            });

            function toggleEditor() {
                if (tinyMCE.getInstanceById('before_form_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'before_form_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'before_form_content');
                }
            };
        });";
        return parent::_prepareLayout();
    }
}
