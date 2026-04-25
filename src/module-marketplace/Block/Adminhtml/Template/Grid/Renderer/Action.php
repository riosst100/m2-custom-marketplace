<?php

namespace TCGCollective\MarketPlace\Block\Adminhtml\Template\Grid\Renderer;

class Action extends \Magento\Email\Block\Adminhtml\Template\Grid\Renderer\Action
{
    public function render(\Magento\Framework\DataObject $row)
    {
        $html = parent::render($row);

        $editUrl = $this->getUrl(
            'adminhtml/email_template/edit',
            ['id' => $row->getId()]
        );

        $html .= ' | <a style="display:inline-block" href="' . $editUrl . '">' . __('Edit Template') . '</a>';

        return $html;
    }
}
