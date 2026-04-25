<?php

declare(strict_types=1);

namespace TCGCollective\MarketplaceProductImportExport\Controller\Marketplace\Media;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\MediaContentApi\Model\Config;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\Forward;

/**
 * Controller serving the media gallery content
 */
class Index extends \Magento\Customer\Controller\AbstractAccount
{
    /**
     * @var Config
     */
    private $config;

    /**
     * Index constructor.
     * @param Context $context
     * @param Config $config
     */
    public function __construct(
        Context $context,
        Config $config
    ) {
        parent::__construct($context);
        $this->config = $config;
    }

    /**
     * Get the media gallery layout
     *
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        // if (!$this->config->isEnabled()) {
        //     /** @var Forward $resultForward */
        //     $resultForward = $this->resultFactory->create(ResultFactory::TYPE_FORWARD);
        //     $resultForward->forward('noroute');

        //     return $resultForward;
        // }

        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Gallery'));

        return $resultPage;
    }
}
