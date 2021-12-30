<?php declare(strict_types = 1);

use App\Response;
use Aws\DynamoDb\DynamoDbClient;
use Aws\DynamoDb\Marshaler;
use Guillermoandrae\Fisher\Db\DynamoDb\DynamoDbAdapter;
use Guillermoandrae\Fisher\Repositories\PostsRepository;

require dirname(__DIR__) . '/vendor/autoload.php';

return function (array $event) {
    $response = new Response();
    $sdk = new DynamoDbClient([
        'region' => 'us-east-1',
        'version' => 'latest',
    ]);
    $dynamoDbAdapter = new DynamoDbAdapter($sdk, new Marshaler());
    $postRepository = new PostsRepository($dynamoDbAdapter);
    $response->setRepository($postRepository);
    $response->setEvent($event);
    return $response->send();
};
