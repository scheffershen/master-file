<?php

namespace App\Tests\Traits;

use App\Entity\UserManagement\LoggedMessage;
use Faker\Factory;

trait LoggedMessageDataTrait
{
	protected function loadLoggedMessageData(): LoggedMessage
	{
		$faker = Factory::create();

	    $logged_message = new LoggedMessage();
	    $logged_message->setFrom([$faker->email()]);
	    $logged_message->setTo([$faker->email()]);
	    $logged_message->setSubject($faker->sentence());
	    $logged_message->setBody($faker->text());
	    $logged_message->setDate((new \DateTime()));
	    $logged_message->setGeneratedId($faker->uuid());
	    $logged_message->setResult(1);
	    $this->em->persist($logged_message);
	    $this->em->flush();

        return $logged_message;	
	}
}