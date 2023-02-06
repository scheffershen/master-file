<?php

namespace App\Tests\Controller\PSMFManagement;

use App\Entity\PSMFManagement\PSMF;
use App\Entity\UserManagement\Client;
use App\Entity\UserManagement\User;
use App\Entity\LovManagement\Gender;
use App\Entity\LovManagement\WorkRole;
use App\Entity\LovManagement\EntitType;
use App\DataFixtures\UserFixtures;
use App\Tests\Controller\AbstractControllerTest;
use App\Tests\Traits\ClientDataTrait;
use App\Tests\Traits\UserDataTrait;
use App\Tests\Traits\PSMFDataTrait;
use Faker\Factory;

/**
* @covers \App\Controller\PSMFManagement\PSMFController
* @group application
* @group PSMFController
* php vendor/bin/phpunit tests/Controller/PSMFManagement/PSMFControllerTest.php
*/
class PSMFControllerTest extends AbstractControllerTest
{
    use ClientDataTrait;
    use UserDataTrait;
    use PSMFDataTrait;

    /**
     * @test
     */   	
	public function indexTest()
    {
        $this->client->request('GET', $this->router->generate('admin_psmf_index', ['_locale' => self::DEFAULT_LANGUAGE])); 
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h4.page-title', $this->translator->trans('psmfs', [], 'messages', self::DEFAULT_LANGUAGE));    	
    }

    /**
     * @test
     */   	
	public function newTest()
    { 
        $admin = $this->em->getRepository(User::class)->findOneBy(['username'=>UserFixtures::ADMIN]);
        $gender = $this->em->getRepository(Gender::class)->findOneBy(['code'=>'GF']);
        $workRole_euqppv = $this->em->getRepository(WorkRole::class)->findOneBy(['code'=>'PVEU']);
        $workRole_deputy_euqppv = $this->em->getRepository(WorkRole::class)->findOneBy(['code'=>'PVDEP']);
        $workRole_contact_pv_client = $this->em->getRepository(WorkRole::class)->findOneBy(['code'=>'PVCC']);
        $um = $this->em->getRepository(EntitType::class)->findOneBy(['code'=>'um']);

        $client = $this->loadClientData($admin);
        [$euqppv, $deputy_euqppv, $contact_pv_client] = $this->loadUserData($admin, $gender, $workRole_euqppv, $workRole_deputy_euqppv, $workRole_contact_pv_client, $client, $um);

        $crawler = $this->client->request('GET', $this->router->generate('admin_psmf_new', ['_locale' => self::DEFAULT_LANGUAGE])); 
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h4.page-title', $this->translator->trans('psmf.add', [], 'messages', self::DEFAULT_LANGUAGE));
        
        $faker = Factory::create();
        $psmf_title = $faker->title();
        $psmf_eudravigNum = $faker->randomNumber();

        $form = $crawler->selectButton($this->translator->trans('action.save', [], 'messages', self::DEFAULT_LANGUAGE))->form([
            'psmf[title]' => $psmf_title,
            'psmf[client]' => $client->getId(),
            'psmf[euqppvEntity]' => $um->getId(),
            'psmf[eudravigNum]' => $psmf_eudravigNum,
            'psmf[euQPPV]' => $euqppv->getId(),
            'psmf[deputyEUQPPV]' => $deputy_euqppv->getId(),
            'psmf[contactPvClient]' => $contact_pv_client->getId(),
        ]);	
        $this->client->submit($form);

        $content = $this->client->getResponse()->getContent();
        self::assertStringContainsString($psmf_title, $content, 'Could not find flash success message');

        $psmf = $this->em->getRepository(PSMF::class)->findOneBy(['eudravigNum' => $psmf_eudravigNum]);
        self::assertSame($psmf_title, $psmf->getTitle());
    }    

    /**
     * @test
     */
    public function disableTest() 
    {
        $admin = $this->em->getRepository(User::class)->findOneBy(['username'=>UserFixtures::ADMIN]);
        $gender = $this->em->getRepository(Gender::class)->findOneBy(['code'=>'GF']);
        $workRole_euqppv = $this->em->getRepository(WorkRole::class)->findOneBy(['code'=>'PVEU']);
        $workRole_deputy_euqppv = $this->em->getRepository(WorkRole::class)->findOneBy(['code'=>'PVDEP']);
        $workRole_contact_pv_client = $this->em->getRepository(WorkRole::class)->findOneBy(['code'=>'PVCC']);
        $um = $this->em->getRepository(EntitType::class)->findOneBy(['code'=>'um']);

        $client = $this->loadClientData($admin);
        [$euqppv, $deputy_euqppv, $contact_pv_client] = $this->loadUserData($admin, $gender, $workRole_euqppv, $workRole_deputy_euqppv, $workRole_contact_pv_client, $client, $um);

        $psmf = $this->loadPSMFData($admin, $client, $um, $euqppv, $deputy_euqppv, $contact_pv_client);

        $this->client->request('GET', $this->router->generate('admin_psmf_disable', ['id'=>$psmf->getId(), '_locale' => self::DEFAULT_LANGUAGE])); 
        $content = $this->client->getResponse()->getContent();
        
        if ($psmf->getIsValid()) {
            self::assertStringContainsString($this->translator->trans('psmf.flash.disable', [], 'messages', self::DEFAULT_LANGUAGE), $content, 'Could not find flash success message');
        } else {
            self::assertStringContainsString($this->translator->trans('psmf.flash.enable', [], 'messages', self::DEFAULT_LANGUAGE), $content, 'Could not find flash success message');
        }
    }
    
    protected function setUp(): void
    {
        parent::setUp();
    }
    
    protected function tearDown(): void
    {                
        $this->kill(); 
        parent::tearDown();
    }       
}