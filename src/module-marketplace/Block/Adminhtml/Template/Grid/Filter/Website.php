<?php

namespace TCGCollective\MarketPlace\Block\Adminhtml\Template\Grid\Filter;

use Magento\Backend\Block\Widget\Grid\Column\Filter\Select;
use Magento\Store\Model\System\Store;

class Website extends Select
{
    protected Store $systemStore;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Framework\DB\Helper $resourceHelper,
        Store $systemStore,
        array $data = []
    ) {
        $this->systemStore = $systemStore;
        parent::__construct($context, $resourceHelper, $data);
    }

    protected function _getOptions()
    {
        $options = [
            ['label' => __('All Websites'), 'value' => '']
        ];

        foreach ($this->systemStore->getWebsiteCollection() as $website) {
            $options[] = [
                'label' => $website->getName(),
                'value' => $website->getId()
            ];
        }

        return $options;
    }
}
