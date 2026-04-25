<?php

namespace TCGCollective\ProductAttributesLink\Block\Adminhtml\Generic;

use Magento\Backend\Block\Widget\Context;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class CustomAttributesBackButton implements ButtonProviderInterface
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * @var \Magento\Framework\App\Response\RedirectInterface
     */
    protected $redirect;

    /**
     * @param Context $context
     * @param \Magento\Framework\App\Response\RedirectInterface $redirect
     */
    public function __construct(
        Context $context,
        \Magento\Framework\App\Response\RedirectInterface $redirect
    ) {
        $this->context = $context;
        $this->redirect = $redirect;
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

    public function getButtonData()
    {
        if ($this->context->getRequest()->getParam('popup')) {
            return '';
        }

        $backUrl = $this->redirect->getRefererUrl();

        $urlKey = null;

        $categories = $this->context->getRequest()->getParam('categories');
        if ($categories == "card_game") {
            $urlKey = 'cardgameattributes';
        }

        if ($categories == "action_toys") {
            $urlKey = 'actiontoysattributes';
        }

        if ($urlKey) {
            $backUrl = $this->getUrl('*/' . $urlKey . '/index');
        }

        return [
            'label' => __('Back'),
            'on_click' => sprintf("location.href = '%s';", $backUrl),
            'class' => 'back',
            'sort_order' => 10
        ];
    }
}