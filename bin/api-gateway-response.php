<?php declare(strict_types = 1);

use Guillermoandrae\App\Response;

require dirname(__DIR__) . '/vendor/autoload.php';

lambda(function (array $event) {
    $response = new Response();
    $response->setEvent($event);
    return $response->send();
});
