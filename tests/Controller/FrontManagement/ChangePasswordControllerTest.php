<?php

namespace App\Tests\Controller\FrontManagement;

use App\DataFixtures\UserFixtures;
use App\Tests\Controller\AbstractControllerTest;

/**
* @covers \App\Controller\FrontManagement\ChangePasswordController
* @group application
* php vendor/bin/phpunit tests/Controller/FrontManagement/ChangePasswordControllerTest.php
*/
class ChangePasswordControllerTest extends AbstractControllerTest
{
    /**
     * @test
     */    
    public function changePasswordTest()
    {
        $crawler = $this->client->request('GET', $this->router->generate('change_password', ['_locale' => self::DEFAULT_LANGUAGE])); 
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('div.card-header', $this->translator->trans('title.set_new_pass', [], 'messages', self::DEFAULT_LANGUAGE));

        $form = $crawler->selectButton($this->translator->trans('action.continue', [], 'messages', self::DEFAULT_LANGUAGE))->form([
            'password[password]' => UserFixtures::ADMIN_PASSWORD,
            'password[password_confirmation]' => UserFixtures::ADMIN_PASSWORD,
        ]);
        $this->client->submit($form);

        $content = $this->client->getResponse()->getContent();
        self::assertStringContainsString('toastr.success("message.password_has_been_reset", "Success");', $content, 'Could not find flash success message');
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
