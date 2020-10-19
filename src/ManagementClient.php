<?php

namespace CronosSupport\Storyblok\Client;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use Throwable;

/**
 * Class ManagementClient
 *
 * @package CronosSupport\Storyblok\Client
 */
class ManagementClient extends BaseClient
{
    /**
     * @param string $endpointUrl
     * @param array $payload
     *
     * @return ClientResponse
     *
     * @throws ApiException
     */
    public function post(string $endpointUrl, array $payload)
    {
        try {
            $requestOptions = [
                RequestOptions::JSON => $payload,
                RequestOptions::HEADERS => ['Authorization' => $this->getApiKey()]
            ];

            $responseObj = $this->client->request('POST', $endpointUrl, $requestOptions);
        } catch (Throwable $exception) {
            throw new ApiException(self::EXCEPTION_GENERIC_HTTP_ERROR . ' - ' . $exception->getMessage(), $exception->getCode());
        }

        return (new ClientResponse())->make($responseObj);
    }

    /**
     * @param string $endpointUrl
     * @param array $payload
     *
     * @return ClientResponse
     *
     * @throws ApiException
     */
    public function put(string $endpointUrl, array $payload)
    {
        try {
            $requestOptions = [
                RequestOptions::JSON => $payload,
                RequestOptions::HEADERS => ['Authorization' => $this->getApiKey()]
            ];

            $responseObj = $this->client->request('PUT', $endpointUrl, $requestOptions);
        } catch (Throwable $exception) {
            throw new ApiException(self::EXCEPTION_GENERIC_HTTP_ERROR . ' - ' . $exception->getMessage(), $exception->getCode());
        }

        return (new ClientResponse())->make($responseObj);
    }

    /**
     * @param string $endpointUrl
     *
     * @return ClientResponse
     *
     * @throws ApiException
     */
    public function delete(string $endpointUrl)
    {
        try {
            $requestOptions = [
                RequestOptions::HEADERS => ['Authorization' => $this->getApiKey()]
            ];

            $responseObj = $this->client->request('DELETE', $endpointUrl, $requestOptions);
        } catch (Throwable $exception) {
            throw new ApiException(self::EXCEPTION_GENERIC_HTTP_ERROR . ' - ' . $exception->getMessage(), $exception->getCode());
        }

        return (new ClientResponse())->make($responseObj);
    }
}
