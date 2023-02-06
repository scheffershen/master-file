<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\LovManagement\Pays;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * @codeCoverageIgnore
 */
final class PaysFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    public function load(ObjectManager $manager): void
    {
        $this->loadClasse($manager);
    }

    public function loadClasse(ObjectManager $manager): void
    {
        $sort = 1;
        foreach ($this->getClasseData() as [$name, $code]) {
            $entity = new Pays();
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
            ['AUSTRIA', 'AUSTRIA'],
            ['BELGIUM', 'BELGIUM'],
            ['BULGARIA', 'BULGARIA'],
            ['CROATIA', 'CROATIA'],
            ['CYPRUS', 'CYPRUS'],
            ['CZECH REPUBLIC', 'CZECH_REPUBLIC'],
            ['DENMARK', 'DENMARK'],
            ['ESTONIA', 'ESTONIA'],
            ['FINLAND', 'FINLAND'],
            ['FRANCE', 'FRANCE'],
            ['ICELAND', 'ICELAND'],            
            ['GERMANY', 'GERMANY'],
            ['GREECE', 'GREECE'],    
            ['HUNGARY', 'HUNGARY'], 
            ['SOUTHERN', 'SOUTHERN'], 
            ['ITALY', 'ITALY'], 
            ['LATVIA', 'LATVIA'],                                                             
            ['POLAND', 'POLAND'],
            ['PORTUGAL', 'PORTUGAL'],
            ['ROMANIA', 'ROMANIA'], 
            ['IRELAND', 'IRELAND'],   
            ['SLOVAKIA', 'SLOVAKIA'],    
            ['SLOVENIA', 'SLOVENIA'],
            ['SPAIN', 'SPAIN'],
            ['LITHUANIA', 'LITHUANIA'], 
            ['SWEDEM', 'SWEDEM'], 
            ['LUXEMBOURG', 'LUXEMBOURG'],    
            ['UNITED KINGDOM', 'UNITED_KINGDOM'],
            ['NETHERLAND', 'NETHERLAND'],          
            ['MALTA', 'MALTA'],  
            ['NORWAY', 'NORWAY'],     
        ];
    }
}
