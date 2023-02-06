<?php

namespace App\Tests\Controller\UserManagement;

use App\Entity\UserManagement\User;
use App\Entity\LovManagement\Gender;
use App\Entity\LovManagement\WorkRole;
use App\Entity\LovManagement\EntitType;
use App\DataFixtures\UserFixtures;
use App\Tests\Controller\AbstractControllerTest;
use App\Tests\Traits\ClientDataTrait;
use App\Tests\Traits\UserDataTrait;
use Faker\Factory;

/**
* @covers \App\Controller\UserManagement\UserController
* @group application
* php vendor/bin/phpunit tests/Controller/UserManagement/UserControllerTest.php
*/
class UserControllerTest extends AbstractControllerTest
{
    use UserDataTrait;
    use ClientDataTrait;

    private $company;
    private $euqppv;
    private $deputy_euqppv;
    private $contact_pv_client;    
    private $gender;    
    private $workRole_euqppv;    
    private $workRole_deputy_euqppv;    
    private $workRole_contact_pv_client;    
    private $um;       
    private $psmf_client;       
    private $presta;
    private $admin;

    /**
     * test
     */   	
	public function indexTest()
    {
        $this->client->request('GET', $this->router->generate('admin_users_index', ['_locale' => self::DEFAULT_LANGUAGE])); 
        $content = $this->client->getResponse()->getContent();

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h4.page-title', $this->translator->trans('user.list', [], 'messages', self::DEFAULT_LANGUAGE));    

        self::assertStringContainsString($this->euqppv->getUsername(), $content, 'Could not find euqppv');
        self::assertStringContainsString($this->deputy_euqppv->getUsername(), $content, 'Could not find deputy_euqppv');	
        self::assertStringContainsString($this->contact_pv_client->getUsername(), $content, 'Could not find contact_pv_client');
	
    }	    

    /**
     * @test
     */ 
    public function newUserTest() 
    {
        $crawler = $this->client->request('GET', $this->router->generate('admin_user_new', ['_locale' => self::DEFAULT_LANGUAGE])); 
        self::assertResponseIsSuccessful();

        // 1. vérifier si la page fonctionne    
        self::assertSelectorTextContains('h4.page-title', $this->translator->trans('user.create', [], 'messages', self::DEFAULT_LANGUAGE));   

        // 2. vérifier si la creation d'un compte User fonctionne 
        $faker = Factory::create();
        $firstName = $faker->firstName();
        $lastName = $faker->lastName();
        $username = $faker->username();
        $email = $faker->email();

        $form = $crawler->selectButton($this->translator->trans('action.save', [], 'messages', self::DEFAULT_LANGUAGE))->form();
        $prefix = $this->getFirstPrefixForm($form);        
        $form->setValues([
            $prefix.'[firstName]' => $firstName, 
            $prefix.'[lastName]' => $lastName,
            $prefix.'[username]' => $username,
            $prefix.'[email]' => $email
        ]); 
        // selects an option or a radio
        $form[$prefix.'[clients]']->select([$this->company->getId()]);
        // ticks a checkbox
        $form[$prefix.'[roles][0]']->tick();

        $this->client->submit($form);

        $content = $this->client->getResponse()->getContent();

        self::assertStringContainsString($this->translator->trans('user.flash.created', [], 'messages', self::DEFAULT_LANGUAGE), $content, 'Could not find flash success message');

        self::assertSelectorTextContains('h4.page-title', $this->translator->trans('action.show', [], 'messages', self::DEFAULT_LANGUAGE));

        // check bdd
        $user = $this->em->getRepository(User::class)->findOneBy(['email'=>$email]);
        self::assertSame($firstName, $user->getFirstName());  
        self::assertSame($lastName, $user->getLastName()); 
        self::assertSame($username, $user->getUsername()); 
        self::assertTrue(in_array('ROLE_UTILISATEUR', $user->getRoles()));
        self::assertFalse(in_array('ROLE_CONSULTANT', $user->getRoles()));
        self::assertFalse(in_array('ROLE_SUPER_CONSULTANT', $user->getRoles()));
        self::assertFalse(in_array('ROLE_ADMIN', $user->getRoles()));
        self::assertFalse($user->getMailAlerte());
        self::assertFalse($user->getPvUser());             
        
        //$profile = $this->client->getProfile();
        if ($profile = $this->client->getProfile()) {
            $mailCollector = $profile->getCollector('swiftmailer');

            // checks that an email was sent
            self::assertSame(1, $mailCollector->getMessageCount());

            $collectedMessages = $mailCollector->getMessages();
            $message = $collectedMessages[0];

            // Asserting email data
            self::assertInstanceOf('Swift_Message', $message);
            self::assertSame($this->translator->trans('email.new_user'), $message->getSubject());
            self::assertSame($this->client->getContainer()->get('parameter_bag')->get('admin_email'), key($message->getFrom()));
            self::assertSame($user->getEmail(), key($message->getTo()));
            self::assertStringContainsString(
                'Your account has been created in our PSMF platform',
                $message->getBody()
            );       
        } 
    }

