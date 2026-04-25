<?php

namespace TCGCollective\MarketPlace\Controller\Marketplace\ImageProxy;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Response\Http;
use Magento\Framework\HTTP\Client\Curl;

class Index extends Action
{
    protected $curl;
    protected $response;

    public function __construct(
        Context $context,
        Curl $curl,
        Http $response
    ) {
        parent::__construct($context);
        $this->curl = $curl;
        $this->response = $response;
    }

    public function execute()
    {
        $url = $this->getRequest()->getParam('url');
        if (!$url) {
            return $this->response->setHttpResponseCode(400);
        }

        // 🔒 whitelist domain
        $allowedHosts = [
            'seller.tcgcollective.co',
            'seller-staging.tcgcollective.co'
        ];

        $host = parse_url($url, PHP_URL_HOST);
        if (!in_array($host, $allowedHosts, true)) {
            return $this->response->setHttpResponseCode(403);
        }
        
        try {
    // $this->curl->setTimeout(1000);

    // ⚠️ STAGING ONLY
    $this->curl->setOption(CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_2);
$this->curl->setOption(CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
$this->curl->setOption(CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);

    $this->curl->setOption(CURLOPT_SSL_VERIFYPEER, false);
    $this->curl->setOption(CURLOPT_SSL_VERIFYHOST, false);

    $this->curl->get($url);

    if ($this->curl->getStatus() !== 200) {
        return $this->response->setHttpResponseCode(502);
    }

    $headers = $this->curl->getHeaders();
    $contentType = $headers['content-type'] ?? 'image/jpeg';

    $this->response
        ->setHeader('Content-Type', $contentType, true)
        ->setHeader('Cache-Control', 'public, max-age=86400', true)
        ->setBody($this->curl->getBody());

    return $this->response;

} catch (\Exception $e) {
    dd($e);
    return $this->response->setHttpResponseCode(500);
}

    }
}
