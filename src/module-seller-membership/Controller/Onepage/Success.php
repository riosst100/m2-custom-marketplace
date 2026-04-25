<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace TCGCollective\SellerMembership\Controller\Onepage;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;

/**
 * Onepage checkout success controller class
 */
class Success extends \Magento\Checkout\Controller\Onepage\Success
{
    /**
     * Order success action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $session = $this->getOnepage()->getCheckout();
        if (!$this->_objectManager->get(\Magento\Checkout\Model\Session\SuccessValidator::class)->isValid()) {
            return $this->resultRedirectFactory->create()->setPath('checkout/cart');
        }

        $isMembershipProduct = false;
        $isListingCountryMembership = false;

        $order = $session->getLastRealOrder();
        if ($order) {
            foreach ($order->getAllItems() as $orderItem) {
                if ($orderItem->getProductType() == "seller_membership") {
                    $isMembershipProduct = true;

                    $product = $orderItem->getProduct();
                    if ($product && $product->getData('membership_type') == "listing_country") {
                        $isListingCountryMembership = true;
                    }
                    break;
                }
            }
        }

        if ($isMembershipProduct) {
            $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/successPage.log');
            $logger = new \Zend_Log();
            $logger->addWriter($writer);
            // $logger->info('Success ');

            $this->messageSession = $this->_objectManager->get(\Magento\Framework\Message\Session::class);
            $this->messageSession->getMessages(true);

            $this->urlBuilder = $this->_objectManager->get(\Magento\Framework\UrlInterface::class);
            $storeManager = $this->_objectManager->get(\Magento\Store\Model\StoreManagerInterface::class);
            $customerSession = $this->_objectManager->get(\Magento\Customer\Model\Session::class);
            $autoLoginHelper = $this->_objectManager->get(\TCGCollective\SellerMembership\Helper\AutoLogin::class);

            // $logger->info('Session Data: ' . json_encode($session->getData()));

            // --- Auto-login token generation ---
            $token = null;
            if ($customerSession->isLoggedIn()) {
                $customerId = $customerSession->getCustomerId();
                $token = $autoLoginHelper->generateToken($customerId);
                // $logger->info('Generated token for customer ID: ' . $customerId);
            }

            // --- Detect target store code dynamically ---
            // if ($isListingCountryMembership) {
                // $targetPath = 'sellermembership/additional/index';
            // } else {
                $targetPath = 'lofmpsellermembership/membership/index';
            // }

            $builtUrl = $this->urlBuilder->getUrl($targetPath);
            // $logger->info('Built URL: ' . $builtUrl);

            // Extract store code (if URL looks like /sg/... or /id/...)
            $parsed = parse_url($builtUrl, PHP_URL_PATH);
            $pathParts = explode('/', trim($parsed, '/'));
            $possibleStoreCode = $pathParts[0] ?? '';

            $availableStores = array_map(
                fn($s) => $s->getCode(),
                $storeManager->getStores()
            );

            if (in_array($possibleStoreCode, $availableStores)) {
                $targetStoreCode = $possibleStoreCode;
            } else {
                $targetStoreCode = $storeManager->getStore()->getCode(); // fallback current store
            }

            // $logger->info('Detected target store code: ' . $targetStoreCode);

            // Get target store base URL
            $targetStore = $storeManager->getStore($targetStoreCode);
            $baseUrl = $targetStore->getBaseUrl();

            // --- Build final redirect URL ---
            if ($token) {
                // Redirect through /autologin handler
                $redirectUrl = $baseUrl . 'marketplace/sellermembership/autologin?autologin_token=' . $token
                    . '&redirect=' . urlencode($targetPath)
                    . '&success=true';
            } else {
                // Fallback if guest
                $redirectUrl = $baseUrl . $targetPath . '?success=true';
            }

            // $logger->info('Redirecting to: ' . $redirectUrl);
            return $this->_redirect($redirectUrl);
        }

        // --- Normal Magento success flow ---
        $session->clearQuote();
        $resultPage = $this->resultPageFactory->create();

        $this->_eventManager->dispatch(
            'checkout_onepage_controller_success_action',
            [
                'order_ids' => [$session->getLastOrderId()],
                'order' => $session->getLastRealOrder()
            ]
        );

        return $resultPage;
    }
}
