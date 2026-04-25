<?php

namespace TCGCollective\MarketplaceRajaOngkir\Model;

use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Directory\Model\CurrencyFactory;

class Carrier extends \Magento\Shipping\Model\Carrier\AbstractCarrier implements
    \Magento\Shipping\Model\Carrier\CarrierInterface
{
    /**
     * Code of the carrier.
     *
     * @var string
     */
    const CODE = 'rajaongkir';

    /**
     * Code of the carrier.
     *
     * @var string
     */
    protected $_code = self::CODE;

    /**
     * @var bool
     */
    protected $_isFixed = true;

    /**
     * @var \Magento\Shipping\Model\Rate\ResultFactory
     */
    protected $_rateResultFactory;

    /**
     * @var \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory
     */
    protected $_rateMethodFactory;

    /**
     * @var \TCGCollective\MarketplaceRajaOngkir\Helper\Data
     */
    protected $helperData;

    protected $curl;

    protected $currencyFactory;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory
     * @param \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory
     * @param \TCGCollective\MarketplaceRajaOngkir\Helper\Data $helperData
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
        \TCGCollective\MarketplaceRajaOngkir\Helper\Data $helperData,
        Curl $curl,
        CurrencyFactory $currencyFactory,
        array $data = []
    ) {
        $this->_rateResultFactory = $rateResultFactory;
        $this->_rateMethodFactory = $rateMethodFactory;
        $this->helperData = $helperData;
        $this->curl = $curl;
        $this->scopeConfig = $scopeConfig;
        $this->currencyFactory = $currencyFactory;
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
    }

    /**
     * @param RateRequest $request
     * @return \Magento\Shipping\Model\Rate\ResultFactory
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @phpcs:disable Generic.Metrics.NestingLevel.TooHigh
     */
    
    public function collectRates(RateRequest $request)
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/shipping.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        // $logger->info('collectRates Raja Ongkir start'); 

        /** @var \Magento\Shipping\Model\Rate\Result $result */
        $result = $this->_rateResultFactory->create();

        if (!$request->getAllItems()) {
            $logger->info('No items in quote.');
            return $result;
        }

        $quotes = [];
        foreach ($request->getAllItems() as $item) {
            $product = $item->getProduct()->load($item->getProductId());
            if ($item->getParentItem() || $product->isVirtual()) {
                continue;
            }

            // Identify seller
            $sellerId = $item->getSellerId() ?: $product->getSellerId() ?: 'no_seller';
            if (!$this->helperData->isSellerEnabled($sellerId)) {
                $logger->info("Raja Ongkir disabled for seller $sellerId");
                continue;
            }

            if (!isset($quotes[$sellerId])) {
                $quotes[$sellerId] = [];
            }
            $quotes[$sellerId][] = $item;
        }

        // Process each seller’s items
        foreach ($quotes as $sellerId => $items) {
            $logger->info("Processing Raja Ongkir for seller: $sellerId");

            $totalWeight = 0;
            $totalValue = 0;
            foreach ($items as $item) {
                $product = $item->getProduct();
                $weight = $product->getWeight() ?: 1;
                $qty = $item->getQty();
                $totalWeight += $weight * $qty;
                $totalValue += $item->getRowTotal();
            }

            // --- Get destination info from request ---            
            $destPostcode = $request->getDestPostcode();
            $destCity = $request->getDestCity();
            $destRegion = $request->getDestRegionCode();
            $destCountry = $request->getDestCountryId();
            $logger->info('PostCode: '.$destPostcode);
            $logger->info('City: '.$destCity);
            $logger->info('Region: '.$destRegion);
            $logger->info('Country: '.$destCountry);

            if (!$destPostcode) {
                $logger->info("Missing destination postcode for seller $sellerId");
                continue;
            }

            $apiUrl = 'https://rajaongkir.komerce.id';
            $apiKey = $this->helperData->getApiKey($sellerId);
            $originPostcode = $this->helperData->getSellerOriginPostcode($sellerId); // e.g. 31398
            $originCity = $this->helperData->getSellerOriginCity($sellerId);
            $originRegion = $this->helperData->getSellerOriginRegion($sellerId);

            $logger->info('Origin region: '.$originRegion);

            $originId = $this->getLocationId($apiUrl, $apiKey, $originCity, $originPostcode, $originRegion);

            // $originPostcode = $this->helperData->getSellerOriginPostcode($sellerId); // e.g. 31398
            // $originUrl = $apiUrl . '/api/v1/destination/domestic-destination?search=' . urlencode($originPostcode) .'&limit=999&offset=999';
            // $originResponse = $this->callApi($originUrl, $apiKey);
            // $logger->info('callApi response: '.json_encode($originResponse));
            // $originId = $originResponse['data'][0]['id'] ?? null;
            // dummy origin 
            // $originId = "3109";
            
            // --- Call Raja Ongkir API ---
            try {                
                // $environment = $this->scopeConfig->getValue('carriers/rajaongkir/environment_type', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);                
                
                // Get receiver destination ID (search by postcode)
                // $destUrl = $apiUrl . '/tariff/api/v1/destination/search?keyword=' . urlencode($destPostcode);
                // $destUrl = $apiUrl . '/api/v1/destination/domestic-destination?search=' . urlencode($destPostcode) .'&limit=999&offset=999';
                // $destResponse = $this->callApi($destUrl, $apiKey);
                // $logger->info('callApi response: '.json_encode($destResponse));
                // $receiverId = $destResponse['data'][0]['id'] ?? null;
                // dummy receiver 
                // $receiverId = 3111;
                $receiverId = $this->getLocationId($apiUrl, $apiKey, $destCity, $destPostcode, $destRegion);
                $logger->info('Origin Id: '.$originId.' | Receiver Id: '.$receiverId);

                if (!$receiverId) {
                    $logger->info("Destination not found for postcode: $destPostcode");
                    continue;
                }

                $idrTotalValue = $this->convertSgdToIdr($totalValue);
                // --- Call RajaOngkir Domestic Cost API ---
                $calculateApiUrl = $apiUrl.'/api/v1/calculate/domestic-cost';
                $postData = [
                    'origin' => $originId, // e.g., 3109
                    'destination' => $receiverId, // e.g., 3111
                    'weight' => max(1, ceil($totalWeight)),
                    'courier' => 'jne:sicepat:ide:sap:jnt:ninja:tiki:lion:anteraja:pos:ncs:rex:rpx:sentral:star:wahana:dse',
                    'price' => 'lowest'
                ];

                // Log payload
                $logger->info('RajaOngkir payload: ' . json_encode($postData));

                // Execute CURL
                $this->curl->setHeaders([
                    'key' => $apiKey,
                    'Content-Type' => 'application/x-www-form-urlencoded'
                ]);

                $this->curl->post($calculateApiUrl, $postData);
                $responseBody = $this->curl->getBody();
                $calcResponse = json_decode($responseBody, true);

                // $logger->info('RajaOngkir calculate response: ' . $responseBody);

                if (!isset($calcResponse['data']) || !is_array($calcResponse['data'])) {
                    $logger->info('Invalid RajaOngkir response structure.');
                    continue;
                }

                // Loop through each courier option
                foreach ($calcResponse['data'] as $option) {
                    $carrierTitle = $option['name'] ?? 'RajaOngkir Courier';
                    $methodTitle = ($option['name'] ?? '') . ' - ' . ($option['service'] ?? '');
                    $cost = $option['cost'] ?? 0;

                    /** @var \Magento\Quote\Model\Quote\Address\RateResult\Method $method */
                    $method = $this->_rateMethodFactory->create();
                    $method->setCarrier($this->_code);
                    $method->setCarrierTitle($carrierTitle);
                    $method->setMethod($this->_code . '_' . ($option['code'] ?? ''));
                    $method->setMethodTitle($methodTitle);
                    $method->setPrice($cost);
                    $method->setCost($cost);

                    $result->append($method);
                }

            } catch (\Exception $e) {
                $logger->info('RajaOngkir API error: ' . $e->getMessage());
            }
        }

        return $result;
    }

    private function convertSgdToIdr($amount)
    {
        $fromCurrency = 'SGD';
        $toCurrency = 'IDR';
        try {
            $currency = $this->currencyFactory->create()->load($fromCurrency);
            $convertedAmount = $currency->convert($amount, $toCurrency);

            $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/shipping.log');
            $logger = new \Zend_Log();
            $logger->addWriter($writer);
            $logger->info("Converted {$amount} {$fromCurrency} → {$convertedAmount} {$toCurrency}");

            return round($convertedAmount, 2);
        } catch (\Exception $e) {
            return $amount; // fallback if conversion fails
        }
    }

    /**
     * Call RajaOngkir API helper
     */
    private function callApi($url, $apiKey)
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/shipping.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info('callApi');

        try {
            $client = new \Laminas\Http\Client($url, ['timeout' => 10]);
            $client->setHeaders(['key' => $apiKey]);
            $response = $client->send();
            return json_decode($response->getBody(), true);
        } catch (\Exception $e) {
            $logger->info('Catch: '.$e->getMessage());
        }        
    }

    /**
     * @return array
     */
    public function getAllowedMethods()
    {
        return ['rajaongkir' => $this->getConfigData('name')];
    }

    public function getLocationId($apiUrl, $apiKey, $destCity, $destPostcode, $destRegion)
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/shipping.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);

        $provinceId = null;
        $destId = 0;
        $provinceUrl = $apiUrl . '/api/v1/destination/province';
        $provinceResponse = $this->callApi($provinceUrl, $apiKey);
        // $logger->info('callApi response province: '.json_encode($provinceResponse));
        
        $provinces = $provinceResponse['data'] ?? [];
        $searchKeyword = $destRegion;

        // Filter provinces that match the keyword
        $matchedProvinces = array_filter($provinces, function($province) use ($searchKeyword) {
            return stripos($province['name'], $searchKeyword) !== false;
        });
        if (!empty($matchedProvinces)) {
            $firstMatch = reset($matchedProvinces);
            if ($firstMatch) {
                $provinceId = $firstMatch['id'];
            }                
        } else {
            $provinceId = null;
        }
        $logger->info('Province Id: '.$provinceId);

        if (!empty($provinceId)) {
            $cityUrl = $apiUrl . '/api/v1/destination/city/' . $provinceId;
            $cityResponse = $this->callApi($cityUrl, $apiKey);
            // $logger->info('callApi response city: '.json_encode($cityResponse));
            
            $cities = $cityResponse['data'] ?? [];
            $searchKeyword = $destCity;

            // Filter cities that match the keyword
            $matchedcities = array_filter($cities, function($city) use ($searchKeyword) {
                return stripos($searchKeyword, $city['name']) !== false;
            });
            if (!empty($matchedcities)) {
                $firstMatch = reset($matchedcities);
                if ($firstMatch) {
                    $destId = $firstMatch['id'];
                }                
            } else {
                $destId = null;
            }

            if (empty($destId)) {
                foreach ($cities as $city) {
                    $districtUrl = $apiUrl . '/api/v1/destination/district/' . urlencode($city['id']);
                    $districtResponse = $this->callApi($districtUrl, $apiKey);
                    // $logger->info('callApi response district: '.json_encode($districtResponse));
                    
                    $districts = $districtResponse['data'] ?? [];
                    $searchKeyword = $destCity;

                    // Filter districts that match the keyword
                    $matcheddistricts = array_filter($districts, function($city) use ($searchKeyword) {
                        return stripos($searchKeyword, $city['name']) !== false;
                    });
                    if (!empty($matcheddistricts)) {
                        $firstMatch = reset($matcheddistricts);
                        if ($firstMatch) {
                            $destId = $firstMatch['id'];
                        }                
                    } else {
                        $destId = null;
                    }
                }
            }    
        }
        
        return $destId;
    }

    protected function getDestinationId($postcode)
    {
        $baseUrl = rtrim($this->getConfigData('base_url'), '/');
        $apiKey = $this->getConfigData('api_key');
        $url = $baseUrl . '/tariff/api/v1/destination/search?keyword=' . urlencode($postcode);

        $this->curl->addHeader("x-api-key", $apiKey);
        $this->curl->get($url);
        $response = json_decode($this->curl->getBody(), true);

        if (isset($response['data'][0]['id'])) {
            return $response['data'][0]['id'];
        }
        return null;
    }

    protected function calculateShipping($originId, $destinationId, $weight, $itemValue)
    {
        $baseUrl = rtrim($this->getConfigData('base_url'), '/');
        $apiKey = $this->getConfigData('api_key');
        $url = $baseUrl . "/tariff/api/v1/calculate?"
            . http_build_query([
                'shipper_destination_id' => $originId,
                'receiver_destination_id' => $destinationId,
                'weight' => $weight,
                'item_value' => $itemValue,
                'cod' => 'no',
            ]);

        $this->curl->addHeader("x-api-key", $apiKey);
        $this->curl->get($url);
        $response = json_decode($this->curl->getBody(), true);

        $methods = [];
        if (isset($response['data'])) {
            // You may have: calculate_reguler, calculate_cargo, calculate_instant
            foreach (['calculate_reguler', 'calculate_cargo', 'calculate_instant'] as $group) {
                if (isset($response['data'][$group]) && is_array($response['data'][$group])) {
                    foreach ($response['data'][$group] as $service) {
                        $methods[] = [
                            'method_code' => $group . '_' . $service['courier_code'],
                            'method_title' => $service['courier_name'] . ' ' . $service['service_type'],
                            'price' => $service['shipping_cost'] ?? 0
                        ];
                    }
                }
            }
        }

        return $methods;
    }
}
