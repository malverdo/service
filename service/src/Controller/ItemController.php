<?php

namespace App\Controller;



use App\Repository\ItemRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class ItemController extends AbstractController
{
    /**
     * @var ItemRepository
     */
    private $itemRepository;

    public function __construct(ItemRepository $itemRepository)
    {
        $this->itemRepository = $itemRepository;
    }

    public function search(Request $request, $item): JsonResponse
    {
        $gte = !empty($request->query->get('gte')) ? strtotime($request->query->get('gte')) : 1;
        $lte = !empty($request->query->get('lte')) ? strtotime($request->query->get('lte')) : 99999999999;
        $items = [];
        $item = $this->itemRepository->termOne(md5($item));

        /**
         * @todo оптимизировать
         */
        foreach ($item['value'] as $key => $value) {
            if ($gte <= $key && $lte <= $key) {
                $items[] = $value;
            }
        }

        $respond = [
            "status" => "success",
            "data" => [
                $item['name'] => array_values($items ?? $item['value'])
            ]

        ];

        return new JsonResponse($respond);
    }

}