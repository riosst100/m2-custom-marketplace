<?php

namespace TCGCollective\MarketPlace\Plugin;

use Magento\Email\Model\ResourceModel\Template as TemplateResource;
use Magento\Email\Model\Template;
use Magento\Framework\App\RequestInterface;

class EmailTemplateResourcePlugin
{
    private RequestInterface $request;

    public function __construct(
        RequestInterface $request
    ) {
        $this->request = $request;
    }

    /**
     * After save hook – ID ALWAYS exists here
     */
    public function afterSave(
        TemplateResource $subject,
        TemplateResource $result,
        Template $template
    ) {
        $emailReceiver = $this->request->getParam('email_receiver');
        $websiteId     = $this->request->getParam('website_id');

        $data = [];

        if ($emailReceiver !== null) {
            $data['email_receiver'] = $emailReceiver;
        }

        if ($websiteId !== null) {
            $data['website_id'] = (int)$websiteId;
        }

        if (!$data) {
            return $result;
        }

        $subject->getConnection()->update(
            $subject->getMainTable(),
            $data,
            ['template_id = ?' => (int)$template->getId()]
        );

        return $result;
    }
}
