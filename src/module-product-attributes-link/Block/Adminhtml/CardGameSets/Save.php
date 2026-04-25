<?php

namespace TCGCollective\ProductAttributesLink\Block\Adminhtml\CardGameSets;

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

        $isDuplicate = 0;
        if ($this->request->getParam('is_duplicate')) {
            $isDuplicate = 1;
        }

        // $data = [
        //     'label' => __('Save'),
        //     'class' => 'save primary',
        //     'id' => 'custom_save',
        //     'on_click' => sprintf("location.href = '%s';", $this->getUrl('*/*/save', ['popup' => $ispopup])),
        //     'sort_order' => 20,
        // ];
        
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
                                'targetName' => 'cardgame_sets_form.cardgame_sets_form',
                                'actionName' => 'save',
                                'params' => [
                                    true,
                                    [
                                        'popup' => $ispopup,
                                        'is_duplicate' => $isDuplicate
                                    ],
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