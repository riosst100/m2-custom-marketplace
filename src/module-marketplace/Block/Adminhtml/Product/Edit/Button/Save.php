<?php

namespace TCGCollective\MarketPlace\Block\Adminhtml\Product\Edit\Button;

use Magento\Ui\Component\Control\Container;
use Magento\Catalog\Block\Adminhtml\Product\Edit\Button\Generic;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableType;

class Save extends Generic
{
    protected $request;
    
    /**
     * @inheritdoc
     */
    public function getButtonData()
    {
        if ($this->getProduct()->isReadonly()) {
            return [];
        }

        $objectManager =  \Magento\Framework\App\ObjectManager::getInstance();
        $this->request = $objectManager->get('\Magento\Framework\App\RequestInterface');

        $isDuplicate = 0;
        if ($this->request->getParam('is_duplicate')) {
            $isDuplicate = 1;
        }

        $isPublish = 0;
        if ($this->request->getParam('action') == "publish") {
            $isPublish = 1;
        }

        // if ($this->request->getParam('action') == "publish") {
        //     return [];
        // }
        $countryName = $this->request->getParam('country');
        
        return [
            'label' => $isPublish ? __('Save & Publish To %1', $countryName) : __('Save'),
            'class' => 'save primary',
            'data_attribute' => [
                'mage-init' => [
                    'buttonAdapter' => [
                        'actions' => [
                            [
                                'targetName' => $this->getSaveTarget(),
                                'actionName' => $this->getSaveAction(),
                                'params' => [
                                    true,
                                    [
                                        'is_duplicate' => $isDuplicate,
                                        'is_publish' => $isPublish
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            'class_name' => Container::SPLIT_BUTTON,
            'options' => $this->getOptions(),
            'dropdown_button_aria_label' => __('Save options'),
        ];
    }

    /**
     * Retrieve options
     *
     * @return array
     */
    protected function getOptions()
    {
        $options[] = [
            'id_hard' => 'save_and_new',
            'label' => __('Save & New'),
            'data_attribute' => [
                'mage-init' => [
                    'buttonAdapter' => [
                        'actions' => [
                            [
                                'targetName' => $this->getSaveTarget(),
                                'actionName' => $this->getSaveAction(),
                                'params' => [
                                    true,
                                    [
                                        'back' => 'new'
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
        ];

        if (!$this->context->getRequestParam('popup') && $this->getProduct()->isDuplicable()) {
            $options[] = [
                'label' => __('Save & Duplicate'),
                'id_hard' => 'save_and_duplicate',
                'data_attribute' => [
                    'mage-init' => [
                        'buttonAdapter' => [
                            'actions' => [
                                [
                                    'targetName' => $this->getSaveTarget(),
                                    'actionName' => $this->getSaveAction(),
                                    'params' => [
                                        true,
                                        [
                                            'back' => 'duplicate'
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
            ];
        }

        $options[] = [
            'id_hard' => 'save_and_close',
            'label' => __('Save & Close'),
            'data_attribute' => [
                'mage-init' => [
                    'buttonAdapter' => [
                        'actions' => [
                            [
                                'targetName' => $this->getSaveTarget(),
                                'actionName' => $this->getSaveAction(),
                                'params' => [
                                    true
                                ]
                            ]
                        ]
                    ]
                ]
            ],
        ];

        return $options;
    }

    /**
     * Retrieve target for button.
     *
     * @return string
     */
    protected function getSaveTarget()
    {
        $target = 'product_form.product_form';
        if ($this->isConfigurableProduct()) {
            $target = 'product_form.product_form.configurableVariations';
        }
        return $target;
    }

    /**
     * Retrieve action for button.
     *
     * @return string
     */
    protected function getSaveAction()
    {
        $action = 'save';
        if ($this->isConfigurableProduct()) {
            $action = 'saveFormHandler';
        }
        return $action;
    }

    /**
     * Is configurable product.
     *
     * @return boolean
     */
    protected function isConfigurableProduct()
    {
        return !$this->getProduct()->isComposite() || $this->getProduct()->getTypeId() === ConfigurableType::TYPE_CODE;
    }
}
