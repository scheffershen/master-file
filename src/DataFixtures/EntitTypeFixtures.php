<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\LovManagement\EntitType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * @codeCoverageIgnore
 */
final class EntitTypeFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    public const ENTIT_TYPE_UM_REFERENCE = 'entit-type-um';
    public const ENTIT_TYPE_CLIENT_REFERENCE = 'entit-type-client';
    public const ENTIT_TYPE_PRESTA_REFERENCE = 'entit-type-presta';

    public function load(ObjectManager $manager): void
    {
        $this->loadEntitType($manager);
    }

    public function loadEntitType(ObjectManager $manager): void
    {
        $sort = 1;
        foreach ($this->getEntitTypeData() as [$name, $code, $reference]) {
            $entity = new EntitType();
            $entity->setTitle($name);
            $entity->setCode($code);
            $entity->setSort($sort);
            $entity->setCreateUser($this->getReference(UserFixtures::ADMIN_USER_REFERENCE));
            $entity->setUpdateUser($this->getReference(UserFixtures::ADMIN_USER_REFERENCE));
            $manager->persist($entity);
            $this->addReference($reference, $entity);
            $sort++;
        }
        $manager->flush();
    }

    private function getEntitTypeData(): array
    {
        return [
            ['UM', 'um', self::ENTIT_TYPE_UM_REFERENCE],
            ['Client', 'client', self::ENTIT_TYPE_CLIENT_REFERENCE],
            ['Presta', 'presta', self::ENTIT_TYPE_PRESTA_REFERENCE],
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
        return ['DEV', 'PROD', 'TEST'];
    }
}
