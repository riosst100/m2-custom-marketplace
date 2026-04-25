<?php
declare(strict_types=1);

namespace TCGCollective\AgeVerification\Model\Resolver;

use Lof\AgeVerification\Helper\Data as HelperData;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class Config implements ResolverInterface
{
    /**
     * @var HelperData
     */
    private $helper;

    public function __construct(
        HelperData $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * @inheritDoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $storeId = $args['storeId'] ?? null;

        try {
            return [
                'enabled' => $this->helper->isEnabled($storeId),
                'verifyType' => $this->helper->getVerifyType($storeId),
                'verifyAge' => $this->helper->getVerifyAge(null, $storeId),
                'popupTitle' => $this->helper->getPopupTitle(null, $storeId),
                'popupDescription' => $this->helper->getPopupDescription(null, $storeId),
                'buttonCancelText' => $this->helper->getButtonCancelText($storeId),
                'buttonConfirmText' => $this->helper->getButtonConfirmText($storeId),
                'buttonRedirectUrl' => $this->helper->getButtonRedirectUrl($storeId),
                'popupIcon' => $this->helper->getPopupIcon($storeId),
                'textColor' => $this->helper->getTextColor($storeId),
                'backgroundColor' => $this->helper->getBackgroundColor($storeId),
                'overlayColor' => $this->helper->getOverlayColor($storeId),
                'buttonCancelTextColor' => $this->helper->getButtonCancelTextColor($storeId),
                'buttonCancelBackgroundColor' => $this->helper->getButtonCancelBackgroundColor($storeId),
                'buttonConfirmTextColor' => $this->helper->getButtonConfirmTextColor($storeId),
                'buttonConfirmBackgroundColor' => $this->helper->getButtonConfirmBackgroundColor($storeId),
                'cookieLifetime' => (int)$this->helper->getCookieLifetime($storeId),
                'isRequiredLogin' => $this->helper->isRequiredLogin($storeId),
                'appliedCategoryIds' => $this->helper->getAppliedCategoryIds($storeId),
                'cmsPageIdentifiers' => $this->helper->getCmsPageIdentifiers($storeId),
                'appliedStoreIds' => $this->helper->getAppliedStoreIds($storeId),
                'addToCartSelector' => $this->helper->getAddToCartSelector($storeId),
                'productItemSelector' => $this->helper->getProductItemSelector($storeId),
                'purchaseNotice' => $this->helper->getPurchaseNotice($storeId),
                'purchaseMessage' => $this->helper->getPurchaseMessage($storeId),
                'loginNotice' => $this->helper->getLoginNotice($storeId),
                'preventNotice' => $this->helper->getPreventNotice($storeId),
                'mediaBaseUrl' => $this->helper->getMediaBaseUrl()
            ];
        } catch (\Exception $e) {
            // Return minimal response if helper fails
            return [
                'enabled' => false
            ];
        }
    }
}
