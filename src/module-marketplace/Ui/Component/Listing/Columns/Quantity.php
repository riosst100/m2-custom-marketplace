<?php

namespace TCGCollective\MarketPlace\Ui\Component\Listing\Columns;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class Quantity extends \Lof\MarketPlace\Ui\Component\Listing\Columns\Quantity
{
    /**
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->productRepository = $objectManager->create(\Magento\Catalog\Model\ProductRepository::class);
        $this->configurableType = $objectManager->create(\Magento\ConfigurableProduct\Model\Product\Type\Configurable::class);
        $this->attributeRepository = $objectManager->create(\Magento\Catalog\Api\ProductAttributeRepositoryInterface::class);
        $this->stockRegistry = $objectManager->create(\Magento\CatalogInventory\Api\StockRegistryInterface::class);

        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');

            foreach ($dataSource['data']['items'] as &$item) {

                if (isset($item['entity_id'])) {
                    $typeId = $item['type_id'];

                    $productId = $item['entity_id'];

                    $product = $this->productRepository->getById($productId, false, 0);
                    if ($typeId == "configurable") {
                        $result = [];

                        $attributes = $this->configurableType->getConfigurableAttributes($product);
                        $usedProducts = $this->configurableType->getUsedProducts($product);
                        if ($usedProducts) {
                            foreach ($usedProducts as $child) {
                                foreach ($attributes as $attribute) {
                                    $attrCode = $attribute->getProductAttribute()->getAttributeCode();
                                    $attrValue = $child->getData($attrCode);

                                    if (!$attrValue) {
                                        continue;
                                    }

                                    // Ambil label dari option value
                                    $attr = $this->attributeRepository->get($attrCode);
                                    $options = $attr->getOptions();

                                    $label = null;
                                    foreach ($options as $option) {
                                        if ($option->getValue() == $attrValue) {
                                            $label = $option->getLabel();
                                            break;
                                        }
                                    }

                                    // Ambil qty dari stock
                                    $stockItem = $this->stockRegistry->getStockItem($child->getId());
                                    $qty = (int) $stockItem->getQty();

                                    $result[] = $label . ':<br/><b>' . $qty . '</b>';
                                }
                            }
                        }

                        // dd($result);

                        $item[$fieldName] = $result ? implode("<br><br>", $result) : '-';
                    } else {
                        // $product = $this->_productloader->create()->load($item['entity_id']);
                        if ($product) {
                            $item[$fieldName] = $product->getData('quantity_and_stock_status')['qty'];
                        }
                    }

                }
            }
        }

        return $dataSource;
    }
}
