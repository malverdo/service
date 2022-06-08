<?php

namespace App\Controller;

use App\Message\SmsNotification;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Messenger\Bridge\Amqp\Transport\AmqpStamp;
use Symfony\Component\Messenger\MessageBusInterface;

class DefaultController extends AbstractController
{


    public function index(MessageBusInterface $bus): JsonResponse
    {
        // will cause the SmsNotificationHandler to be called
        $bus->dispatch(new SmsNotification('Look! I creasdated a message!'));

        return new JsonResponse('hello');
    }
}