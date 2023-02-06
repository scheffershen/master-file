<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\LovManagement\Obligation;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * bin/console doctrine:fixtures:load.
 *
 * @codeCoverageIgnore
 */
final class ObligationFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    public const OBLIGATION_VO_REFERENCE = 'obligation-vo';
    public const OBLIGATION_VF_REFERENCE = 'obligation-vf';

    public function load(ObjectManager $manager): void
    {
        $this->loadObligation($manager);
    }

    public function loadObligation(ObjectManager $manager): void
    {
        $sort = 1;
        foreach ($this->getObligationData() as [$name, $code, $reference]) {
            $entity = new Obligation();
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

    private function getObligationData(): array
    {
        return [
            ['Valeur de remplacement obligatoire', 'VO', self::OBLIGATION_VO_REFERENCE],
            ['Valeur de remplacement facultative', 'VF', self::OBLIGATION_VF_REFERENCE],
        ];
    }
}
