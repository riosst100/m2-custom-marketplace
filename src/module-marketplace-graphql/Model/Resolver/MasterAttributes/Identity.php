<?php

namespace TCGCollective\MarketplaceGraphQl\Model\Resolver\MasterAttributes;

use Magento\Framework\GraphQl\Query\Resolver\IdentityInterface;

class Identity implements IdentityInterface
{

    private $cacheTag = \Magento\Framework\App\Config::CACHE_TAG;

    /**
     * @param array $resolvedData
     * @return string[]
     */
    public function getIdentities(array $resolvedData): array
    {
        $ids =  [$this->cacheTag, sprintf('%s_%s', $this->cacheTag, 'master_attributes')];

        return $ids;
    }
}

