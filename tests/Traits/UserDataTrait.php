<?php

namespace App\Tests\Traits;

use App\Entity\UserManagement\Client;
use App\Entity\UserManagement\User;
use App\Entity\LovManagement\Gender;
use App\Entity\LovManagement\WorkRole;
use App\Entity\LovManagement\EntitType;
use Faker\Factory;

trait UserDataTrait
{
	protected function loadUserData(User $admin, Gender $gender, WorkRole $workRole_euqppv, WorkRole $workRole_deputy_euqppv, WorkRole $workRole_contact_pv_client, Client $client, EntitType $um): array
	{
		$faker = Factory::create();

                $passwordEncoder = $this->client->getContainer()->get('security.user_password_encoder.generic');

                $euqppv = new User();
                $euqppv->setUsername($faker->userName());
                $euqppv->setPassword($passwordEncoder->encodePassword($euqppv, $faker->password()));
                $euqppv->setEmail($faker->email());
                $euqppv->setFirstName($faker->firstName());
                $euqppv->setLastName($faker->lastName());
                $euqppv->setIsEnable(true);
                $euqppv->addClient($client);
                $euqppv->setMailAlerte(false);
                $euqppv->setPvUser(true);
                $euqppv->setCvUri('image.jpg');
                $euqppv->addWorkRole($workRole_euqppv);
                $euqppv->setWorkFunction($faker->jobTitle());
                $euqppv->setMobile($faker->phoneNumber());
                $euqppv->setFixe($faker->phoneNumber());
                $euqppv->setFax($faker->phoneNumber());
                $euqppv->setWorkAttachment($um);
                $euqppv->setWorkName($faker->company());
                $euqppv->setCreateUser($admin);
                $euqppv->setUpdateUser($admin);
                $this->em->persist($euqppv);

                $deputy_euqppv = new User();
                $deputy_euqppv->setUsername($faker->userName());
                $deputy_euqppv->setPassword($passwordEncoder->encodePassword($deputy_euqppv, $faker->password()));
                $deputy_euqppv->setEmail($faker->email());
                $deputy_euqppv->setFirstName($faker->firstName());
                $deputy_euqppv->setLastName($faker->lastName());
                $deputy_euqppv->setIsEnable(true);
                $deputy_euqppv->addClient($client);
                $deputy_euqppv->setMailAlerte(false);
                $deputy_euqppv->setPvUser(true);
                $deputy_euqppv->setCvUri('image.jpg');
                $deputy_euqppv->addWorkRole($workRole_deputy_euqppv);
                $deputy_euqppv->setWorkFunction($faker->jobTitle());
                $deputy_euqppv->setMobile($faker->phoneNumber());
                $deputy_euqppv->setFixe($faker->phoneNumber());
                $deputy_euqppv->setFax($faker->phoneNumber());
                $deputy_euqppv->setWorkAttachment($um);
                $deputy_euqppv->setWorkName($faker->company());
                $deputy_euqppv->setCreateUser($admin);
                $deputy_euqppv->setUpdateUser($admin);        
                $this->em->persist($deputy_euqppv);

                $contact_pv_client = new User();
                $contact_pv_client->setUsername($faker->userName());
                $contact_pv_client->setPassword($passwordEncoder->encodePassword($contact_pv_client, $faker->password()));
                $contact_pv_client->setEmail($faker->email());
                $contact_pv_client->setFirstName($faker->firstName());
                $contact_pv_client->setLastName($faker->lastName());
                $contact_pv_client->setIsEnable(true);
                $contact_pv_client->addClient($client);
                $contact_pv_client->setMailAlerte(false);
                $contact_pv_client->setPvUser(true);
                $contact_pv_client->setCvUri('image.jpg');
                $contact_pv_client->addWorkRole($workRole_contact_pv_client);
                $contact_pv_client->setWorkFunction($faker->jobTitle());
                $contact_pv_client->setMobile($faker->phoneNumber());
                $contact_pv_client->setFixe($faker->phoneNumber());
                $contact_pv_client->setFax($faker->phoneNumber());
                $contact_pv_client->setWorkAttachment($um);
                $contact_pv_client->setWorkName($faker->company());
                $contact_pv_client->setCreateUser($admin);
                $contact_pv_client->setUpdateUser($admin);         
                $this->em->persist($contact_pv_client);        

                $this->em->flush();   
                     
                return [$euqppv, $deputy_euqppv, $contact_pv_client];
	}
}