<?php

namespace TCGCollective\ProductAttributesLink\Ui\DataProvider\Form;
    
class PlatformDatabaseFormDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    protected $platformDbHelper;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        \TCGCollective\PlatformDatabase\Helper\Data $platformDbHelper,
        array $meta = [],
        array $data = []
    ) {
        $this->platformDbHelper = $platformDbHelper;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }
    
    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        return $this->platformDbHelper->getPlatformDatabaseFormDataProvider();
    }

    public function addFilter(\Magento\Framework\Api\Filter $filter)
    {
        // @phpstan-ignore-next-line as adding return statement cause of backward compatibility issue
        // $this->getCollection()->addFieldToFilter(
        //     $filter->getField(),
        //     [$filter->getConditionType() => $filter->getValue()]
        // );
        return null;
    }
}