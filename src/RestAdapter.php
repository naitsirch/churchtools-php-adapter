<?php

namespace Naitsirch\ChurchTools\Api;

use Psr\Http\Client\ClientInterface;

/**
 * Adapter for the ChurchTools' REST API.
 *
 * @author naitsirch <naitsirch@e.mail.de>
 * @see https://demo.church.tools/api
 */
class RestAdapter
{
    private ClientInterface $client;

    private string $token;
    
    public function __construct(ClientInterface $client, string $token)
    {
        $this->client = $client;
        $this->token = $token;
    }

}
