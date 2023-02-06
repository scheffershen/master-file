<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\LovManagement\Gender;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * @codeCoverageIgnore
 */
final class GenderFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    public const GENDER_HOMME_REFERENCE = 'gender-homme';
    public const GENDER_FEMME_REFERENCE = 'gender-femme';

    public function load(ObjectManager $manager): void
    {
        $this->loadGender($manager);
    }

    public function loadGender(ObjectManager $manager): void
    {
        $sort = 1;
        foreach ($this->getGenderData() as [$name, $code, $reference]) {
            $entity = new Gender();
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

    private function getGenderData(): array
    {
        return [
            ['Homme', 'GH', self::GENDER_HOMME_REFERENCE],
            ['Femme', 'GF', self::GENDER_FEMME_REFERENCE],
        ];
    }
}
