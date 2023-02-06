<?php

namespace App\Tests\Traits;

use App\Entity\UserManagement\Client;
use App\Entity\UserManagement\User;
use Faker\Factory;

trait ClientDataTrait
{
	protected function loadClientData(User $admin): Client
	{
		$faker = Factory::create();

	    $client = new Client();
	    $client->setName($faker->company());
	    $client->setAdress($faker->address());
	    $client->setReason($faker->sentence());
	    $client->setCreateUser($admin);
	    $client->setUpdateUser($admin);
        $this->em->persist($client);
        $this->em->flush();

        return $client;	
	}
}