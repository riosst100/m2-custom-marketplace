<?php

namespace TCGCollective\MarketplaceProductImportExport\Controller\Marketplace\Product;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;

class DeleteImage extends Action
{
    protected $modelFactory;

    public function __construct(
        Context $context,
        \TCGCollective\MarketplaceProductImportExport\Model\SellerImagesFactory $modelFactory
    ) {
        $this->modelFactory = $modelFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $resultJson = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON);
        try {
            $selected = (array) $this->getRequest()->getParam('selected');
            $count = 0;
            if ($selected) {
                $collection = $this->modelFactory->create()->getCollection()
                    ->addFieldToFilter('entity_id', ['in' => $selected]);
                foreach ($collection as $model) {
                    $model->delete();
                    $count++;
                }
            }
            return $resultJson->setData(['success' => true, 'deleted' => $count]);
        } catch (\Exception $e) {
            return $resultJson->setData(['success' => false, 'error' => $e->getMessage()]);
        }
    }
}