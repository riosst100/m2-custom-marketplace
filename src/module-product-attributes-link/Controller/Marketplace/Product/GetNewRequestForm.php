<?php
namespace TCGCollective\ProductAttributesLink\Controller\Marketplace\Product;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\Action\Context;

class GetNewRequestForm extends Action implements HttpPostActionInterface, CsrfAwareActionInterface
{
    protected $jsonFactory;
    protected $resultPageFactory;
    protected $formFactory;
    
    public function __construct(
        Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Lof\Formbuilder\Model\FormFactory $formFactory
    ) {
        $this->jsonFactory = $jsonFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->formFactory = $formFactory;
        parent::__construct(
            $context
        );
    }

    public function execute()
    {
        $resultJson = $this->jsonFactory->create();
        $resultJson->setHttpResponseCode(200);

        $resultJson->setData([
            'success' => false,
            'result' => __('Sorry, there is no form available. Please contact the platform administrator.')
        ]);
        
        $attributeCode = $this->getRequest()->getParam('attribute_code');

        $formId = $this->getFormIdByIdentifier($attributeCode);
        if ($formId) {
            $resultPage = $this->resultPageFactory->create();
            $block = $resultPage->getLayout()->createBlock("Lof\Formbuilder\Block\Widget\Form")->setFormid($formId)->setFrameWorkCss("boostrap")->setAttributeCode($attributeCode)->setShowTextBefore(1)->setShowTextAfter(1)->toHtml();

            $resultJson->setData([
                'success' => true,
                'result' => $block
            ]);
        }

        return $resultJson;
    }

    public function getFormIdByIdentifier($attributeCode) 
    {
        $collection = $this->formFactory->create()
        ->getCollection()
        ->addFieldToFilter('identifier', $attributeCode)
        ->addFieldToFilter('status', 1);
        if ($collection->getSize()) {
            return $collection->getFirstItem()->getFormId();
        }

        return null;
    }

    // Bypass CSRF validation (wajib untuk webhook eksternal)
    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }

    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }
}