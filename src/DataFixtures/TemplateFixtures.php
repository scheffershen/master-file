<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\TemplateManagement\Template;
use App\Entity\TemplateManagement\Section;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * @codeCoverageIgnore
 */
final class TemplateFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    public const TEMPLATE_REFERENCE = 'template';

    public function load(ObjectManager $manager): void
    {
        $this->loadTemplate($manager);
    }

    public function loadTemplate(ObjectManager $manager): void
    {
        foreach ($this->getTemplateData() as [$header, $footer]) {
            $entity = new Template();
            $entity->setHeader($header);
            $entity->setFooter($footer);            
            $manager->persist($entity);
            $this->addReference(self::TEMPLATE_REFERENCE, $entity);   
            break;
        }
        $manager->flush();

        $this->updateSection($manager);
    }

    private function updateSection(ObjectManager $manager): void
    {
        $sections = $manager->getRepository(Section::class)->findAll();
        foreach ($sections as $section) {
            $section->setTemplate($this->getReference(self::TEMPLATE_REFERENCE));
            $manager->persist($section);
        }
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            SectionFixtures::class,
        ];
    }

    public static function getGroups(): array
    {
        return ['TEST'];
    }

    private function getTemplateData(): array
    {
        return [
            [$this->getReference(SectionFixtures::SECTION_HEADER_REFERENCE), $this->getReference(SectionFixtures::SECTION_FOOTER_REFERENCE)],
        ];
    }
}
