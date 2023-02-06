<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\LovManagement\Scope;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * @codeCoverageIgnore
 */
final class ScopeFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    public const SCOPE_SYSTEME_REFERENCE = 'scope-systeme';
    public const SCOPE_GLOBALE_REFERENCE = 'scope-globale';
    public const SCOPE_LOCALE_REFERENCE = 'scope-locale';

    public function load(ObjectManager $manager): void
    {
        $this->loadScope($manager);
    }

    public function loadScope(ObjectManager $manager): void
    {
        $sort = 1;
        foreach ($this->getScopeData() as [$name, $code, $reference]) {
            $entity = new Scope();
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

    private function getScopeData(): array
    {
        return [
            ['Globale', 'SG', self::SCOPE_GLOBALE_REFERENCE],
            ['Locale', 'SL', self::SCOPE_LOCALE_REFERENCE],
            ['Système', 'SS', self::SCOPE_SYSTEME_REFERENCE],
        ];
    }

}
