<?php
namespace TCGCollective\MarketplaceGraphQl\Plugin;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class AddPwaUrlToEmailVariables
{
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Inject pwa_base_url into template variables globally
     */
    public function beforeSetTemplateVars(
        \Magento\Framework\Mail\Template\TransportBuilder $subject,
        array $templateVars
    ) {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/signin.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);        

        if (empty($templateVars['pwa_base_url'])) {
            $storeId = $templateVars['store'] ?? null;
            $pwaBaseUrl = $this->scopeConfig->getValue(
                'pwa/pwa_settings/pwa_base_url',
                ScopeInterface::SCOPE_STORE,
                $storeId
            );

            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->storeManager = $objectManager->create(\Magento\Store\Model\StoreManagerInterface::class);

            $store = $this->storeManager->getStore($storeId);

            $websiteCode = $store->getWebsite()->getCode();

            if ($websiteCode && $websiteCode != "base") {
                $templateVars['pwa_base_url'] = $pwaBaseUrl . '/' . $websiteCode;
            } else {
                $templateVars['pwa_base_url'] = $pwaBaseUrl;
            }
        }

        return [$templateVars];
    }
}
