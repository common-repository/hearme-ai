<?php

namespace HearMe\Includes;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\RequestOptions;
use HearMe\Exceptions\HMA_NotFoundEpisodeException;
use HearMe\Exceptions\HMA_InternalServerErrorException;

final class HMA_Connector
{
    /**
     * @var Client
     */
    private $client;

    private $apiKey;

    public function __construct()
    {
        $options = get_option('hear_me_options');
        $this->apiKey = isset($options['api_key']) && !empty($options['api_key']) ? $options['api_key'] : null;

        $this->client = new Client([
            'base_uri' => HEAR_ME_API_URL
        ]);
    }

    public function getEpisodeInfo(string $id)
    {
        try {
            return $this->_get('episode/' . $id, ['key' => $this->apiKey]);
        } catch (ClientException $exception) {
            if ($exception->getResponse()->getStatusCode() === 404) {
                throw new HMA_NotFoundEpisodeException();
            }
            if ($exception->getResponse()->getStatusCode() === 500) {
                throw new HMA_InternalServerErrorException();
            }
        }
    }

    public function checkKey()
    {
        try {
            return $this->_get('echo', ['key' => $this->apiKey]);
        } catch (ClientException $exception) {
            if ($exception->getResponse()->getStatusCode() === 404) {
                throw new HMA_NotFoundEpisodeException();
            }
            if ($exception->getResponse()->getStatusCode() === 500) {
                throw new HMA_InternalServerErrorException();
            }
        }
    }

    public function getEpisodePlayer(string $id)
    {
        try {
            return $this->_get('episode/' . $id . '/player', ['key' => $this->apiKey]);
        } catch (ClientException $exception) {
            if ($exception->getResponse()->getStatusCode() === 404) {
                throw new HMA_NotFoundEpisodeException();
            }
            if ($exception->getResponse()->getStatusCode() === 500) {
                throw new HMA_InternalServerErrorException();
            }
        }
    }

    public function getEpisodeCost(string $id)
    {
        try {
            return $this->_get('episode/' . $id . '/cost', ['key' => $this->apiKey]);
        } catch (ClientException $exception) {
            if ($exception->getResponse()->getStatusCode() === 404) {
                throw new HMA_NotFoundEpisodeException();
            }
            if ($exception->getResponse()->getStatusCode() === 500) {
                throw new HMA_InternalServerErrorException();
            }
        }
    }

    public function sendEpisodeDraft(string $id, string $publishDate, string $content, string $title = null, string $author = null)
    {
        $data = [
            'publishDate' => $publishDate,
            'html' => $content
        ];

        if ($title) {
            $data['title'] = $title;
        }

        if ($author) {
            $data['author'] = $author;
        }
        return $this->_post('episode/' . $id . '?key=' . $this->apiKey, $data);
    }

    public function publishEpisode(string $id)
    {
        return $this->_post('episode/' . $id . '/publication?key=' . $this->apiKey, []);
    }

    private function _post(string $url, array $data)
    {
        $response = $this->client->post($url, [
            RequestOptions::JSON => $data
        ]);

        return json_decode((string) $response->getBody());
    }

    private function _get(string $url, array $parameters)
    {
        $response = $this->client->get($url, [
            'query' => $parameters
        ]);

        return json_decode((string) $response->getBody());
    }
}
