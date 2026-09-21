<?php

declare(strict_types=1);

namespace ClickPesa\Resources;

use ClickPesa\Http\HttpClientInterface;

abstract class AbstractResource
{
    protected HttpClientInterface $http;

    public function __construct(HttpClientInterface $http)
    {
        $this->http = $http;
    }

    /**
     * @param string $endpoint
     * @param array<string, mixed> $query
     * @return array<string, mixed>
     */
    protected function requestGet(string $endpoint, array $query = []): array
    {
        $options = [];
        if (!empty($query)) {
            $options['query'] = $query;
        }

        return $this->http->request('GET', $endpoint, $options);
    }

    /**
     * @param string $endpoint
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    protected function requestPost(string $endpoint, array $data = []): array
    {
        return $this->http->request('POST', $endpoint, [
            'json' => $data,
        ]);
    }

    /**
     * @param string $endpoint
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    protected function requestPatch(string $endpoint, array $data = []): array
    {
        return $this->http->request('PATCH', $endpoint, [
            'json' => $data,
        ]);
    }

    /**
     * @param string $endpoint
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    protected function requestDelete(string $endpoint, array $data = []): array
    {
        $options = [];
        if (!empty($data)) {
            $options['json'] = $data;
        }

        return $this->http->request('DELETE', $endpoint, $options);
    }
}
