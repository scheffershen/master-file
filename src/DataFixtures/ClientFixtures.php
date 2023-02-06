<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\UserManagement\Client;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * @codeCoverageIgnore
 */
final class ClientFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    public const CLIENT1_REFERENCE = 'client1';
    public const CLIENT2_REFERENCE = 'client2';

    public function load(ObjectManager $manager): void
    {
        $this->loadClient($manager);
    }

    public function loadClient(ObjectManager $manager): void
    {
        foreach ($this->getClientData() as [$name, $adress, $reference]) {
            $entity = new Client();
            $entity->setName($name);
            $entity->setAdress($adress);
            $entity->setCreateUser($this->getReference(UserFixtures::ADMIN_USER_REFERENCE));
            $entity->setUpdateUser($this->getReference(UserFixtures::ADMIN_USER_REFERENCE));
            $manager->persist($entity);
            $this->addReference($reference, $entity);
        }
        $manager->flush();
    }

    private function getClientData(): array
    {
        return [
            ['client 1', 'addresse 1', self::CLIENT1_REFERENCE],
            ['client 2', 'addresse 2', self::CLIENT2_REFERENCE]
        ];
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
        ];
    }

    public static function getGroups(): array
    {
        return ['TEST'];
    }

}
