<?php

namespace RebelWalls\LaravelStoryblok\Client;

class Client extends BaseClient
{
    const EXCEPTION_GENERIC_HTTP_ERROR = "An HTTP Error has occurred!";

    /**
     * @var boolean
     */
    private $editModeEnabled;

    /**
     * @var string
     */
    private $resolveRelations;

    /**
     * @param string $apiKey
     * @param string $apiEndpoint
     * @param string $apiVersion
     * @param boolean $ssl
     */
    public function __construct(string $apiKey = null, string $apiEndpoint = "api.storyblok.com", string $apiVersion = "v1", bool $ssl = false)
    {
        parent::__construct($apiKey, $apiEndpoint, $apiVersion, $ssl);

        $this->handleEditMode();
    }

    /**
     * Sets editmode to receive draft versions
     *
     * @param boolean $enabled
     *
     * @return Client
     */
    public function setEditMode(bool $enabled = true): Client
    {
        $this->editModeEnabled = $enabled;

        return $this;
    }

    /**
     * Gets a story by the slug identifier
     *
     * @param string $slug
     *
     * @return ClientResponse
     *
     * @throws ApiException
     */
    public function getStoryBySlug(string $slug): ClientResponse
    {
        return $this->getStory($slug);
    }

    /**
     * Gets a story by it’s UUID
     *
     * @param string $uuid
     *
     * @return ClientResponse
     *
     * @throws ApiException
     */
    public function getStoryByUuid(string $uuid): ClientResponse
    {
        return $this->getStory($uuid, true);
    }

    /**
     * Gets a story
     *
     * @param string $slug
     * @param boolean $byUuid
     *
     * @return ClientResponse
     *
     * @throws ApiException
     */
    private function getStory(string $slug, bool $byUuid = false): ClientResponse
    {
        $version = 'draft'; // todo: this needs to follow/reflect the publishing flow, but for dev purposes, we always want the latest version

        $key = 'stories/' . $slug;

        $options = array(
            'token' => $this->getApiKey(),
            'version' => $version,
        );

        if ($byUuid) {
            $options['find_by'] = 'uuid';
        }

        if ($this->resolveRelations) {
            $options['resolve_relations'] = $this->resolveRelations;
        }

        try {
            return $this->get($key, $options);
        } catch (\Exception $e) {
            throw new ApiException(self::EXCEPTION_GENERIC_HTTP_ERROR . ' - ' . $e->getMessage(), $e->getCode());
        }
    }

    /**
     * Gets a list of stories
     *
     * array(
     *    'starts_with' => $slug,
     *    'with_tag' => $tag,
     *    'sort_by' => $sort_by,
     *    'per_page' => 25,
     *    'page' => 0
     * )
     *
     * @param array $options
     *
     * @return ClientResponse
     *
     * @throws ApiException
     */
    public function getStories($options = array())
    {
        $version = 'draft'; // todo: this needs to follow/reflect the publishing flow, but for dev purposes, we always want the latest version
        $endpointUrl = 'stories/';

        $options = array_merge($options, array(
            'token' => $this->getApiKey(),
            'version' => $version,
        ));

        return $this->get($endpointUrl, $options);
    }

    /**
     * Gets a list of tags
     *
     * array(
     *    'starts_with' => $slug
     * )
     *
     * @param array $options
     *
     * @return ClientResponse
     *
     * @throws ApiException
     */
    public function getTags($options = array())
    {
        $version = 'draft'; // todo: this needs to follow/reflect the publishing flow, but for dev purposes, we always want the latest version
        $endpointUrl = 'tags/';

        $options = array_merge($options, array(
            'token' => $this->getApiKey(),
            'version' => $version,
        ));

        return $this->get($endpointUrl, $options);
    }

    /**
     * Gets a list of datasource entries
     *
     * @param string $slug
     * @param array $options
     *
     * @return ClientResponse
     *
     * @throws ApiException
     */
    public function getDatasourceEntries(string $slug, array $options = [])
    {
        $version = 'draft'; // todo: this needs to follow/reflect the publishing flow, but for dev purposes, we always want the latest version
        $endpointUrl = 'datasource_entries/';

        $options = array_merge($options, array(
            'token' => $this->getApiKey(),
            'version' => $version,
            'datasource' => $slug
        ));

        return $this->get($endpointUrl, $options);
    }

    /**
     * @return void
     */
    private function handleEditMode(): void
    {
        $this->editModeEnabled = isset($_GET['_storyblok']) ? $_GET['_storyblok'] : false;
    }
}
