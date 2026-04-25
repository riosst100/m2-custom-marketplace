<?php
declare(strict_types=1);

namespace TCGCollective\MarketplaceGraphQl\Model\Resolver;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Psr\Log\LoggerInterface;

class MasterAttributes implements ResolverInterface
{
    protected $catalogHelper;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @param /\oreMarketplace\MarketPlace\Helper\Catalog $catalogHelper
     * @param LoggerInterface|null $logger
     */
    public function __construct(
        \TCGCollective\MarketPlace\Helper\Catalog $catalogHelper,
        LoggerInterface $logger = null
    ) {
        $this->catalogHelper = $catalogHelper;
        $this->logger = $logger ?? ObjectManager::getInstance()->get(LoggerInterface::class);
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        return $this->catalogHelper->getMasterAttributes();
    }
}
