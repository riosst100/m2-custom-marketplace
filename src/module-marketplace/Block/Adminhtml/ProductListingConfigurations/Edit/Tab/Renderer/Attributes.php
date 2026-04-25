<?php

namespace TCGCollective\MarketPlace\Block\Adminhtml\ProductListingConfigurations\Edit\Tab\Renderer;

use Magento\Backend\Block\Template;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;
use Magento\Eav\Model\Config;

class Attributes extends Template implements RendererInterface
{
    protected $eavConfig;
    protected $element;

    public function __construct(
        Template\Context $context,
        Config $eavConfig,
        array $data = []
    ) {
        $this->eavConfig = $eavConfig;
        parent::__construct($context, $data);
    }

    protected function _construct()
    {
        $this->setTemplate('TCGCollective_MarketPlace::attributes.phtml');
    }

    /**
     * WAJIB untuk custom renderer
     */
    public function render(AbstractElement $element)
    {
        $this->element = $element;
        return $this->toHtml();
    }

    public function getElement()
    {
        return $this->element;
    }

    public function getAttributes()
    {
        return $this->eavConfig
            ->getEntityType('catalog_product')
            ->getAttributeCollection();
    }

    public function getSelectedValues()
    {
        $value = $this->getElement()->getValue();

        if (!$value) {
            return [];
        }

        if (is_string($value)) {
            return json_decode($value, true) ?? [];
        }

        return $value;
    }
}