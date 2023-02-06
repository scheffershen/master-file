<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\LovManagement\WorkRole;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * @codeCoverageIgnore
 */
final class WorkRoleFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    public const WORK_ROLE_PVEU_REFERENCE = 'work-role-EUQPPV';
    public const WORK_ROLE_PVDEP_REFERENCE = 'work-role-deputy-EUQPPV';
    public const WORK_ROLE_PVCC_REFERENCE = 'work-role-Contact-PV-Client';
    public const WORK_ROLE_PVA_REFERENCE = 'work-role-Autre';

    public function load(ObjectManager $manager): void
    {
        $this->loadWorkRole($manager);
    }

    public function loadWorkRole(ObjectManager $manager): void
    {
        $sort = 1;
        foreach ($this->getWorkRoleData() as [$name, $code, $reference]) {
            $entity = new WorkRole();
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

    private function getWorkRoleData(): array
    {
        return [
            ['EUQPPV', 'PVEU', self::WORK_ROLE_PVEU_REFERENCE],
            ['deputy EUQPPV', 'PVDEP', self::WORK_ROLE_PVDEP_REFERENCE],
            ['Contact PV Client', 'PVCC', self::WORK_ROLE_PVCC_REFERENCE],
            ['Autre', 'PVA', self::WORK_ROLE_PVA_REFERENCE],
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
