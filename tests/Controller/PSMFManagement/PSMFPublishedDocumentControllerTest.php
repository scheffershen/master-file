<?php

namespace App\Tests\Controller\PSMFManagement;

use App\Tests\Controller\AbstractControllerTest;

/**
* @covers \App\Controller\PSMFManagement\PSMFPublishedDocumentController
* @group application
* php vendor/bin/phpunit tests/Controller/PSMFManagement/PSMFPublishedDocumentController.php
*/
class PSMFPublishedDocumentControllerTest extends AbstractControllerTest
{
    /**
     * @test
     */     
    public function indexTest()
    {
        $this->client->request('GET', $this->router->generate('admin_published_document_index', ['_locale' => self::DEFAULT_LANGUAGE])); 
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h4.page-title', $this->translator->trans('documents', [], 'messages', self::DEFAULT_LANGUAGE));      
    }
  
    // public function newTest() {}
    // public function downloadTest()
    // public function localeTest()
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