    /**
     * test
     */
    public function newPvUserTest() 
    {
        $crawler = $this->client->request('GET', $this->router->generate('admin_user_new', ['_locale' => self::DEFAULT_LANGUAGE])); 
     
        // 3. vérifier si la creation d'un compte PV User fonctionne 
        $faker = Factory::create();
        $firstName = $faker->firstName();
        $lastName = $faker->lastName();
        $username = $faker->username();
        $email = $faker->email();
        $workFunction = $faker->jobTitle();
        $mobile = $faker->phoneNumber();
        $fixe = $faker->phoneNumber();
        $fax = $faker->phoneNumber();
        $adresse = $faker->address();
        $workName = $faker->company();

        $form = $crawler->selectButton($this->translator->trans('action.save', [], 'messages', self::DEFAULT_LANGUAGE))->form();
        $prefix = $this->getFirstPrefixForm($form);        
        $form->setValues([
            $prefix.'[firstName]' => $firstName, 
            $prefix.'[lastName]' => $lastName,
            $prefix.'[username]' => $username,
            $prefix.'[email]' => $email,
            $prefix.'[workFunction]' => $workFunction,
            $prefix.'[mobile]' => $mobile,
            $prefix.'[fixe]' => $fixe,
            $prefix.'[fax]' => $fax,
            $prefix.'[adresse]' => $adresse,
            $prefix.'[workName]' => $workName,
        ]); 
        $form[$prefix.'[roles][0]']->tick();
        $form[$prefix.'[pvUser]']->tick();
        $form[$prefix.'[cv][file]']->upload($this->client->getContainer()->get('kernel')->getProjectDir().'/tests/Fixtures/image.jpg');
        // selects an option or a radio
        $form[$prefix.'[gender]']->select([$this->gender->getId()]);
        $form[$prefix.'[workRoles]']->select([$this->workRole_euqppv->getId(), $this->workRole_deputy_euqppv->getId(), $this->workRole_contact_pv_client->getId()]);
        $form[$prefix.'[workAttachment]']->select([$this->um->getId()]);

        $this->client->submit($form);
        $crawler = $this->client->followRedirect();
        $content = $this->client->getResponse()->getContent();
        self::assertStringContainsString($this->translator->trans('user.flash.created', [], 'messages', self::DEFAULT_LANGUAGE), $content, 'Could not find flash success message');

        self::assertSelectorTextContains('h4.page-title', $this->translator->trans('action.show', [], 'messages', self::DEFAULT_LANGUAGE));

        self::assertStringContainsString($firstName, $content);
        self::assertStringContainsString($lastName, $content);
        self::assertStringContainsString($email, $content);
        self::assertStringContainsString($this->company->getName(), $content);
        self::assertStringContainsString($lastName, $content); 
        self::assertStringContainsString('ROLE_UTILISATEUR', $content);    
        self::assertStringNotContainsString('ROLE_CONSULTANT', $content); 
        self::assertStringNotContainsString('ROLE_SUPER_CONSULTANT', $content); 
        self::assertStringNotContainsString('ROLE_ADMIN', $content); 
        self::assertStringContainsString($workFunction, $content); 
        self::assertStringContainsString($mobile, $content); 
        self::assertStringContainsString($fixe, $content); 
        self::assertStringContainsString($fax, $content); 
        self::assertStringContainsString($adresse, $content); 
        self::assertStringContainsString($workName, $content); 
        self::assertStringContainsString($this->gender->getTitle(), $content);
        self::assertStringContainsString($this->um->getTitle(), $content);
        self::assertStringContainsString($this->workRole_euqppv->getTitle(), $content);
        self::assertStringContainsString($this->workRole_deputy_euqppv->getTitle(), $content);
        self::assertStringContainsString($this->workRole_contact_pv_client->getTitle(), $content);

        // check bdd
        $user = $this->em->getRepository(User::class)->findOneBy(['email'=>$email]);
        self::assertSame($firstName, $user->getFirstName());  
        self::assertSame($lastName, $user->getLastName()); 
        self::assertSame($username, $user->getUsername()); 
        self::assertTrue(in_array('ROLE_UTILISATEUR', $user->getRoles()));
        self::assertFalse(in_array('ROLE_CONSULTANT', $user->getRoles()));
        self::assertFalse(in_array('ROLE_SUPER_CONSULTANT', $user->getRoles()));
        self::assertFalse(in_array('ROLE_ADMIN', $user->getRoles())); 
        self::assertFalse($user->getMailAlerte());
        self::assertTrue($user->getPvUser());
        self::assertSame($workFunction, $user->getWorkFunction()); 
        self::assertSame($mobile, $user->getMobile()); 
        self::assertSame($fixe, $user->getFixe()); 
        self::assertSame($fax, $user->getFax()); 
        self::assertSame($adresse, $user->getAdresse()); 
        self::assertSame($this->gender, $user->getGender()); 
        self::assertSame($this->um, $user->getWorkAttachment()); 
        self::assertCount(3, \count($user->getWorkRoles())); 

        // removeUploadedImage
        $cv = $crawler->selectImage('cv')->image();
        $this->removeUploadedImage(basename($cv->getUri()));    
    } 
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->init();
    }
    
    protected function init()
    {
        $this->admin = $this->em->getRepository(User::class)->findOneBy(['username'=>UserFixtures::ADMIN]);
        $this->gender = $this->em->getRepository(Gender::class)->findOneBy(['code'=>'GF']);
        $this->workRole_euqppv = $this->em->getRepository(WorkRole::class)->findOneBy(['code'=>'PVEU']);
        $this->workRole_deputy_euqppv = $this->em->getRepository(WorkRole::class)->findOneBy(['code'=>'PVDEP']);
        $this->workRole_contact_pv_client = $this->em->getRepository(WorkRole::class)->findOneBy(['code'=>'PVCC']);
        $this->um = $this->em->getRepository(EntitType::class)->findOneBy(['code'=>'um']);
        $this->psmf_client = $this->em->getRepository(EntitType::class)->findOneBy(['code'=>'client']);
        $this->presta = $this->em->getRepository(EntitType::class)->findOneBy(['code'=>'presta']);
  
        $this->company = $this->loadClientData($this->admin);
        [$this->euqppv, $this->deputy_euqppv, $this->contact_pv_client] = $this->loadUserData($this->admin, $this->gender, $this->workRole_euqppv, $this->workRole_deputy_euqppv, $this->workRole_contact_pv_client, $this->company, $this->um);

        $this->addUserCvImage('image.jpg');          
    }

    protected function tearDown(): void
    {        
        $this->removeUploadedImage('image.jpg');  
        $this->kill(); 

        $this->company = null;
        $this->euqppv = null;
        $this->deputy_euqppv = null;
        $this->contact_pv_client = null;
        $this->gender = null;
        $this->workRole_euqppv = null;
        $this->workRole_deputy_euqppv = null;
        $this->workRole_contact_pv_client = null;
        $this->um = null;
        $this->psmf_client = null;
        $this->presta = null;
        $this->admin = null;

        parent::tearDown();
    }    
}