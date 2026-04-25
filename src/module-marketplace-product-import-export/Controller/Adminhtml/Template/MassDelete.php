<?php

namespace TCGCollective\MarketplaceProductImportExport\Controller\Adminhtml\Template;

use Magento\Backend\App\Action;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Ui\Component\MassAction\Filter;
use TCGCollective\MarketplaceProductImportExport\Model\ResourceModel\ImportExportTemplate\CollectionFactory;

class MassDelete extends Action
{
    // public const ADMIN_RESOURCE = 'Lof_Formbuilder::blacklist_delete';
    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * MassDelete constructor.
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(Context $context, Filter $filter, CollectionFactory $collectionFactory)
    {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context);
    }

    /**
     * Execute action
     *
     * @inheritdoc
     */
    public function execute()
    {
        if ($id = $this->getRequest()->getParam('id')) {
            $collection = $this->collectionFactory->create()
            ->addFieldToFilter('entity_id', ['eq' => $id]);
        } else {
            $collection = $this->filter->getCollection($this->collectionFactory->create());
        }
        
        $collectionSize = $collection->getSize();
        foreach ($collection as $model) {
            $model->delete();
        }

        $this->messageManager->addSuccessMessage(__('A total of %1 record(s) have been deleted.', $collectionSize));

        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/');
    }
}
