<?php
namespace TCGCollective\MarketPlace\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;

class SideFiltersSequence extends AbstractFieldArray
{
    /**
     * @var \Magento\Framework\View\Element\BlockInterface
     */
    protected $paymentMethod;
    protected $textareaRenderer;

    protected function _prepareToRender()
    {
        // For Customer Groups
        $this->addColumn('category_id', [
            'label' => __('Category'),
            'renderer' => $this->getCategoriesRenderer()
        ]);

        // For Payment Methods
        $this->addColumn('filters', [
            'label' => __('Filters Squence (Without Prefix)'),
            'renderer' => $this->getTextareaRenderer()
        ]);

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
}