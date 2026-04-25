<?php

namespace TCGCollective\MarketPlace\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class ShipFrom extends \Magento\Framework\DataObject implements OptionSourceInterface
{
    protected $countryCollectionFactory;

    /**
     * Must be NULL so Magento can populate options correctly
     * @var array|null
     */
    protected $_options = null;
    
    public function __construct(
        \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory,
        array $data = []
    ) {
        $this->countryCollectionFactory = $countryCollectionFactory;
        parent::__construct($data);
    }

    public function getAllOptions()
    {
        if ($this->_options === null) {

            $this->_options = [];

            $collection = $this->countryCollectionFactory->create();
            $collection->addFieldToSelect(['country_id', 'iso2_code', 'iso3_code']);

            $countries = $collection->toOptionArray();

            usort($countries, function($a, $b) {
                return strcmp($a['value'], $b['value']);
            });

            $i = 1;
            foreach ($countries as $item) {
                if (!empty($item['label'])) {

                    $this->_options[] = [
                        'value' => $i,              // int ID (safe for backend_type=int)
                        'label' => $item['label'],  // country name
                        'code'  => $item['value'],  // ISO code like ZA, ID, US
                    ];

                    $i++;
                }
            }
        }

        // dd($this->_options);

        return $this->_options;
    }

    public function getOptionIdByCode($code)
    {
        // die('<pre>'.print_r($this->getAllOptions(),true));
        foreach ($this->getAllOptions() as $opt) {
            if ($opt['code'] === $code) {
                // dd($opt);
                return $opt['value']; // return integer id
            }
        }
        return null;
    }

    public function toOptionArray()
    {
        return $this->getAllOptions();
    }
}
