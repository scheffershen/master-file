<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\TemplateManagement\Section;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * @codeCoverageIgnore
 */
final class SectionFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    public const SECTION_HEADER_REFERENCE = 'section-header';
    public const SECTION_FOOTER_REFERENCE = 'section-footer';

    public function load(ObjectManager $manager): void
    {
        $this->loadSection($manager);
    }

    public function loadSection(ObjectManager $manager): void
    {
        foreach ($this->getSectionData() as [$name, $contenu, $position, $contenuEditable, $editable, $isPageBreak, $isAnnexe, $allowSubSection, $reference]) {
            $entity = new Section();
            $entity->setTitle($name);
            $entity->setContenu($contenu);
            $entity->setPosition($position);
            $entity->setContenuEditable($contenuEditable);
            $entity->setIsAnnexe($isAnnexe);            
            $entity->setAllowSubSection($allowSubSection);
            $entity->setEditable($editable);
            $entity->setIsPageBreak($isPageBreak);
            $entity->setCreateUser($this->getReference(UserFixtures::ADMIN_USER_REFERENCE));
            $entity->setUpdateUser($this->getReference(UserFixtures::ADMIN_USER_REFERENCE));
            $manager->persist($entity);
            if ($reference) {
                $this->addReference($reference, $entity);
            }        
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
        return ['TEST'];
    }

    private function getSectionData(): array
    {
        //$name, $contenu, $position, $contenuEditable, $editable, $isPageBreak, $isAnnexe, $allowSubSection, $reference
        return [
            ['Header', '', null, true, true, false, false, false, self::SECTION_HEADER_REFERENCE],
            ['Footer', '', null, true, true, false, false, false, self::SECTION_FOOTER_REFERENCE],
            ['PV System master file', '', 1, true, true, true,  false, false, ''],
            ['Table of contents', '', 2, false, true, true, false, false, ''],
            ['Abbreviations', '', 3, true, true, true, false, false, ''],
        ];
    }
}
