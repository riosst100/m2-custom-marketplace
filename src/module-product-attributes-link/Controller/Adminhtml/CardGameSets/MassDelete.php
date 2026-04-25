<?php

namespace TCGCollective\ProductAttributesLink\Controller\Adminhtml\CardGamesSets;

use Magento\Backend\App\Action;
use Magento\Ui\Component\MassAction\Filter;
use TCGCollective\ProductAttributesLink\Model\ResourceModel\CardGamesSets\CollectionFactory;

class MassDelete extends \TCGCollective\ProductAttributesLink\Controller\Adminhtml\Index
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \TCGCollective\ProductAttributesLink\Helper\Data
     */
    protected $helper;

    protected $cardGameSets;

    protected $collectionFactory;

    protected $filter;

    protected $cardGameSetsCard;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Registry $registry
     * @param \TCGCollective\ProductAttributesLink\Model\CardGamesSetsFactory $cardGameSets
     * @param \TCGCollective\ProductAttributesLink\Model\CardGamesSetsCardFactory $cardGameSetsCard
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry,
        \TCGCollective\ProductAttributesLink\Model\CardGamesSetsFactory $cardGameSets,
        \TCGCollective\ProductAttributesLink\Model\CardGamesSetsCardFactory $cardGameSetsCard,
        CollectionFactory $collectionFactory,
        Filter $filter
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->cardGameSets = $cardGameSets;
        $this->cardGameSetsCard = $cardGameSetsCard;
        parent::__construct($context,$resultPageFactory,$registry);
    }

    public function execute()
    {
        $refererUrl = $this->_redirect->getRefererUrl();

        // Define variable
        $collection = $this->cardGameSets->create()->getCollection();
        $setsListingUrl = '*/cardgamessets/index';
        $entityCode = 'card_game';
        $entity = null;
        $count = 0;

        if ($refererUrl) {
            if (preg_match('/'.$entityCode.'\/(\d+)/', $refererUrl, $matches)) {
                $entity = $matches[1];
            }
        }

        $id = $this->getRequest()->getParam('id') ?: null;
        $selectedIds = $this->getRequest()->getParam('selected') ?: null;
        $excluded = $this->getRequest()->getParam('excluded') ?: null;
        
        if ($id || $selectedIds || !$selectedIds && $excluded == "false") {
            try {
                if ($id) {
                    $collection->addFieldToFilter('entity_id', $id);
                } elseif ($selectedIds) {
                    $collection->addFieldToFilter('entity_id', ['in' => $selectedIds]);
                } elseif ($excluded == "false" && $entity) {
                    $collection->addFieldToFilter($entityCode, $entity);
                } else {
                    $this->messageManager->addError(__('Please select data to delete.'));
                    return $this->_redirect($refererUrl);
                }

                if ($collection->getSize()) {
                    foreach($collection as $model) {
                        $entity = $model->getData($entityCode);
                        $setId = $model->getId();

                        $model->delete();

                        $singlesDeleteCount = 0;

                        // Delete card
                        $collection = $this->cardGameSetsCard->create()->getCollection();
                        $collection->addFieldToFilter('set_id', $setId);
                        if ($collection->getSize()) {
                            foreach($collection as $model) {
                                $model->delete();

                                $singlesDeleteCount++;
                            }
                        }
                        
                        if ($singlesDeleteCount > 0) {
                            $this->messageManager->addSuccess(__('A total of %1 singles have been deleted.', $singlesDeleteCount));
                        }

                        $count++;
                    }
                }
                
                $this->messageManager->addSuccess(__('A total of %1 sets have been deleted.', $count));
            } catch (\Exception $e) {
                $this->messageManager->addError(__($e->getMessage()));
            }

            if ($entity) {
                return $this->_redirect($setsListingUrl.'/'.$entityCode.'/'.$entity);
            }
        } else {
            $this->messageManager->addError(__('Please select data to delete.'));
        }

        return $this->_redirect($refererUrl);
    }
}