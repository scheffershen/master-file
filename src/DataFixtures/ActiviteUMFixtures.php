<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\LovManagement\ActiviteUM;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * @codeCoverageIgnore
 */
final class ActiviteUMFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    public function load(ObjectManager $manager): void
    {
        $this->loadClasse($manager);
    }

    public function loadClasse(ObjectManager $manager): void
    {
        $sort = 1;
        foreach ($this->getClasseData() as [$name, $code]) {
            $entity = new ActiviteUM();
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
            ['EU QPPV BACKUP', 'EU_QPPV_BACKUP'],
            ['CALL MANAGEMENT WORKING HOURS', 'CALL_MANAGEMENT_WORKING_HOURS'],
            ['CALL MANAGEMENT NON WORKING HOURS', 'CALL_MANAGEMENT_NON_WORKING_HOURS'],
            ['CASE MANAGEMENT', 'CASE_MANAGEMENT'],
            ['ICSR SUBMISSION', 'ICSR_SUBMISSION'],
            ['LITERATURE MONITORING', 'LITERATURE_MONITORING'],
            ['SIGNAL MANAGEMENT', 'SIGNAL_MANAGEMENT'],
            ['PSUR MANAGEMENT', 'PSUR_MANAGEMENT'],
            ['RMP MANAGEMENT', 'RMP_MANAGEMENT'],
            ['VARIATION MANAGEMENT', 'VARIATION_MANAGEMENT'],
            ['RESPONSES TO THE AUTHORITIES', 'RESPONSES_TO_THE_AUTHORITIES'],            
            ['COMMUNICATION OF SAFETY CONCERNS', 'COMMUNICATION_OF_SAFETY_CONCERNS'],
        ];
    }
}
