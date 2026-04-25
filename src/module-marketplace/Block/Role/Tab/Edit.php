<?php

namespace TCGCollective\MarketPlace\Block\Role\Tab;

class Edit extends \Magento\User\Block\Role\Tab\Edit
{
    /**
     * Get Json Representation of Resource Tree
     *
     * @return array
     */
    public function getTree()
    {
        $aclResources = $this->getAclResources();
        $denyIds = [
            'Magento_Analytics::analytics',
            'Magento_Backend::lof_all_elements',
            'Magento_Backend::faq_elements',
            'Lof_MarketPlace::marketplace',
            'Magento_Backend::preorder',
            'Magento_Backend::sellermembership_elements',
            'Magento_Backend::storelocator_elements',
            'Magento_Backend::form_elements',
            'Magento_Backend::mppreorder',
            'Lof_Extensions::landofcoderextensions',
            'Magento_Backend::smtpemail_elements',
            'Lof_SocialLogin::social_elements',
            'Magento_Backend::couponcode_elements',
            'Magento_Backend::mpfaq_elements',
            'Lofmp_SellerIdentificationApproval::Attachment',
            'Lofmp_SplitCart::quote',
            'Magento_Cart::cart',
            'Magento_Backend::myaccount',
            'Lofmp_CancelOrder::cancelorders',
            'Lofmp_Rma::rma',
            'Magento_Backend::global_search',
            'Magento_Newsletter::template',
            'Magento_Newsletter::queue',
            'Magento_Newsletter::subscriber',
            'Magento_Paypal::paypal_settlement_reports',
            'Magento_Analytics::business_intelligence',
            'Magento_AdobeIms::adobe_ims',
            'Magento_AdobeStockAsset::adobe_stock_asset',
            'Magento_Backend::convert',
            'Magento_Paypal::billing_agreement',
            'Magento_AdobeStockImageAdminUi::media_gallery',
            'Magento_Newsletter::problem',
            'Lof_All::config',
            'Lof_Faq::config_faq',
            'Lof_MarketPlace::config_seller',
            'Lof_Gdpr::configuration',
            'Lofmp_Quickrfq::lofmp_quickrfq',
            'Lof_PreOrder::config_preorder',
            'Lofmp_SellerMembership::config_pricecomparison',
            'Lofmp_StoreLocator::config_storelocator',
            'Lof_AgeVerification::config',
            'Lof_Formbuilder::config_form',
            'Lof_MarketPermissions::config',
            'Lofmp_PreOrder::config_preorder',
            'Lof_Quickrfq::config_quickrfq',
            'Lof_SmtpEmail::config_smtpemail',
            'Lof_SocialLogin::sociallogin_config',
            'Lofmp_CancelOrder::config',
            'Lofmp_CouponCode::config_couponcode',
            'Lofmp_Faq::config_faq',
            'Lofmp_LayeredNavigation::config',
            // 'Magento_Shipping::carriers',
            // 'Magento_Shipping::config_shipping',
            // 'Magento_Multishipping::config_multishipping',
            '',
            '',
            '',
            '',
        ];

        $filteredAclResources = $this->filterAclTree($aclResources, $denyIds);
        return $this->_integrationData->mapResources($filteredAclResources, $this->getSelectedResources());
    }

    public function filterAclTree(array $items, array $denyIds)
    {
        $result = [];

        foreach ($items as $item) {

            // skip jika id ada di blacklist
            if (in_array($item['id'], $denyIds, true)) {
                continue;
            }

            // filter children secara recursive
            if (!empty($item['children'])) {
                $item['children'] = $this->filterAclTree($item['children'], $denyIds);
            }

            $result[] = $item;
        }

        return $result;
    }

    /**
     * Get lit of all ACL resources declared in the system.
     *
     * @return array
     */
    private function getAclResources()
    {
        $resources = $this->_aclResourceProvider->getAclResources();
        $configResource = array_filter(
            $resources,
            function ($node) {
                return isset($node['id'])
                    && $node['id'] == 'Magento_Backend::admin';
            }
        );
        $configResource = reset($configResource);
        return $configResource['children'] ?? [];
    }
}
