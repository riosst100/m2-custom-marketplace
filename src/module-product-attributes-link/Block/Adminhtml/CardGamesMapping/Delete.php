<?php

namespace TCGCollective\ProductAttributesLink\Block\Adminhtml\CardGamesMapping;

use Magento\Backend\Block\Widget\Context;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class Delete implements ButtonProviderInterface
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * @param Context $context
     */
    public function __construct(
        Context $context
    ) {
        $this->context = $context;
    }

    /**
     * Generate url by route and parameters
     *
     * @param string $route
     * @param array $params
     * @return string
     */
    protected function getUrl($route = '', $params = [])
    {
        return $this->context->getUrlBuilder()->getUrl($route, $params);
    }

    /**
     * @return array
     */
    public function getButtonData()
    {
        $data = [];
        $mappingId = $this->context->getRequest()->getParam('entity_id');
        if ($mappingId) {
            $data = [
                'label' => __('Delete'),
                'class' => 'delete',
                'on_click' => "deleteConfirm('" . __('Are you sure you want to do this?') .
                    "', '" . $this->getUrl('*/*/massDelete', ['id' => $mappingId]) . "')",
                'sort_order' => 20,
            ];
        }
        return $data;
    }
}