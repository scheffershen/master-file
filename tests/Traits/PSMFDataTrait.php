<?php

namespace App\Tests\Traits;

use App\Entity\UserManagement\Client;
use App\Entity\UserManagement\User;
use App\Entity\LovManagement\Gender;
use App\Entity\LovManagement\WorkRole;
use App\Entity\LovManagement\EntitType;
use App\Entity\PSMFManagement\PSMF;
use Faker\Factory;

trait PSMFDataTrait
{
	protected function loadPSMFData(User $admin, Client $client, EntitType $um, User $euqppv, User $deputy_euqppv, User $contact_pv_client): PSMF
	{
		$faker = Factory::create();

	    $psmf = new PSMF();
	    $psmf->setTitle($faker->title());
	    $psmf->setclient($client);
        $psmf->setEuqppvEntity($um);
        $psmf->setEudravigNum($faker->randomNumber());
        $psmf->setEuQPPV($euqppv);
        $psmf->setDeputyEUQPPV($deputy_euqppv);
        $psmf->setContactPvClient($contact_pv_client);
	    $psmf->setCreateUser($admin);
	    $psmf->setUpdateUser($admin);
        $this->em->persist($psmf);
        $this->em->flush();

        return $psmf;	
	}
}