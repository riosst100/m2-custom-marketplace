<?php

namespace TCGCollective\MarketplaceProductPreview\Block\Adminhtml\Product\Edit\Button;

use Magento\Ui\Component\Control\Container;
use Magento\Catalog\Block\Adminhtml\Product\Edit\Button\Generic;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableType;

class PublishButton extends Generic
{
    /**
     * @inheritdoc
     */
    public function getButtonData()
    {
        if (!$this->getProduct()->getId()) {
            return [];
        }

        $objectManager =  \Magento\Framework\App\ObjectManager::getInstance();
        $this->request = $objectManager->get('\Magento\Framework\App\RequestInterface');

        if ($this->request->getParam('action') != "publish") {
            return [];
        }

        $countryName = $this->request->getParam('country');

        return [
            'label' => $countryName ? __('Publish To %1', $countryName) : __('Publish'),
            'class' => 'primary',
            'on_click' => 'return false;',
            'data_attribute' => [
                'mage-init' => [
                    'buttonAdapter' => [
                        'actions' => [
                            [
                                'targetName' => $this->getSaveTarget(),
                                'actionName' => $this->getSaveAction(),
                                'params' => [
                                    false,
                                    [
                                        'is_publish' => true,
                                        'website_id' => $this->request->getParam('website_id')
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
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
