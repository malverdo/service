<?php

namespace App\Client;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class ItemsccService
{
    private $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    public function getItemsOffset(int $offset = 0): ?string
    {
//        $offset = $offset * 3000;

        $response = $this->client->request(
            'GET',
            'https://itemsccservice.com/v2/items/show/csgo?token=199eee3e44c074f7b308e852a02ce92f&offset=' . $offset
        );

        if (empty($response->toArray()['items'])) {
            $return = null;
        } else {
            $return = serialize($response->toArray()['items']);
        }

        return $return;
    }
}