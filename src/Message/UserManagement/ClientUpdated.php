<?php

namespace App\Message\UserManagement;

use App\Entity\UserManagement\Client;

class ClientUpdated
{
    private $client;
    private $old;
    private $action;

    public function __construct(string $action, array $old, Client $client)
    {
        $this->client = $client;
        $this->old = $old;    
        $this->action = $action;         
    }

    public function getClient(): Client
    {
        return $this->client;
    }

    public function getOld(): array 
    {
    	return $this->old;
    }

    public function getAction(): string 
    {
        return $this->action;
    }      
}
