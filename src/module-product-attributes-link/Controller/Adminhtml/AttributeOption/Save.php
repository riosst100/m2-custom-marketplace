<?php

namespace TCGCollective\ProductAttributesLink\Controller\Adminhtml\AttributeOption;

use Magento\Backend\App\Action;

class Save extends \TCGCollective\ProductAttributesLink\Controller\Adminhtml\Index
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \TCGCollective\ProductAttributesLink\Model\ProductAttributesLinkFactory
     */
    protected $productAttributesLinkFactory;

    /**
     * @var \TCGCollective\ProductAttributesLink\Helper\Data
     */
    protected $mappingHelper;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry,
        \TCGCollective\ProductAttributesLink\Model\ProductAttributesLinkFactory $productAttributesLinkFactory,
        \TCGCollective\ProductAttributesLink\Helper\Data $mappingHelper
    ) {
        parent::__construct($context, $resultPageFactory, $registry);
        $this->productAttributesLinkFactory = $productAttributesLinkFactory;
        $this->mappingHelper = $mappingHelper;
    }

    public function execute()
    {
        try {
            $data = $this->getRequest()->getParams();

            // die(print_r($data,true));

            foreach($data as $index => $val) {
                if (is_array($val)) {
                    $val = array_filter($val);
                    $data[$index] = count($val) > 0 ? implode(",", $val) : $val;
                }
            }

            $id = isset($data['entity_id']) ? $data['entity_id'] : null;
            if ($id) {
                $collection = $this->productAttributesLinkFactory->create()
                ->getCollection()
                ->addFieldToFilter('entity_id', $id);
                if ($collection->getSize()) {
                    $model = $collection->getFirstItem();

                    $data['entity_id'] = $model->getId();
                }

                // $data['group_id'] = $this->mappingHelper->getGroupIdByName($data['group_id']);

                $model->setData($data);
                $model->save();
                
                $this->messageManager->addSuccess(__('Mapping data updated successfully'));
            }
        } catch (\Exception $e) {
            $this->messageManager->addError(__($e->getMessage()));
        }

        $entityType = $data['entity_type'] == "category" ? "categories" : "attributes";

        $this->_redirect('*/*/edit/entity_id/'.$id.'/entity_type/'.$entityType);
    }
}