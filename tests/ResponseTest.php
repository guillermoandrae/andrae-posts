<?php

namespace AppTest;

use App\Response;
use Aws\DynamoDb\DynamoDbClient;
use Aws\DynamoDb\Marshaler;
use Guillermoandrae\Fisher\Db\DynamoDb\DynamoDbAdapter;
use Guillermoandrae\Fisher\Db\DynamoDb\KeyTypes;
use Guillermoandrae\Fisher\Repositories\PostsRepository;
use Guillermoandrae\Lambda\Contracts\ApiGatewayResponseInterface;
use PHPUnit\Framework\TestCase;

final class ResponseTest extends TestCase
{
    const TABLE_NAME = 'social-posts';

    protected ApiGatewayResponseInterface $response;

    public function testSendPut()
    {
        $this->getResponse()->setEvent([
            'httpMethod' => 'PUT',
            'body' => [
                'originalAuthor' => 'testing',
                'createdAt' => 'yesterday',
                'source' => 'Friendster',
            ]
        ]);
        $this->getResponse()->send();
        $this->assertEquals(201, $this->getResponse()->getStatusCode());
    }

    public function testSendGet()
    {
        $this->getResponse()->setEvent([
            'httpMethod' => 'GET',
            'queryStringParameters' => [
                'limit' => 25
            ]
        ]);
        $output = $this->getResponse()->send();
        $body = json_decode($output['body'], true);
        $data = $body['data'][0];
        $this->assertEquals('testing', $data['originalAuthor']);
    }

    public function testSendUnsupportedMethod()
    {
        $this->getResponse()->setEvent(['httpMethod' => 'DELETE']);
        $this->getResponse()->send();
        $this->assertEquals(405, $this->getResponse()->getStatusCode());
    }

    protected function setUp(): void
    {
        parent::setUp();
        $response = new Response();
        $sdk = new DynamoDbClient([
            'region' => 'us-east-1',
            'version' => 'latest',
            'endpoint' => 'http://localhost:8000',
            'credentials' => [
                'key' => 'not-a-key',
                'secret' => 'not-a-secret'
            ]
        ]);
        $dynamoDbAdapter = new DynamoDbAdapter($sdk, new Marshaler());
        $postRepository = new PostsRepository($dynamoDbAdapter);
        $response->setRepository($postRepository);
        if (!$dynamoDbAdapter->useTable(self::TABLE_NAME)->tableExists()) {
            $dynamoDbAdapter->useTable(self::TABLE_NAME)->createTable([
                'originalAuthor' => [
                    'type' => 'S', 'keyType' => KeyTypes::HASH
                ],
                'createdAt' => [
                    'type' => 'N', 'keyType' => KeyTypes::RANGE
                ]
            ]);
        }
        $this->response = $response;
    }

    private function getResponse(): ApiGatewayResponseInterface
    {
        return $this->response;
    }
}
