<?php

namespace TCGCollective\ProductAttributesLink\Controller\Adminhtml\ActionToySingles;

class Index extends \TCGCollective\ProductAttributesLink\Controller\Adminhtml\Index
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
        $this->resultPageFactory = $resultPageFactory;
        $this->registry = $registry;
        $this->helper = $helper;
        parent::__construct($context,$resultPageFactory,$registry);
    }
    
    public function execute()
    {
        $setId = $this->getRequest()->getParam('set_id');
        $sets = $this->helper->getSetsById('action_toys', $setId);
        $attrId = $sets->getData('toys_brand');
        $title = $this->helper->getOptionLabelById('toys_brand', $attrId).' | '.$sets->getData('set_name');
        
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend($title);
        return $resultPage;
    }
}
