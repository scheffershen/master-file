<?php

namespace App\Tests\Controller\AdminManagement;

use App\Entity\UserManagement\LoggedMessage;
use App\Tests\Controller\AbstractControllerTest;
use App\DataFixtures\UserFixtures;
use App\Tests\Traits\LoggedMessageDataTrait;
/**
* @covers \App\Controller\AdminManagement\TrackingController
* @group application
* php vendor/bin/phpunit tests/Controller/AdminManagement/TrackingControllerTest.php
*/
class TrackingControllerTest extends AbstractControllerTest
{
    use LoggedMessageDataTrait;

    private $logged_message;

    /**
     * @test
     */   	
	public function connexionTest()
    {
        $this->client->request('GET', $this->router->generate('admin_tracking_connexion', ['_locale' => self::DEFAULT_LANGUAGE])); 
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h4.page-title', $this->translator->trans('tracking.login', [], 'messages', self::DEFAULT_LANGUAGE)); 
        
    }

    /**
     * @test
     */   	
	public function connexionDownloadTest()
    {
        ob_start();
        $this->client->request('GET', $this->router->generate('admin_tracking_connexion_download', ['_locale' => self::DEFAULT_LANGUAGE])); 
        ob_end_clean();
        $response = $this->client->getResponse();
        self::assertEquals('application/force-download;charset=UTF-8', $response->headers->get('Content-Type'));
        self::assertStringContainsString('attachment; filename="export_connexion_', $response->headers->get('Content-Disposition'));
        self::assertStringContainsString('.xls', $response->headers->get('Content-Disposition'));
    }

    /**
     * @test
     */   	
	public function mailLogTest()
    {
        $this->client->request('GET', $this->router->generate('admin_mail_log', ['_locale' => self::DEFAULT_LANGUAGE])); 
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h4.page-title', $this->translator->trans('tracking.mail', [], 'messages', self::DEFAULT_LANGUAGE));    	
    }    

    /**
     * @test
     */   	
	public function mailLogDownloadTest()
    {
        ob_start();
        $this->client->request('GET', $this->router->generate('admin_mail_log_download', ['_locale' => self::DEFAULT_LANGUAGE])); 
        ob_end_clean();
        $response = $this->client->getResponse();
        self::assertEquals('application/force-download;charset=UTF-8', $response->headers->get('Content-Type'));
        self::assertStringContainsString('attachment; filename="export_mail_log_', $response->headers->get('Content-Disposition'));
        self::assertStringContainsString('.xls', $response->headers->get('Content-Disposition'));	
    }        

    /**
     * @test
     */   	
	public function mailLogMessageTest()
    {    	
		$this->client->request('GET', $this->router->generate('admin_mail_log_message', ['_locale' => self::DEFAULT_LANGUAGE, 'id'=>$this->logged_message->getId()])); 
        $response = $this->client->getResponse();
        self::assertTrue($response->isSuccessful());
        self::assertStringContainsString($this->logged_message->getBody(), $response->getContent());   
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->init();
    }
    
    protected function init(): void
    {
        $this->logged_message = $this->loadLoggedMessageData();
        
    }
    
    protected function tearDown(): void
    {                
        $this->kill(); 
        $this->logged_message = null;
        parent::tearDown();
    }       
}	