<?php

namespace TCGCollective\MarketPlace\Plugin\User\Edit\Tab;

use Magento\User\Block\User\Edit\Tab\Main;
use Magento\Framework\Data\Form;
use Magento\Store\Model\StoreManagerInterface;

class MainPlugin
{
    public function __construct(
        private StoreManagerInterface $storeManager
    ) {}

    public function afterPrepareForm(Main $subject, $result)
    {
        /** @var Form $form */
        $form = $subject->getForm();
        if (!$form) {
            return $result;
        }

        $fieldset = $form->getElement('base_fieldset');
        if (!$fieldset) {
            return $result;
        }

        // Avoid duplicate field
        if ($form->getElement('website_id')) {
            return $result;
        }

        $fieldset->addField(
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

        return $result;
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
