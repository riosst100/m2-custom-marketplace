<?php

namespace TCGCollective\MarketPlace\Model\Category\Attribute\Source;

class CustomLandingPage extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**
     * @inheritdoc
     */
    public function getAllOptions()
    {
        if ($this->_options === null) {
            $this->_options[] = [
                'label' => __('-- Select landing page --'),
                'value' => null
            ];

            $this->_options[] = [
                'label' => __('Card Games Sets'),
                'value' => 'card_games_sets'
            ];

            $this->_options[] = [
                'label' => __('Sport Cards Sets'),
                'value' => 'sport_cards_sets'
            ];

            $this->_options[] = [
                'label' => __('Lego Sets'),
                'value' => 'lego_sets'
            ];
        }

        return $this->_options;
    }
}
