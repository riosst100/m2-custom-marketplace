<?php

namespace TCGCollective\ProductAttributesLink\Block\Adminhtml\Product\Attribute\Set;

class Main extends \Magento\Catalog\Block\Adminhtml\Product\Attribute\Set\Main
{
    /**
     * Retrieve Unused in Attribute Set Attribute Tree as JSON
     *
     * @return string
     */
    public function getAttributeTreeJson()
    {
        $items = [];
        $setId = $this->_getSetId();

        $collection = $this->_collectionFactory->create()->setAttributeSetFilter($setId)->load();

        $attributesIds = ['0'];
        /* @var $item \Magento\Eav\Model\Entity\Attribute */
        foreach ($collection->getItems() as $item) {
            $attributesIds[] = $item->getAttributeId();
        }

        $attributes = $this->_collectionFactory->create()->setAttributesExcludeFilter(
            $attributesIds
        )->addVisibleFilter()->load();

        foreach ($attributes as $child) {
            $attr = [
                'text' => $this->escapeHtml($child->getAttributeCode()),
                'id' => $child->getAttributeId(),
                'cls' => 'leaf',
                'allowDrop' => false,
                'allowDrag' => true,
                'leaf' => true,
                'is_user_defined' => $child->getIsUserDefined(),
                'entity_id' => $child->getEntityId(),
            ];

            $items[] = $attr;
        }

        if (count($items) == 0) {
            $items[] = [
                'text' => __('Empty'),
                'id' => 'empty',
                'cls' => 'folder',
                'allowDrop' => false,
                'allowDrag' => false,
            ];
        }

        usort($items, function ($a, $b) {
            return $a['text'] <=> $b['text'];
        });

        return $this->_jsonEncoder->encode($items);
    }
}
