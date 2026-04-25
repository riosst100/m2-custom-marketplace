<?php

namespace TCGCollective\MarketplacePaymentAirwallex\Model\Client\Request;

use TCGCollective\MarketplacePaymentAirwallex\Model\Client\AbstractClient;
use Airwallex\Payments\Model\Client\Interfaces\BearerAuthenticationInterface;
use Psr\Http\Message\ResponseInterface;

class TestSuccessPayment extends AbstractClient implements BearerAuthenticationInterface
{
    /**
     * @return string
     */
    protected function getUri(): string
    {
        return 'simulation/issuing/create';
    }

    /**
     * @return string
     */
    protected function getMethod(): string
    {
        return 'POST';
    }

    protected function getBaseUrl(): string
    {
        $url = 'https://api-demo.airwallex.com/api/v1/';
        if ($this->configuration->isDemoMode()) {
            $url = 'https://api-demo.airwallex.com/api/v1/';
        }
        return $url;
    }

    public function setRequestParams(
        $authCode, 
        $cardNumber, 
        $merchantCategoryCode, 
        $merchantInfo, 
        $amount, 
        $currency
    ) {
        return $this->setParams([
            "auth_code" => $authCode,
            "card_number" => $cardNumber,
            "merchant_category_code" => $merchantCategoryCode,
            "merchant_info" => $merchantInfo,
            "transaction_amount" => $amount,
            "transaction_currency" => $currency
        ]);
    }

    /**
     * @param ResponseInterface $response
     *
     * @return array
     */
    protected function parseResponse(ResponseInterface $response): array
    {
        $content = $response->getBody()->getContents();
        dd($response);
        if (empty($content)) return [];
        $content = json_decode($content, true);
        return $content['items'] ?? [];
    }
}
