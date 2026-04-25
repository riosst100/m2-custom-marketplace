<?php

namespace TCGCollective\MarketPlace\Model\Source;

use TCGCollective\ProductAttributesLink\Helper\Data;
use Psr\Log\LoggerInterface as Logger;
use Magento\Framework\Data\OptionSourceInterface;

class CategoriesGames implements OptionSourceInterface
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
                $category = $this->helper->getCategoryByUrlKey('games');
                if ($category) {
                    $children = $category->getChildrenCategories();
                    if ($children) {
                        foreach($children as $childCategory) {
                            $items[] = [
                                'label' => $childCategory->getName(),
                                'value' => (int)$childCategory->getId(),
                            ];
                        }
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