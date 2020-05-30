<?php

namespace Guillermoandrae\App;

use Guillermoandrae\Lambda\Contracts\AbstractApiGatewayResponse;

final class Response extends AbstractApiGatewayResponse
{
    public function getBody(): array
    {
        $body = parent::getBody();
        $body['meta']['template'] = 'Remove all of this template jazz, fam...';
        return $body;
    }
}
