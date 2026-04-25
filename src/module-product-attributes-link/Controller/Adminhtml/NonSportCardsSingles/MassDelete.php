<?php

namespace TCGCollective\ProductAttributesLink\Controller\Adminhtml\NonSportCardsSetsCard;

use Magento\Backend\App\Action;

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

    protected $nonSportCardsSetsCard;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Registry $registry
     * @param \TCGCollective\ProductAttributesLink\Model\NonSportCardsSetsCardFactory $nonSportCardsSetsCard
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry,
        \TCGCollective\ProductAttributesLink\Model\NonSportCardsSetsCardFactory $nonSportCardsSetsCard
    ) {
        $this->nonSportCardsSetsCard = $nonSportCardsSetsCard;
        parent::__construct($context,$resultPageFactory,$registry);
    }

    public function execute()
    {
        try {
            $entityID = '';
            $setId = '';
            $selected = $this->getRequest()->getParam('selected') ?: null;
            // die(print_r($selected,true));
            $id = $this->getRequest()->getParam('id') ?: null;
            if ($id || $selected) {
                $count = 0;

                $collection = $this->nonSportCardsSetsCard->create()
                ->getCollection();
                if ($id) {
                    $collection->addFieldToFilter('entity_id', $id);
                }
                if ($selected) {
                    $collection->addFieldToFilter('entity_id', ['in' => $selected]);
                }
                if ($collection->getSize()) {
                    foreach($collection as $model) {
                        $entityID = $model->getData('nsc_categories');
                        $setId = $model->getSetId();
                        $model->delete();

                        $count++;
                    }
                }
                
                $this->messageManager->addSuccess(__('A total of %1 cards have been deleted.', $count));
            }
        } catch (\Exception $e) {   
            $this->messageManager->addError(__($e->getMessage()));
        }

        $this->_redirect('*/*/index/set_id/'.$setId);
    }
}