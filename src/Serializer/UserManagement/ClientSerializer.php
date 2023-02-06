<?php

namespace App\Serializer\UserManagement;

use App\Entity\UserManagement\Client;

/**
 * ClientSerializer
 */
class ClientSerializer
{
    public function serialize(Client $client)
    {
        return [
        		'name' => $client->getName(),
                'adress' => $client->getAdress(),
                'logoUri' => $client->getLogoUri()
            ];     	
    }
}