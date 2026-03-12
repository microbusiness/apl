<?php

namespace App\Service;

use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class TransService
{
    private HttpClientInterface $httpClient;
    private string $urlService;

    public function __construct(HttpClientInterface $httpClient, string $urlService)
    {
        $this->httpClient = $httpClient;
        $this->urlService = $urlService;
    }

    public function translate(string $language, array $data): array
    {
        $response = $this->httpClient->request('POST', $this->urlService . '/translate', [
            'headers' => [
                'Accept' => 'application/json',
                'X-Request-Id' => uuid_create(UUID_TYPE_RANDOM),
            ],
            'json' => [
                'language' => $language,
                'data' => $data,
            ],
        ]);

        return $response->toArray();
    }

    public function getTrans(string $locale): array {
        $uuid = UuidAlias::v4();
        $reqHash = $uuid->toString();
        $response = $this->httpClient->request('POST', 'http://localhost:8091/translate',
            [
                'headers' => [
                    'Accept' => 'application/json',
                    'X-Request-Id' => $reqHash,
                ],
                'json' => [
                    'requestHash' => $reqHash,
                    'language' => $locale,
                    'Data' => [
                        '3333' => [
                            'textHash' => '3333',
                            'text' => 'Это первое сообщение',
                            'num' => 1
                        ],
                        '4444' => [
                            'textHash' => '4444',
                            'text' => 'Это второе сообщение',
                            'num' => 2
                        ]
                    ]
                ]
            ],
        );

        // Get the response content
        $content = $response->getContent();

        // Decode JSON response
        return $response->toArray();
    }
}
