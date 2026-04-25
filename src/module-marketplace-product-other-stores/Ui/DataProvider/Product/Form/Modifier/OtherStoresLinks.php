<?php

namespace TCGCollective\MarketplaceProductOtherStores\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Catalog\Controller\Adminhtml\Product\Initialization\StockDataFilter;
use Magento\Catalog\Model\Locator\LocatorInterface;
 
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
 
use Magento\Ui\Component\Container;
use Magento\Ui\Component\Form\Element\DataType\Number;
use Magento\Ui\Component\Form\Element\DataType\Text;
use Magento\Ui\Component\Form\Element\Textarea;
use Magento\Ui\Component\Form\Element\Input;
use Magento\Ui\Component\Form\Field;
use Magento\Ui\Component\Modal;

class OtherStoresLinks extends AbstractModifier
{
    const LINK_TO_OTHER_STORES = 'link_to_other_stores';
 
    /**
     * @var LocatorInterface
     */
    private $locator;
 
    /**
     * @var ArrayManager
     */
    private $arrayManager;
 
    /**
     * @var array
     */
    private $meta = [];
 
    /**
     * @var string
     */
    protected $scopeName;   
 
    /**
     * @param LocatorInterface $locator
     * @param ArrayManager $arrayManager
     */
    public function __construct(
        LocatorInterface $locator,
        ArrayManager $arrayManager,
        $scopeName = ''
    ) {
        $this->locator = $locator;
        $this->arrayManager = $arrayManager;
        $this->scopeName = $scopeName;
    }
 
    /**
     * {@inheritdoc}
     */
    public function modifyData(array $data)
    {
        $fieldCode = self::LINK_TO_OTHER_STORES;
 
        $model = $this->locator->getProduct();
        $modelId = $model->getId();

        $defaultRecord = [
            [
                "record_id" => "0",
                "stores" => "Shopee",
                "product_link" => ""
            ],
            [
                "record_id" => "1",
                "stores" => "Lazada",
                "product_link" => ""
            ]
        ];

        $highlightsData = [];
 
        $rawHighlightsData = $model->getData('link_to_other_stores');
        if ($rawHighlightsData) {
            $rawHighlightsData = json_decode($rawHighlightsData, true);
            if ($rawHighlightsData) {
                $existingStores = array_column($rawHighlightsData, 'stores');

                foreach ($defaultRecord as $defaultStore) {
                    if (!in_array($defaultStore['stores'], $existingStores)) {
                        // If the store does not exist, add it to highlightsData
                        $rawHighlightsData[] = $defaultStore;
                    }
                }

                // $newHighlightsData = [];

                // $index = 0;
                // foreach ($rawHighlightsData as $data) {
                    // $data['record_id'] = $index++; // Assign record_id based on the index
                    // $newHighlightsData[] = $data;
                //     // $data['initialize'] = false;
                // }

                $highlightsData = $rawHighlightsData;
            }

        //     
        }

        if (!$highlightsData) {
            $highlightsData = $defaultRecord;
        }
        
        // dd($highlightsData);
        
        $path = $modelId . '/' . self::DATA_SOURCE_DEFAULT . '/'. self::LINK_TO_OTHER_STORES;
        $data = $this->arrayManager->set($path, $data, $highlightsData);

        return $data;
    }
 
    /**
     * {@inheritdoc}
     */
    public function modifyMeta(array $meta)
    {
        $this->meta = $meta;
        $this->initAttractionHighlightFields();
        return $this->meta;
    }
 
    /**
     * Customize attraction highlights field
     *
     * @return $this
     */
    protected function initAttractionHighlightFields()
    {
        $highlightsPath = $this->arrayManager->findPath(
            self::LINK_TO_OTHER_STORES,
            $this->meta,
            null,
            'children'
        );
         
        if ($highlightsPath) {
            $this->meta = $this->arrayManager->merge(
                $highlightsPath,
                $this->meta,
                $this->initHighlightFieldStructure($highlightsPath)
            );
            $this->meta = $this->arrayManager->set(
                $this->arrayManager->slicePath($highlightsPath, 0, -3)
                . '/' . self::LINK_TO_OTHER_STORES,
                $this->meta,
                $this->arrayManager->get($highlightsPath, $this->meta)
            );
            $this->meta = $this->arrayManager->remove(
                $this->arrayManager->slicePath($highlightsPath, 0, -2),
                $this->meta
            );
        }

        // dd($this->meta);
 
        return $this;
    }   
 
 
    /**
     * Get attraction highlights dynamic rows structure
     *
     * @param string $highlightsPath
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function initHighlightFieldStructure($highlightsPath)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => 'dynamicRows',
                        'label' => __('Link to Other Stores'),
                        'renderDefaultRecord' => true,
                        'deleteProperty' => false,
                        'addButton' => false,
                        'recordTemplate' => 'record',
                        'required' => false,
                        'dataScope' => '',
                        'dndConfig' => [
                            'enabled' => false,
                        ],
                        'disabled' => false,
                        'sortOrder' =>
                            $this->arrayManager->get($highlightsPath . '/arguments/data/config/sortOrder', $this->meta),
                    ],
                ],
            ],
            'children' => [
                'record' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'componentType' => Container::NAME,
                                'isTemplate' => true,
                                'is_collection' => true,
                                'component' => 'Magento_Ui/js/dynamic-rows/record',
                                'dataScope' => '',
                            ]
                        ],
                    ],
                    'children' => [
                        'stores' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'componentType' => 'text',
                                        'component' => 'Magento_Ui/js/form/element/abstract',
                                        'template' => 'TCGCollective_MarketplaceProductOtherStores/components/cell-stores',
                                        'label' => __('Stores'),
                                        'dataScope' => 'stores',
                                    ],
                                ],
                            ],
                        ],
                        'product_link' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'formElement' => Input::NAME,
                                        'componentType' => Field::NAME,
                                        'dataType' => Text::NAME,
                                        'label' => __('Product Link'),
                                        'dataScope' => 'product_link',
                                        'require' => 0,
                                        'validation'   => [
                                            'validate-store-url' => true
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }   
}