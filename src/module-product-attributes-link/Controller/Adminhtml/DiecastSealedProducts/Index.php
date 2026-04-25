<?php

namespace TCGCollective\ProductAttributesLink\Controller\Adminhtml\CardGamesSealedProducts;

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
        $sets = null;
        $drop = null;

        $dropId = $this->getRequest()->getParam('card_drop');
        if ($dropId) {
            $drop = $this->helper->getCardGameDropById($dropId);
        }

        $setId = $this->getRequest()->getParam('card_release');
        if ($setId) {
            $sets = $this->helper->getCardGameRelease($setId);
            $attrId = $sets->getCardGame();
        } else {
            $attrId = $this->getRequest()->getParam('card_game');
        }

        $title = 'Sealed Products Database | ' . $this->helper->getOptionLabelById('card_game', $attrId);
        
        if ($sets) {
            $title .= ' | ' . $sets->getData('card_release');
        }

        if ($drop) {
            $title .= ' | ' . $drop->getData('card_drop');
        }

        // $title .= ' | Sealed Products';
        
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend($title);
        return $resultPage;
    }
}
