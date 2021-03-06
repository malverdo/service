<?php

namespace App\Repository;

use App\Client\ElasticSearch;
use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastic\Elasticsearch\Exception\ServerResponseException;

class ItemRepository
{
    /**
     * @var ElasticSearch
     */
    public $elasticSearch;

    public function __construct(ElasticSearch $elasticSearch)
    {
        $this->elasticSearch = $elasticSearch;
    }

    /**
     * @throws ServerResponseException
     * @throws ClientResponseException
     */
    public function terms(array $terms, string $index = 'item', string $path = 'data.item.hash_name' )
    {
        $params = [
            'index' => $index,
            'body' => [
                'query' => [
                    'terms' => [
                        $path => $terms
                    ]
                ]
            ]
        ];


        return $this->elasticSearch->search($params);
    }

    /**
     * @throws ServerResponseException
     * @throws ClientResponseException
     */
    public function termOne(string $term, string $index = 'item', string $path = 'data.item.hash_name' )
    {
        $params = [
            'index' => $index,
            'body' => [
                'query' => [
                    'term' => [
                        $path => $term
                    ]
                ]
            ]
        ];

        $item = $this->elasticSearch->search($params);

        return $item['hits']['hits'][0]['_source']['data']['item'];
    }

}