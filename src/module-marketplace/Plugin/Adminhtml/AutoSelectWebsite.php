<?php

namespace TCGCollective\MarketPlace\Plugin\Adminhtml;

use Magento\Backend\App\AbstractAction;
use Magento\Framework\App\RequestInterface;
use TCGCollective\MarketPlace\Helper\AdminWebsite;

class AutoSelectWebsite
{
    public function __construct(
        private AdminWebsite $adminWebsite
    ) {}

    public function beforeExecute(AbstractAction $subject)
    {
        // Super admin → do nothing
        if ($this->adminWebsite->isAllWebsitesAllowed()) {
            return;
        }

        /** @var RequestInterface $request */
        $request = $subject->getRequest();

        // If scope already selected → do nothing
        if (
            $request->getParam('website') !== null ||
            $request->getParam('store') !== null
        ) {
            return;
        }

        $allowedWebsites = $this->adminWebsite->getAllowedWebsiteIds();
        if (empty($allowedWebsites)) {
            return;
        }

        // Auto-select first allowed website
        $request->setParam('website', (int) reset($allowedWebsites));
    }
}
