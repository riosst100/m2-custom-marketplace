<?php
namespace TCGCollective\MarketPlace\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;

class PrefixMapping extends AbstractFieldArray
{
    /**
     * @var \Magento\Framework\View\Element\BlockInterface
     */
    protected $paymentMethod;
    protected $textareaRenderer;

    protected function _prepareToRender()
    {
        // For Customer Groups
        // $this->addColumn('category_id', [
        //     'label' => __('Category'),
        //     'renderer' => $this->getCategoriesRenderer()
        // ]);

        $this->addColumn('main_attr_label', ['label' => __('Game / Categories'), 'class' => 'required-entry']);     
        $this->addColumn('prefix', ['label' => __('Prefix'), 'class' => 'required-entry']);     

        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
    }

    protected function _prepareArrayRow(DataObject $row)
    {
        // dd($row);
        $filters = $row['filters'] ?? [];
        if ($filters) {
        
        }

        $options = [];

        $categories = $row->getCategoryId();
        if ($categories) {
            $options['option_' . $this->getCategoriesRenderer()->calcOptionHash($categories)] = 'selected="selected"';
        }
        
        $row->setData('option_extra_attrs', $options);
    }

    protected function getTextareaRenderer()
    {
        if (!$this->textareaRenderer) {
            $this->textareaRenderer = $this->getLayout()->createBlock(
                \TCGCollective\MarketPlace\Block\Adminhtml\Form\Field\TextareaColumn::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->textareaRenderer;
    }

    protected function getCategoriesRenderer()
    {
        
        if (!$this->paymentMethod) {
        $this->paymentMethod = $this->getLayout()->createBlock(
            \TCGCollective\MarketPlace\Block\Adminhtml\Form\Field\CategoriesColumn::class,
            '',
            ['data' => ['is_render_to_js_template' => true]]
        );
    }
    return $this->paymentMethod;
    }

    /**
     * Get the element HTML
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $html = parent::_getElementHtml($element);
        $script = '<script>
                document.addEventListener("DOMContentLoaded", function(event) {
                    require([
                        \'jquery\',
                        \'Magento_Theme/js/sortable\'
                    ], function ($) {
                        setTimeout(function () {
                            $(\'#row_marketplace_catalog_prefix_configurations_prefix\').sortable({
                                containment: "parent",
                                items: \'tr\',
                                tolerance: \'pointer\',
                            });
                        }, 1000);
                    });
                });
            </script>';
        $html .= $script;
        return $html;
    }
}