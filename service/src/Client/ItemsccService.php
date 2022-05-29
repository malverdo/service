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

    public function getItemsOffset(int $offset = 0): ?array
    {

        $response = $this->client->request(
            'GET',
            'https://itemsccservice.com/v2/items/show/csgo?token=199eee3e44c074f7b308e852a02ce92f&offset=' . $offset
        );

        if (empty($response->toArray()['items'])) {
            $return = null;
        } else {
            $return = $response->toArray()['items'];
        }

        return $return;
    }
}