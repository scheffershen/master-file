<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\LovManagement\TypeVariable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * @codeCoverageIgnore
 */
final class TypeVariableFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    public const TYPE_TXT_REFERENCE = 'type-text';
    public const TYPE_INT_REFERENCE = 'type-integer';
    public const TYPE_IMG_REFERENCE = 'type-image';
    public const TYPE_DATE_REFERENCE = 'type-date';
    public const TYPE_LTXT_REFERENCE = 'type-long-text';
    public const TYPE_AUTRE_REFERENCE = 'type-autre';
    public const TYPE_OPTION_REFERENCE = 'type-option';

    public function load(ObjectManager $manager): void
    {
        $this->loadTypeVariable($manager);
    }

    public function loadTypeVariable(ObjectManager $manager): void
    {
        $sort = 1;
        foreach ($this->getTypeVariableData() as [$name, $code, $reference]) {
            $entity = new TypeVariable();
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

    private function getTypeVariableData(): array
    {
        return [
            ['Text', 'TXT', self::TYPE_TXT_REFERENCE],
            ['Integer', 'INT', self::TYPE_INT_REFERENCE],
            ['Image', 'IMG', self::TYPE_IMG_REFERENCE],
            ['Date', 'DATE', self::TYPE_DATE_REFERENCE],
            ['Text Long', 'LTXT', self::TYPE_LTXT_REFERENCE],
            ['Autre', 'AUTRE', self::TYPE_AUTRE_REFERENCE],
            ['Option', 'OPTION', self::TYPE_OPTION_REFERENCE],
        ];
    }

}
