<?php

namespace App;

use Guillermoandrae\Lambda\Contracts\ApiGatewayResponseInterface;
use Guillermoandrae\Lambda\Contracts\AbstractApiGatewayResponse;
use Guillermoandrae\Repositories\RepositoryInterface;

final class Response extends AbstractApiGatewayResponse
{
    private RepositoryInterface $repository;

    public function setRepository(RepositoryInterface $repository): ApiGatewayResponseInterface
    {
        $this->repository = $repository;
        return $this;
    }

    public function getRepository(): RepositoryInterface
    {
        return $this->repository;
    }

    public function handle(): void
    {
        switch ($this->getEvent()['httpMethod']) {
            case 'GET':
                $this->findPosts();
                break;
            case 'PUT':
                $this->createPost();
                break;
            default:
                $this->setStatusCode(405);
                $this->setBodyData(['message' => 'Unsupported HTTP method']);
                break;
        }
    }

    private function findPosts(): void
    {
        $query = $this->getEvent()['queryStringParameters'];
        $limit = $query['limit'];
        $items = $this->getRepository()->findAll(0, $limit);
        $this->addBodyMeta('count', count($items));
        $this->setBodyData($items->toArray());
    }

    private function createPost(): void
    {
        $data = $this->getEvent()['body'];
        $data['createdAt'] = strtotime($data['createdAt']);
        $data['platform'] = $data['source'];
        unset($data['source']);
        if ($this->getRepository()->create($data)) {
            $this->setStatusCode(201);
            $this->setBodyData([]);
        }
    }
}