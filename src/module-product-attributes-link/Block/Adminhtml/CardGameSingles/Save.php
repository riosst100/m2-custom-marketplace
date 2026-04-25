<?php

namespace TCGCollective\ProductAttributesLink\Block\Adminhtml\CardGameSingles;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class Save implements ButtonProviderInterface 
{
    /**
     * Url Builder
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    protected $request;

    /**
     * CustomButton constructor.
     *
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->request = $request;
        $this->urlBuilder = $context->getUrlBuilder();
    }

    public function getButtonData() 
    {
        $ispopup = 0;
        if ($this->request->getParam('popup')) {
            $ispopup = 1;
        }
        
        $data = [
            'label' => __('Save'),
            'class' => 'save primary',
            'on_click' => '',
            'sort_order' => 80,
            'data_attribute' => [
                'mage-init' => [
                    'Magento_Ui/js/form/button-adapter' => [
                        'actions' => [
                            [
                                'targetName' => 'cardgame_singles_form.cardgame_singles_form',
                                'actionName' => 'save',
                                'params' => [
                                    true,
                                    ['popup' => $ispopup],
                                ]
                            ]
                        ]
                    ]
                ],

            ]
        ];

        return $data;
    }

    public function getUrl($route = '', $params = [])
    {
        return $this->urlBuilder->getUrl($route, $params);
    }
}