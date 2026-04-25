<?php

namespace TCGCollective\MarketPlace\Block\Adminhtml\Product\Edit\Button;

use Magento\Ui\Component\Control\Container;
use Magento\Catalog\Block\Adminhtml\Product\Edit\Button\Generic;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableType;

class Unpublish extends Generic
{
    /**
     * @inheritdoc
     */
    public function getButtonData()
    {
        $objectManager =  \Magento\Framework\App\ObjectManager::getInstance();
        $this->request = $objectManager->get('\Magento\Framework\App\RequestInterface');

        $isPublish = 0;
        if ($this->request->getParam('action') == "publish") {
            $isPublish = 1;
        }

        if (!$isPublish) {
            return [];
        }
        
        return [
            'label' => __('Unpublish'),
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
                                        'is_publish' => $isPublish,
                                        'is_unpublish' => true
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            'class_name' => Container::SPLIT_BUTTON,
            'options' => [],
            'dropdown_button_aria_label' => __('Save options'),
        ];
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
