<?php

namespace TCGCollective\MarketPlace\Controller\Adminhtml\SidebarFilters;

use Magento\Framework\Message\MessageInterface;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory;

class InlineEdit extends \TCGCollective\ProductAttributesLink\Controller\Adminhtml\Index
{
    protected $attributeCollectionFactory;


    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->registry = $registry;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct(
            $context,
            $resultPageFactory,
            $registry
        );
    }

    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();

        $postItems = $this->getRequest()->getParam('items', []);
        if (!($this->getRequest()->getParam('isAjax') && count($postItems))) {
            return $resultJson->setData(
                [
                    'messages' => [
                        __('Please correct the data sent.')
                    ],
                    'error' => true,
                ]
            );
        }

        foreach (array_keys($postItems) as $attributeId) {
            $item = $postItems[$attributeId] ?? [];
            if ($item) {
                $this->updateAttributeData($attributeId, $item);
            }
        }

        return $resultJson->setData(
            [
                'messages' => $this->getErrorMessages(),
                'error' => $this->isErrorExists()
            ]
        );
    }

    public function updateAttributeData($attributeId, $item) 
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->attributeCollectionFactory = $objectManager->get(\Magento\Eav\Model\Entity\AttributeFactory::class);
        
        if ($attributeId && $item) {
            $model = $this->attributeCollectionFactory->create()
            ->load($attributeId);
            
            foreach($item as $attrCode => $value) {
                $model->setData($attrCode, $value);
            }
            
            $model->save();
        }
    }

    /**
     * Get array with errors
     *
     * @return array
     */
    protected function getErrorMessages()
    {
        $messages = [];
        foreach ($this->getMessageManager()->getMessages()->getErrors() as $error) {
            $messages[] = $error->getText();
        }
        return $messages;
    }

    /**
     * Check if errors exists
     *
     * @return bool
     */
    protected function isErrorExists()
    {
        return (bool)$this->getMessageManager()->getMessages(true)->getCountByType(MessageInterface::TYPE_ERROR);
    }
}
