<?php

namespace App\Builder;

use App\Client\ElasticSearch;
use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastic\Elasticsearch\Exception\MissingParameterException;
use Elastic\Elasticsearch\Exception\ServerResponseException;

class ElasticSearchBuilder
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
     * @throws MissingParameterException
     */
    public function createIndexItem()
    {
        if (!$this->elasticSearch->exists('item')){
            $params = [
                'index' => 'item',
                'body' => [
                    'mappings' => [
                        'properties' => [
                            'data' => [
                                'type' => 'object',
                                'properties' => [
                                    'item' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'name_hash' => [
                                                'type' => 'keyword'
                                            ],
                                            'internals' => [
                                                'type' => 'object',
                                                'properties' => [
                                                    'date' => [
                                                        'type' => 'date',
                                                        "format" => "yyyy-MM-dd ||strict_date_optional_time_nanos||epoch_millis||basic_date_time||basic_ordinal_date"
                                                    ],
                                                    'price' => [
                                                        'type' => 'long'
                                                    ]
                                                ]
                                            ]
                                        ]
                                    ],
                                ]
                            ]

                        ]
                    ]
                ],
                'settings' => [
                    'number_of_shards' => 2,
                    'number_of_replicas' => 0
                ]
            ];
            $this->elasticSearch->createIndex($params);
        }
    }

    /**
     * @throws ServerResponseException
     * @throws ClientResponseException
     * @throws MissingParameterException
     *
     */
    public function createDocumentItems($items)
    {
        foreach ($items as $item) {
            $params = [
                'index' => 'item',
                'body'  => [
                    'data' => [
                        'item' => [
                            'hash_name' => $item['steam_market_hash_name'],
                            'value' => [
                                [
                                    'date' => date('y-m-d'),
                                    'price' => $item['steam_price_en'],
                                ]
                            ]
                        ]
                    ]
                ]
            ];

            $this->elasticSearch->addDocument($params);
        }
    }
}