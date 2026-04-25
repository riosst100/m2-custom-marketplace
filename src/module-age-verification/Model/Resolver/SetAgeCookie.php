<?php
declare(strict_types=1);

namespace TCGCollective\AgeVerification\Model\Resolver;

use Lof\AgeVerification\Helper\Data as HelperData;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\Cookie\PhpCookieManager;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class SetAgeCookie implements ResolverInterface
{
    /**
     * @var PhpCookieManager
     */
    private $cookieManager;

    /**
     * @var CookieMetadataFactory
     */
    private $cookieMetadataFactory;

    /**
     * @var HelperData
     */
    private $helper;

    public function __construct(
        PhpCookieManager $cookieManager,
        CookieMetadataFactory $cookieMetadataFactory,
        HelperData $helper
    ) {
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->helper = $helper;
    }

    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $age = (int)($args['age'] ?? 0);
        $lifetime = isset($args['lifetime']) ? (int)$args['lifetime'] : (int)$this->helper->getCookieLifetime();

        if ($age <= 0) {
            return [
                'success' => false,
                'message' => __('Invalid age value.')
            ];
        }

        try {
            $metadata = $this->cookieMetadataFactory->createPublicCookieMetadata()
                ->setDuration($lifetime)
                ->setPath('/')
                ->setHttpOnly(false);

            $this->cookieManager->setPublicCookie('TCGCollective_AgeVerification', (string)$age, $metadata);

            return [
                'success' => true,
                'message' => __('Age cookie saved.')
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => __('Unable to set cookie.')
            ];
        }
    }
}
