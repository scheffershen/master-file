<?php

namespace App\Message\UserManagement;

use App\Entity\UserManagement\Client;

class ClientLogoCreatedOrUpdated
{
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function getClient(): Client
    {
        return $this->client;
    }
}
