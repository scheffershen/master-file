<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\TemplateManagement\Variable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * @codeCoverageIgnore
 */
final class VariableFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    public const VARIABLE_SYSTEM_REFERENCE = 'variable-system';
    public const VARIABLE_GLOBALE_REFERENCE = 'variable-globale';
    public const VARIABLE_LOCALE_TXT_REFERENCE = 'variable-locale-text';
    public const VARIABLE_LOCALE_LTEXT_REFERENCE = 'variable-locale-long-text';
    public const VARIABLE_LOCALE_IMG_REFERENCE = 'variable-locale-image';

    public function load(ObjectManager $manager): void
    {
        $this->loadVariable($manager);
    }

    public function loadVariable(ObjectManager $manager): void
    {
        foreach ($this->getVariableData() as [$balise, $label, $type, $scope, $obligatore, $userHelp, $reference]) {
            $entity = new Variable();
            $entity->setBalise($balise);
            $entity->setLabel($label);
            $entity->setType($this->getReference($type));
            $entity->setScope($this->getReference($scope));
            $entity->setObligation($this->getReference($obligatore));
            $entity->setUserHelp($userHelp);
            $entity->setCreateUser($this->getReference(UserFixtures::ADMIN_USER_REFERENCE));
            $entity->setUpdateUser($this->getReference(UserFixtures::ADMIN_USER_REFERENCE));
            $manager->persist($entity);
            $this->addReference($reference, $entity);                           
        }
        $manager->flush();
    }

    private function getVariableData(): array
    {
        return [
            ['VARIABLE_SYSTEM', self::VARIABLE_SYSTEM_REFERENCE, TypeVariableFixtures::TYPE_TXT_REFERENCE, ScopeFixtures::SCOPE_SYSTEME_REFERENCE, ObligationFixtures::OBLIGATION_VF_REFERENCE, 'VARIABLE_SYSTEM', self::VARIABLE_SYSTEM_REFERENCE],
            ['VARIABLE_GLOBALE', self::VARIABLE_GLOBALE_REFERENCE, TypeVariableFixtures::TYPE_TXT_REFERENCE, ScopeFixtures::SCOPE_GLOBALE_REFERENCE, ObligationFixtures::OBLIGATION_VF_REFERENCE, 'VARIABLE_GLOBALE', self::VARIABLE_GLOBALE_REFERENCE],
            ['VARIABLE_LOCALE_TXT', self::VARIABLE_LOCALE_TXT_REFERENCE, TypeVariableFixtures::TYPE_TXT_REFERENCE, ScopeFixtures::SCOPE_LOCALE_REFERENCE, ObligationFixtures::OBLIGATION_VF_REFERENCE, 'VARIABLE_LOCALE_TXT', self::VARIABLE_LOCALE_TXT_REFERENCE],
            ['VARIABLE_LOCALE_LTEXT', self::VARIABLE_LOCALE_LTEXT_REFERENCE, TypeVariableFixtures::TYPE_LTXT_REFERENCE, ScopeFixtures::SCOPE_LOCALE_REFERENCE, ObligationFixtures::OBLIGATION_VF_REFERENCE, 'VARIABLE_LOCALE_LTEXT', self::VARIABLE_LOCALE_LTEXT_REFERENCE],
            ['VARIABLE_LOCALE_IMG', self::VARIABLE_LOCALE_IMG_REFERENCE, TypeVariableFixtures::TYPE_IMG_REFERENCE, ScopeFixtures::SCOPE_LOCALE_REFERENCE, ObligationFixtures::OBLIGATION_VF_REFERENCE, 'VARIABLE_LOCALE_IMG', self::VARIABLE_LOCALE_IMG_REFERENCE],
        ];
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            ScopeFixtures::class,
            TypeVariableFixtures::class,
            ObligationFixtures::class
        ];
    }

    public static function getGroups(): array
    {
        return ['TEST'];
    }

}
