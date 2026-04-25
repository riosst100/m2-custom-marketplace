<?php

namespace TCGCollective\MarketPlace\Ui\Component\Form\SidebarFilters;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Options tree for "Categories" field
 */
class AttributeCodes implements OptionSourceInterface
{
    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->attributeCollectionFactory = $objectManager->get(\Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory::class);

        $options = [];

        $collection = $this->attributeCollectionFactory->create();

        $collection->getSelect()->join(
            ['cea' => 'catalog_eav_attribute'],
            'cea.attribute_id = main_table.attribute_id',
            []
        )
        ->where('main_table.entity_type_id = 4 AND cea.is_filterable = 0 AND main_table.frontend_input="select"')
        ->order('main_table.attribute_code ASC')
        ->group('main_table.attribute_id');

        if ($collection->getSize()) {
            foreach($collection as $attribute) {
                $options[] = [
                    'label' => __($attribute->getAttributeCode()),
                    'value' => $attribute->getAttributeId()
                ];
            }
        }

        return $options;
    }
}
