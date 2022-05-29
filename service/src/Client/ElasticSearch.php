<?php

namespace App\Client;


use Elastic\Elasticsearch\ClientBuilder;
use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\Exception\AuthenticationException;
use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastic\Elasticsearch\Exception\MissingParameterException;
use Elastic\Elasticsearch\Exception\ServerResponseException;
use Http\Promise\Promise;

class ElasticSearch
{
    /**
     * @var Client
     */
    private $client;


    /**
     * @return Client
     */
    public function getClient(): Client
    {
        return $this->client;
    }

    /**
     * @throws AuthenticationException
     *
     */
    public function __construct($host, $port)
    {
        $address = $this->createAddress($host, $port);
        $this->client = ClientBuilder::create()
            ->setHttpClient(new \GuzzleHttp\Client)
            ->setSSLVerification(false)
            ->setHosts([$address])->build();

    }

    /**
     * @param $host
     * @param $port
     * @return string
     */
    private function createAddress($host, $port): string
    {
        return sprintf('%s:%s', $host, $port);
    }



    /**
     * @param array $params
     * @throws ClientResponseException
     * @throws MissingParameterException
     * @throws ServerResponseException
     */
    public function createIndex(array $params)
    {
        $this->client->indices()->create($params);
    }

    /**
     * @param array $params
     * @throws ClientResponseException
     * @throws MissingParameterException
     * @throws ServerResponseException
     *
     */
    public function addDocument(array $params)
    {
        $this->client->index($params);
    }

    /**
     * @param array $params
     * @throws ClientResponseException
     * @throws ServerResponseException
     *
     */
    public function addDocumentBulk(array $params)
    {
        $this->client->bulk($params);
    }

    /**
     * @param string $param
     * @return \Elastic\Elasticsearch\Response\Elasticsearch|Promise
     * @throws ClientResponseException
     * @throws MissingParameterException
     * @throws ServerResponseException
     */
    public function exists(string $param)
    {
        return $this->client->exists(['id'=> 0, 'index' => $param]);
    }

    /**
     * @throws ServerResponseException
     * @throws ClientResponseException
     */
    public function search($param)
    {
        return $this->client->search($param);
    }
}