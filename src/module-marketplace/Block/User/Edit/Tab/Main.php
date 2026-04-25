<?php

namespace TCGCollective\MarketPlace\Block\User\Edit\Tab;

use Magento\User\Block\User\Edit\Tab\Main as CoreMain;
use Magento\Framework\Data\Form;
use Magento\Store\Model\StoreManagerInterface;

class Main extends CoreMain
{
    protected StoreManagerInterface $storeManager;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Framework\Locale\ListsInterface $localeLists,
        StoreManagerInterface $storeManager,
        array $data = [],
        \Magento\Framework\Locale\OptionInterface $deployedLocales = null
    ) {
        $this->storeManager = $storeManager;
        parent::__construct(
            $context,
            $registry,
            $formFactory,
            $authSession,
            $localeLists,
            $data,
            $deployedLocales
        );
    }

    protected function _prepareForm()
    {
        parent::_prepareForm();

        /** @var Form $form */
        $form = $this->getForm();
        if (!$form) {
            return $this;
        }

        $fieldset = $form->getElement('base_fieldset');
        if (!$fieldset || $form->getElement('website_id')) {
            return $this;
        }

        $field = $fieldset->addField(
            'website_id',
            'select',
            [
                'name'   => 'website_id',
                'label'  => __('Website Access'),
                'title'  => __('Website Access'),
                'values' => $this->getWebsiteOptions(),
                'note'   => __('Select which website this admin user can access. Choose "All Websites" for full access.')
            ]
        );

        // 🔑 IMPORTANT PART — manually set value
        $user = $this->_coreRegistry->registry('permissions_user');
        if ($user && $user->getId()) {
            $field->setValue((int)$user->getWebsiteId());
        }

        return $this;
    }

    private function getWebsiteOptions(): array
    {
        $options[] = [
            'value' => 0,
            'label' => __('All Websites')
        ];

        foreach ($this->storeManager->getWebsites() as $website) {
            $options[] = [
                'value' => (int)$website->getId(),
                'label' => $website->getName()
            ];
        }

        return $options;
    }
}
