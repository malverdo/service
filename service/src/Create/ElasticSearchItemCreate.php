<?php

namespace App\Create;

use App\Client\ElasticSearch;
use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastic\Elasticsearch\Exception\MissingParameterException;
use Elastic\Elasticsearch\Exception\ServerResponseException;

class ElasticSearchItemCreate
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
    public function createIndex($index)
    {
        if (!$this->elasticSearch->exists($index)){
            $params = [
                'index' => $index,
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
                                            'name' => [
                                                'type' => 'text'
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
     *
     */
    public function createDocumentBulk($items, $index)
    {
        $params = [
            'index' => $index,
            'body' => []
        ];
        foreach ($items as $key => $item) {
            $params['body'][] = [
                'index' => [
                    '_index' => $index,
                ]
            ];

            $params['body'][] = [
                'data' => [
                    $index => [
                        'hash_name' => md5($item['steam_market_hash_name'] ?? $key),
                        'name' => $item['steam_market_hash_name'] ?? $key,
                        'value' => [
                            time() => [
                                'date' => date('y-m-d'),
                                'price' => $item['steam_price_en'] ?? $item,
                            ]
                        ]
                    ]
                ]
            ];
        }
        $this->elasticSearch->addDocumentBulk($params);

    }

    /**
     *
     */
    public function updatePriceDocumentBulk($items, $index)
    {
        $params = [
            'index' => $index,
            'body'  => []
        ];
        foreach ($items as $item) {
            $params['body'][] = [
                'update' => [
                    '_index' => $index,
                    '_id' => $item['id']
                ]
            ];

            $params['body'][] = [
                'doc' => [
                    'data' => [
                        'item' => [
                            'value' => [
                                  time() =>[
                                       'date' => date('y-m-d'),
                                       'price' => $item['price'],
                                   ]
                            ]
                        ]
                    ]
                ]
            ];
        }
        $this->elasticSearch->addDocumentBulk($params);

    }
}