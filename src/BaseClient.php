<?php

namespace CronosSupport\Storyblok\Client;

use Closure;
use GuzzleHttp\Client as Guzzle;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\RequestOptions;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Throwable;

/**
 * Storyblok Client
 *
 * @property Guzzle client
 */
class BaseClient
{
    const EXCEPTION_GENERIC_HTTP_ERROR = "An HTTP Error has occurred!";

    /**
     * @var string
     */
    private $apiKey;

    /**
     * @var integer
     */
    protected $maxRetries = 5;

    /**
     * @var Guzzle
     */
    protected $client;

    /**
     * @var float
     */
    protected $timeout;

    /**
     * @param string $apiKey
     * @param string $apiEndpoint
     * @param string $apiVersion
     * @param boolean $ssl
     */
    public function __construct(string $apiKey = null, string $apiEndpoint = "api.storyblok.com", string $apiVersion = "v1", bool $ssl = false)
    {
        $handlerStack = HandlerStack::create(new CurlHandler());
        $handlerStack->push(Middleware::retry($this->retryDecider(), $this->retryDelay()));

        $this->setApiKey($apiKey);

        $this->client = new Guzzle([
            'base_uri'=> $this->generateEndpoint($apiEndpoint, $apiVersion, $ssl),
            'handler' => $handlerStack
        ]);
    }

    /**
     * @return Closure
     */
    public function retryDecider()
    {
        return function ($retries, $request, $response = null, RequestException $exception = null) {
            // Limit the number of retries
            if ($retries >= $this->maxRetries) {
                return false;
            }

            // Retry connection exceptions
            if ($exception instanceof ConnectException) {
                return true;
            }

            if ($response) {
                // Retry on server errors
                if ($response->getStatusCode() >= 500 || $response->getStatusCode() == 429) {
                    return true;
                }
            }

            return false;
        };
    }

    /**
     * delay 1s 2s 3s 4s 5s
     *
     * @return Closure
     */
    public function retryDelay()
    {
        return function ($numberOfRetries) {
            return 1000 * $numberOfRetries;
        };
    }

    /**
     * @param string $apiKey
     *
     * @return BaseClient
     */
    public function setApiKey(string $apiKey): BaseClient
    {
        $this->apiKey = $apiKey;

        return $this;
    }

    /**
     * @return string
     */
    public function getApiKey()
    {
        return $this->apiKey;
    }

    /**
     * @param integer $maxRetries
     *
     * @return BaseClient
     */
    public function setMaxRetries(int $maxRetries): BaseClient
    {
        $this->maxRetries = $maxRetries;

        return $this;
    }

    /**
     * Set timeout in seconds
     *
     * @param integer $timeout
     *
     * @return BaseClient
     */
    public function setTimeout(int $timeout): BaseClient
    {
        $this->timeout = $timeout;

        return $this;
    }

    /**
     * @return integer
     */
    public function getTimeout()
    {
        return $this->timeout;
    }

    /**
     * @param string $apiEndpoint
     * @param string $apiVersion
     * @param boolean $ssl
     *
     * @return string
     */
    private function generateEndpoint(string $apiEndpoint, string $apiVersion, bool $ssl)
    {
        $prefix = $this instanceof ManagementClient ? '' : '/cdn';
        $protocol = $ssl ? 'https://' : 'http://';

        return $protocol . $apiEndpoint . "/" . $apiVersion . $prefix . "/";
    }

    /**
     * @param string $endpointUrl
     * @param array $queryString
     *
     * @return ClientResponse
     *
     * @throws ApiException
     */
    public function get(string $endpointUrl, array $queryString = [])
    {
        try {
            $query = http_build_query($queryString, null, '&');
            $string = preg_replace('/%5B(?:[0-9]|[1-9][0-9]+)%5D=/', '=', $query);
            $requestOptions = [RequestOptions::QUERY => $string];

            if ($this->getTimeout()) {
                $requestOptions[RequestOptions::TIMEOUT] = $this->getTimeout();
            }

            if ($this instanceof ManagementClient) {
                $requestOptions[RequestOptions::HEADERS] = ['Authorization' => $this->apiKey];
            }

            $response = $this->client->request('GET', $endpointUrl, $requestOptions);
        } catch (Throwable $e) {
            throw new ApiException(self::EXCEPTION_GENERIC_HTTP_ERROR . ' - ' . $e->getMessage(), $e->getCode());
        }

        return (new ClientResponse())->make($response);
    }
}
