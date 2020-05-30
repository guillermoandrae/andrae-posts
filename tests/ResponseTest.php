<?php

namespace GuillermoandraeTest\App;

use Guillermoandrae\App\Response;
use PHPUnit\Framework\TestCase;

final class ResponseTest extends TestCase
{
    public function testSend()
    {
        $response = new Response();
        $output = $response->send();
        $body = json_decode($output['body'], true);
        $this->assertArrayHasKey('template', $body['meta']);
    }
}
