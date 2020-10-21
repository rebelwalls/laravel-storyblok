<?php

namespace RebelWalls\LaravelStoryblok\Client;

use Psr\Http\Message\ResponseInterface;
use Illuminate\Support\Collection;

/**
 * Storyblok Client
 *
 * @property array|string responseBody
 * @property integer responseCode
 * @property array responseHeaders
 */
class ClientResponse
{
    /**
     * @param ResponseInterface $response
     *
     * @return $this
     */
    public function make(ResponseInterface $response)
    {
        $httpResponseCode = $response->getStatusCode();
        $data = (string) $response->getBody()->getContents();
        $jsonResponseData = (array) json_decode($data, true);

        $this->responseBody = $jsonResponseData; // return response data as json if possible, raw if not
        $this->responseCode = $httpResponseCode;
        $this->responseHeaders = $response->getHeaders() ?: [];

        return $this;
    }

    /**
     * Gets the json response body
     *
     * @return Collection
     */
    public function getBody()
    {
        return collect($this->responseBody);
    }

    /**
     * Gets the response headers
     *
     * @return Collection
     */
    public function getHeaders()
    {
        return collect($this->responseHeaders);
    }

    /**
     * Gets the response status
     *
     * @return integer
     */
    public function getCode()
    {
        return $this->responseCode;
    }


    /**
     * Transforms links into a tree
     *
     * @return array
     */
    public function getAsTree()
    {
        if (!isset($this->responseBody)) {
            return [];
        }

        $tree = [];

        foreach ($this->responseBody['links'] as $item) {
            if (!isset($tree[$item['parent_id']])) {
                $tree[$item['parent_id']] = array();
            }

            $tree[$item['parent_id']][] = $item;
        }

        return $this->generateTree($tree, 0);
    }

    /**
     * Recursive function to generate tree
     *
     * @param array $items
     * @param integer $parent
     *
     * @return array
     */
    private function generateTree(array $items, int $parent = 0)
    {
        $tree = [];

        if (isset($items[$parent])) {
            $result = $items[$parent];

            foreach ($result as $item) {
                if (!isset($tree[$item['id']])) {
                    $tree[$item['id']] = [];
                }

                $tree[$item['id']]['item']  = $item;
                $tree[$item['id']]['children']  = $this->generateTree($items, $item['id']);
            }
        }

        return $tree;
    }
}
