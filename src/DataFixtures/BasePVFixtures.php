<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\LovManagement\BasePV;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * @codeCoverageIgnore
 */
final class BasePVFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    public function load(ObjectManager $manager): void
    {
        $this->loadClasse($manager);
    }

    public function loadClasse(ObjectManager $manager): void
    {
        $sort = 1;
        foreach ($this->getClasseData() as [$name, $code]) {
            $entity = new BasePV();
            $entity->setTitle($name);
            $entity->setCode($code);
            $entity->setSort($sort);
            $entity->setCreateUser($this->getReference(UserFixtures::ADMIN_USER_REFERENCE));
            $entity->setUpdateUser($this->getReference(UserFixtures::ADMIN_USER_REFERENCE));
            $manager->persist($entity);
            $sort++;
        }
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
        ];
    }

    public static function getGroups(): array
    {
        return ['DEV', 'PROD', 'TEST'];
    }

    private function getClasseData(): array
    {
        return [
            ['EVEREPORT', 'EVEREPORT'],
            ['SAFETY EASY', 'SAFETY_EASY'],
            ['OTHER', 'OTHER'],
        ];
    }
}
