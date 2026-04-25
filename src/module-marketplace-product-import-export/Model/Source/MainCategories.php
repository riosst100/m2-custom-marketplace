<?php

namespace TCGCollective\MarketplaceProductImportExport\Model\Source;

use TCGCollective\ProductAttributesLink\Helper\Data;
use Psr\Log\LoggerInterface as Logger;
use Magento\Framework\Data\OptionSourceInterface;

class MainCategories implements OptionSourceInterface
{
    /**
     * @var array
     */
    private $options;

    /**
     * @var Logger
     */
    private $logger;
    
    /**
     * @var Data
     */
    private $helper;

    /**
     * @param Logger $logger
     * @param Data $helper
     */
    public function __construct(
        Logger $logger,
        Data $helper
    ) {
        $this->logger = $logger;
        $this->helper = $helper;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        if (!$this->options) {
            $this->options = [
                [
                    'label' => ' ',
                    'value' => ''
                ]
            ];

            $items = [];
            
            try {
                $categories = [
                    [
                        'name' => __('Anime / Action Toys'),
                        'value' => 'action_toys'
                    ],
                    [
                        'name' => __('Collectible Card Games'),
                        'value' => 'card_game'
                    ],
                    [
                        'name' => __('Living Card Games'),
                        'value' => 'living_card_game'
                    ],
                    [
                        'name' => __('Miniature Games'),
                        'value' => 'miniature_game'
                    ],
                    [
                        'name' => __('Sport Card'),
                        'value' => 'sport_card'
                    ],
                    [
                        'name' => __('Non Sport Card'),
                        'value' => 'non_sport_card'
                    ],
                    [
                        'name' => __('Bricks'),
                        'value' => 'bricks'
                    ],
                    [
                        'name' => __('Fashion Dolls'),
                        'value' => 'dolls'
                    ],
                    [
                        'name' => __('Diecast / Model Kits'),
                        'value' => 'diecast_modelkits'
                    ],
                    [
                        'name' => __('Trains'),
                        'value' => 'trains'
                    ]
                ];
                
                if ($categories) {
                    foreach($categories as $category) {
                        $items[] = [
                            'label' => $category['name'] ?? '',
                            'value' => $category['value'] ?? '',
                        ];
                    }
                }

                if ($items) {
                    // usort($items, fn($a, $b) => $b['label'] <=> $a['label']);
                    $this->options = $items;
                }
            } catch (\Exception $exception) {
                $this->logger->critical($exception->getMessage());
            }
        }

        return $this->options;
    }
}