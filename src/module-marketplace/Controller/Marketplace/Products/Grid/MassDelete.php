<?php

namespace TCGCollective\MarketPlace\Controller\Marketplace\Products\Grid;

use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;

class MassDelete extends Action
{
    protected $jsonFactory;
    protected $productRepository;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        JsonFactory $jsonFactory,
        ProductRepositoryInterface $productRepository
    ) {
        $this->jsonFactory = $jsonFactory;
        $this->productRepository = $productRepository;
        parent::__construct($context);
    }

    public function execute()
    {
        $ids = $this->getRequest()->getParam('ids', []);

        if (!is_array($ids) || empty($ids)) {
            return $this->jsonFactory->create()->setData([
                'success' => false
            ]);
        }

        if ($this->getRequest()->getParam('select_all')) {

            $collection = $this->collectionFactory->create();

            // apply same filter as grid
            if ($category) {
                $collection->addCategoriesFilter(['in' => $category]);
            }

            foreach ($collection as $item) {
                $item->delete();
            }

        } else {

            $ids = $this->getRequest()->getParam('ids', []);
            foreach ($ids as $id) {
                $product = $this->productRepository->getById($id);
                $this->productRepository->delete($product);
            }
        }

        return $this->jsonFactory->create()->setData([
            'success' => true
        ]);
    }
}