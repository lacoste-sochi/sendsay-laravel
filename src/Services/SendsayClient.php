<?php

namespace Rutrue\Sendsay\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Rutrue\Sendsay\Contracts\SendsayClientInterface;
use Rutrue\Sendsay\Exceptions\SendsayApiException;

class SendsayClient implements SendsayClientInterface
{
    private Client $client;

    public function __construct(
        private string $account,
        private string $apiKey,
        private string $baseUrl = 'https://api.sendsay.ru'
    ) {
        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'timeout' => 15.0,
        ]);
    }

    public function request(string $method, string $endpoint, array $data = []): array
    {
        try {
            $response = $this->client->post(
                "/general/api/v100/json/{$this->account}",
                [
                    'json' => $data,
                    'headers' => [
                        'Authorization' => "sendsay apikey=$this->apiKey",
                        'Content-Type' => 'application/json',
                    ],
                    'http_errors' => false
                ]
            );

            $body = json_decode($response->getBody(), true);

            if ($response->getStatusCode() !== 200) {
                throw new SendsayApiException(
                    $body['error']['message'] ?? 'API request failed',
                    $response->getStatusCode(),
                    null,
                    $body
                );
            }

            return $body;

        } catch (GuzzleException $e) {
            throw new SendsayApiException(
                'HTTP request failed: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }
}
