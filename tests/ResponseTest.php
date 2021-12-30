<?php

namespace AppTest;

use App\Response;
use Guillermoandrae\DynamoDb\Constant\AttributeTypes;
use Guillermoandrae\DynamoDb\Constant\KeyTypes;
use Guillermoandrae\DynamoDb\DynamoDbAdapter;
use Guillermoandrae\Lambda\Contracts\ApiGatewayResponseInterface;
use Guillermoandrae\Repositories\RepositoryFactory;
use PHPUnit\Framework\TestCase;

final class ResponseTest extends TestCase
{
    const TABLE_NAME = 'social-posts';

    protected ApiGatewayResponseInterface $response;

    public function testSendPut()
    {
        $this->getResponse()->setEvent([
            'httpMethod' => 'PUT',
            'body' => json_encode([
                'originalAuthor' => 'testing',
                'createdAt' => 'yesterday',
                'source' => 'Friendster',
            ])
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
        $dynamoDbAdapter = new DynamoDbAdapter();
        if (!$dynamoDbAdapter->tableExists(self::TABLE_NAME)) {
            $dynamoDbAdapter->createTable([
                'originalAuthor' => [AttributeTypes::STRING, KeyTypes::HASH],
                'createdAt' => [AttributeTypes::NUMBER, KeyTypes::RANGE],
            ], self::TABLE_NAME);
        }
        $postRepository = RepositoryFactory::factory('posts', $dynamoDbAdapter);
        $response->setRepository($postRepository);
        $this->response = $response;
    }

    private function getResponse(): ApiGatewayResponseInterface
    {
        return $this->response;
    }
}
