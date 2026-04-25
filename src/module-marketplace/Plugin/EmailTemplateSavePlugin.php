<?php

namespace TCGCollective\MarketPlace\Plugin;

use Magento\Email\Controller\Adminhtml\Email\Template\Save;
use Magento\Email\Model\TemplateFactory;
use Magento\Email\Model\ResourceModel\Template as TemplateResource;

class EmailTemplateSavePlugin
{
    private TemplateFactory $templateFactory;
    private TemplateResource $templateResource;

    public function __construct(
        TemplateFactory $templateFactory,
        TemplateResource $templateResource
    ) {
        $this->templateFactory = $templateFactory;
        $this->templateResource = $templateResource;
    }

    public function afterExecute(Save $subject, $result)
    {
        $request = $subject->getRequest();
        $emailReceiver = $request->getParam('email_receiver');
        
        if ($emailReceiver === null) {
            return $result;
        }

        $templateId = (int)$request->getParam('id');
        
        if (!$templateId) {
            return $result;
        }

        $template = $this->templateFactory->create();
        $this->templateResource->load($template, $templateId);
        
        if ($template->getId()) {
            $template->setData('email_receiver', $emailReceiver);
            $this->templateResource->save($template);
        }

        return $result;
    }
}
