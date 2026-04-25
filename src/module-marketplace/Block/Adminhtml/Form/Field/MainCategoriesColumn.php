<?php
namespace TCGCollective\MarketPlace\Block\Adminhtml\Form\Field;

use Magento\Framework\View\Element\Context;
use Magento\Framework\View\Element\Html\Select;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;

class MainCategoriesColumn extends Select
{
    /**
     * @var CategoryCollectionFactory
     */
    private $categoryCollectionFactory;

    public function __construct(
        Context $context,
        CategoryCollectionFactory $categoryCollectionFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->categoryCollectionFactory = $categoryCollectionFactory;
    }

    public function setInputName($value)
    {
        return $this->setName($value);
    }

    public function _toHtml(): string
    {
        if (!$this->getOptions()) {
            $this->setOptions($this->getCategories());
        }
        return parent::_toHtml();
    }

    public function getCategories()
    {
        $options = [];

        // Get category collection (exclude root category)
        $collection = $this->categoryCollectionFactory->create()
            ->addAttributeToSelect('name')
            ->addAttributeToFilter('level', ['eq' => 3]) // skip "Default Category"
            ->addIsActiveFilter()
            ->setOrder('path', 'ASC');

        foreach ($collection as $category) {
            $options[] = [
                'label' => $category->getParentCategory()->getName() . ' | ' . $category->getName(),
                'value' => $category->getId()
            ];
        }

        return $options;
    }
}