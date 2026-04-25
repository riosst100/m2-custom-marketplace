<?php

namespace TCGCollective\MarketPlace\Controller\Marketplace\Products\Grid;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;

class ExportCsv extends Action
{
    protected $fileFactory;
    protected $collectionFactory;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        FileFactory $fileFactory,
        CollectionFactory $collectionFactory
    ) {
        $this->fileFactory = $fileFactory;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $fileName = 'products_export.csv';
        $content = '';

        $collection = $this->collectionFactory->create();
        $collection->addAttributeToSelect(['name', 'price']);

        // FILTER SAME AS GRID
        $status   = $this->getRequest()->getParam('status');
        $category = $this->getRequest()->getParam('category');
        $search   = $this->getRequest()->getParam('search');

        if ($status !== null && $status !== '') {
            $collection->addAttributeToFilter('status', $status);
        }

        if ($category) {
            $collection->addCategoriesFilter(['in' => $category]);
        }

        if ($search) {
            $collection->addAttributeToFilter(
                'name',
                ['like' => '%' . $search . '%']
            );
        }

        // HEADER
        $content .= "ID,Name,Price\n";

        foreach ($collection as $item) {
            $content .= sprintf(
                "%s,%s,%s\n",
                $item->getId(),
                str_replace(',', ' ', $item->getName()),
                $item->getPrice()
            );
        }

        return $this->fileFactory->create(
            $fileName,
            $content,
            DirectoryList::VAR_DIR,
            'text/csv'
        );
    }
}