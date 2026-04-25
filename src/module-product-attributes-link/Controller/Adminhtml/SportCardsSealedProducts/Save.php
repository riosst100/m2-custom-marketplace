<?php

namespace TCGCollective\ProductAttributesLink\Controller\Adminhtml\SportCardsSealedProducts;

class Save extends \TCGCollective\ProductAttributesLink\Controller\Adminhtml\Index
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

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Registry $registry
     * @param \TCGCollective\ProductAttributesLink\Helper\Data $helper
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry,
        \TCGCollective\ProductAttributesLink\Helper\Data $helper
    ) {
        $this->helper = $helper;
        parent::__construct($context,$resultPageFactory,$registry);
    }

    public function execute()
    {
        try {
            $data = $this->getRequest()->getParams();
            $exist = false;

            $id = isset($data['entity_id']) ? $data['entity_id'] : null;
            $model = $this->helper->getCardGameSealedProductsById($id, true);
            if ($model && $model->getId()) {
                $data['entity_id'] = $model->getId();

                $exist = true;
            }

            $model->setData($data);
            $model->save();
            
            if ($exist) {
                $this->messageManager->addSuccess(__('Data updated successfully'));
            } else {
                $this->messageManager->addSuccess(__('Data saved successfully'));
            }
        } catch (\Exception $e) {   
            $this->messageManager->addError(__($e->getMessage()));
        }

        $this->_redirect('*/*/index/set_id/'.$model->getData('set_id'));
    }
}