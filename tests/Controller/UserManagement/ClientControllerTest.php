<?php

namespace App\Tests\Controller\UserManagement;

use App\Entity\UserManagement\Client;
use App\Entity\UserManagement\User;
use App\DataFixtures\UserFixtures;
use App\Tests\Controller\AbstractControllerTest;
use App\Tests\Traits\ClientDataTrait;
use Faker\Factory;

/**
* @covers \App\Controller\UserManagement\ClientController
* @group application
* php vendor/bin/phpunit tests/Controller/UserManagement/ClientControllerTest.php
*/
class ClientControllerTest extends AbstractControllerTest
{
    use ClientDataTrait;

    private $company;

    /**
     * @test
     */   	
	public function indexTest()
    {
        $this->client->request('GET', $this->router->generate('admin_client_index', ['_locale' => self::DEFAULT_LANGUAGE])); 
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h4.page-title', $this->translator->trans('clients', [], 'messages', self::DEFAULT_LANGUAGE)); 
        
        $content = $this->client->getResponse()->getContent();

        self::assertStringContainsString($this->company->getName(), $content, 'Could not find client name');
        self::assertStringContainsString($this->company->getAdress(), $content, 'Could not find client adress');            
    }	

    /**
     * @test
     */   	
	public function newTest()
    { 
        $crawler = $this->client->request('GET', $this->router->generate('admin_client_new', ['_locale' => self::DEFAULT_LANGUAGE])); 
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h4.page-title', $this->translator->trans('client.add', [], 'messages', self::DEFAULT_LANGUAGE));

        $faker = Factory::create();
        $company = $faker->company();
        $adress = $faker->address();
        $reason = $faker->text();

        $form = $crawler->selectButton($this->translator->trans('action.save', [], 'messages', self::DEFAULT_LANGUAGE))->form([
            'client[name]' => $company, 
            'client[adress]' => $adress,
            'client[reason]' => $reason
        ]);	
        $this->client->submit($form);

        $content = $this->client->getResponse()->getContent();
        self::assertStringContainsString($this->translator->trans('client.flash.created', [], 'messages', self::DEFAULT_LANGUAGE), $content, 'Could not find flash success message');

        $client = $this->em->getRepository(Client::class)->findOneBy(['name'=>$company]);
        self::assertSame($company, $client->getName());  
        self::assertSame($adress, $client->getAdress()); 
        self::assertSame($reason, $client->getReason());         
    }    

    /**
     * @test
     */     
    public function showTest()
    {

        $this->client->request('GET', $this->router->generate('admin_client_show', ['id'=>$this->company->getId(), '_locale' => self::DEFAULT_LANGUAGE])); 
        $content = $this->client->getResponse()->getContent();

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h4.page-title', $this->translator->trans('clients', [], 'messages', self::DEFAULT_LANGUAGE));

        self::assertStringContainsString($this->company->getName(), $content, 'Could not find client name');
        self::assertStringContainsString($this->company->getAdress(), $content, 'Could not find client adress');
    }

    /**
     * @test
     */ 
    public function editTest()
    {
        $crawler = $this->client->request('GET', $this->router->generate('admin_client_edit', ['id'=>$this->company->getId(), '_locale' => self::DEFAULT_LANGUAGE])); 
        $content = $this->client->getResponse()->getContent();

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h4.page-title', $this->translator->trans('client.edit', [], 'messages', self::DEFAULT_LANGUAGE));

        $faker = Factory::create();
        $company = $faker->company();
        $adress = $faker->address();
        $reason = $faker->text();

        $form = $crawler->selectButton($this->translator->trans('action.save', [], 'messages', self::DEFAULT_LANGUAGE))->form();
        $prefix = $this->getFirstPrefixForm($form);        
        $form->setValues([
            $prefix.'[name]' => $company, 
            $prefix.'[adress]' => $adress,
            $prefix.'[reason]' => $reason,
        ]); 
        $form[$prefix.'[logo]']->upload($this->client->getContainer()->get('kernel')->getProjectDir().'/tests/Fixtures/image.jpg');
        $this->client->submit($form);

        $content = $this->client->getResponse()->getContent();
        self::assertStringContainsString($this->translator->trans('client.flash.updated', [], 'messages', self::DEFAULT_LANGUAGE), $content, 'Could not find flash success message');

        $crawler = $this->client->request('GET', $this->router->generate('admin_client_show', ['id'=>$this->company->getId(), '_locale' => self::DEFAULT_LANGUAGE])); 
        $content = $this->client->getResponse()->getContent();

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h4.page-title', $this->translator->trans('clients', [], 'messages', self::DEFAULT_LANGUAGE));

        self::assertStringContainsString(htmlentities($company, ENT_QUOTES), $content, 'Could not find client name');
        self::assertStringContainsString($adress, $content, 'Could not find client adress');
        
        // removeUploadedImage
        $logo = $crawler->selectImage('logo')->image();
        $this->removeUploadedImage(basename($logo->getUri()));        
    }     

    protected function setUp(): void
    {
        parent::setUp();
        $this->init();
    }
    
    protected function init()
    {
        $admin = $this->em->getRepository(User::class)->findOneBy(['username'=>UserFixtures::ADMIN]);
        $this->company = $this->loadClientData($admin);
        
    }

    protected function tearDown(): void
    {        
        $this->kill(); 
        parent::tearDown();
    }      
}