<?php

namespace App\Tests\Controller\FrontManagement;

use App\Tests\Controller\AbstractControllerTest;
use App\DataFixtures\UserFixtures;

/**
* @covers \App\Controller\FrontManagement\UserProfileController
* @group application
* php vendor/bin/phpunit tests/Controller/FrontManagement/UserProfileControllerTest.php
*/
class UserProfileControllerTest extends AbstractControllerTest
{
    /**
     * @test
     */    
	public function editTest()
    {
        $this->client->request('GET', $this->router->generate('user_profile', ['username'=>UserFixtures::ADMIN, '_locale' => self::DEFAULT_LANGUAGE])); 
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h4.page-title', $this->translator->trans('profile', [], 'messages', self::DEFAULT_LANGUAGE));
    }

    /**
     * @test
     */    
    public function changePasswordTest()
    {
        $crawler = $this->client->request('GET', $this->router->generate('user_change_password', ['username'=>UserFixtures::ADMIN, '_locale' => self::DEFAULT_LANGUAGE])); 
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h4.page-title', $this->translator->trans('title.set_new_pass', [], 'messages', self::DEFAULT_LANGUAGE));

        $form = $crawler->selectButton($this->translator->trans('action.save', [], 'messages', self::DEFAULT_LANGUAGE))->form([
            'password[password]' => UserFixtures::ADMIN_PASSWORD,
            'password[password_confirmation]' => UserFixtures::ADMIN_PASSWORD,
        ]);
        $this->client->submit($form);

        $content = $this->client->getResponse()->getContent();
        self::assertStringContainsString($this->translator->trans('action.sign_in', [], 'messages', self::DEFAULT_LANGUAGE), $content, 'Could not find Sign in button');           
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
