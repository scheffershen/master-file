<?php

namespace App\Tests\Controller\TemplateManagement;

use App\Entity\TemplateManagement\Section;
use App\Tests\Controller\AbstractControllerTest;

/**
* @covers \App\Controller\TemplateManagement\SectionController
* @group application
* php vendor/bin/phpunit tests/Controller/TemplateManagement/SectionControllerTest.php
*/
class SectionControllerTest extends AbstractControllerTest
{
    /**
     * @test
     */   	
	public function indexTest() 
	{
        $this->client->request('GET', $this->router->generate('admin_section_index', ['_locale' => self::DEFAULT_LANGUAGE])); 
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h4.page-title', $this->translator->trans('templates', [], 'messages', self::DEFAULT_LANGUAGE)); 	 
	}

    /**
     * @test
     */   	
	public function newTest() 
	{
        $this->client->request('GET', $this->router->generate('admin_section_new', ['_locale' => self::DEFAULT_LANGUAGE])); 
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h4.page-title', $this->translator->trans('section.create', [], 'messages', self::DEFAULT_LANGUAGE)); 	 
	}

    /**
     * @test
     */   	
	public function showTest() 
	{
		$sections = $this->em->getRepository(Section::class)->findAll();

		if (count($sections) > 0) {
			$section = end($sections); 
	        $crawler = $this->client->request('GET', $this->router->generate('admin_section_show', ['id'=>$section->getId(), '_locale' => self::DEFAULT_LANGUAGE])); 
	        self::assertResponseIsSuccessful();
	        self::assertSelectorTextContains('a.btn',$this->translator->trans('action.back', [], 'messages', self::DEFAULT_LANGUAGE));

	        $expected = trim(preg_replace('/\s\s+/', ' ', $section->getTitle()));
	        $actual = trim(preg_replace('/\s\s+/', ' ', $crawler->filter('h4.page-title')->text()));
	        self::assertStringContainsString($expected, $actual); 	        
		}
 
	}
 	
    /**
     * @test
     */   	
	public function editTest() 
	{
		$sections = $this->em->getRepository(Section::class)->findBy(['editable'=>true, 'contenuEditable'=>true, 'isValid'=>true]);

		if (count($sections) > 0) {
			$section = end($sections); 
	        $crawler = $this->client->request('GET', $this->router->generate('admin_section_edit', ['id'=>$section->getId(), '_locale' => self::DEFAULT_LANGUAGE])); 
	        self::assertResponseIsSuccessful();	 	        
        	self::assertSelectorTextContains('h4.page-title', $this->translator->trans('section.update', [], 'messages', self::DEFAULT_LANGUAGE));
        	
        	$form = $crawler->filter('form[name=section_edit]')->form();     
        	$prefix = $this->getFirstPrefixForm($form);
        	self::assertSame("section_edit", $prefix);   
		} else {
			self::expectNotToPerformAssertions();
		}
 
	}  

    // sidebarTest()
    // disableTest()
    // deleteTest()
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