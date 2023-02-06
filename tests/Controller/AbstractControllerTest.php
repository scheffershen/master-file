<?php

namespace App\Tests\Controller;

use App\DataFixtures\UserFixtures;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Form;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractControllerTest extends WebTestCase
{
    public const DEFAULT_LANGUAGE = 'en';  

    protected $client;
    protected $em;
    protected $router;
    protected $translator;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->client->followRedirects(true);
        $this->em =  $this->client->getContainer()->get('doctrine')->getManager();
        $this->translator = $this->client->getContainer()->get('translator');
        $this->router = $this->client->getContainer()->get('router');

        $this->logInAs(UserFixtures::ADMIN, UserFixtures::ADMIN_PASSWORD);        
    }


    public function logInAs(string $username, string $password)
    {
        $crawler = $this->client->request('GET', $this->createUrl('login'));
        $form = $crawler->filter('button[type=submit]')->form();
        $data = [
            'email' => $username,
            'password' => $password,
        ];

        $this->client->submit($form, $data);
    }

    protected function createUrl(string $url): string
    {
        return '/' . self::DEFAULT_LANGUAGE . '/' . ltrim($url, '/');
    } 

    protected function assertResponseStatusCode(int $statusCode, Response $response, string $message = ''): void
    {
        $this->assertSame($statusCode, $response->getStatusCode(), $message);
    }

    protected function assertExcelExportResponse(string $prefix): void
    {
        /** @var BinaryFileResponse $response */
        $response = $this->client->getResponse();
        self::assertInstanceOf(BinaryFileResponse::class, $response);

        self::assertEquals('application/force-download;charset=UTF-8', $response->headers->get('Content-Type'));
        self::assertStringContainsString('attachment; filename=' . $prefix, $response->headers->get('Content-Disposition'));
        self::assertStringContainsString('.xls', $response->headers->get('Content-Disposition'));
    }

    protected function assertIsRedirect($url = null, $endsWith = true): void
    {
        self::assertTrue($this->client->getResponse()->isRedirect(), 'Response is not a redirect');
        if (null === $url) {
            return;
        }

        self::assertTrue($this->client->getResponse()->headers->has('Location'), 'Could not find "Location" header');
        if ($endsWith) {
            self::assertStringEndsWith(
                $url,
                $this->client->getResponse()->headers->get('Location'),
                'Redirect URL does not match'
            );
        } else {
            self::assertStringContainsString(
                $url,
                $this->client->getResponse()->headers->get('Location'),
                'Redirect URL does not match'
            );
        }
    }

    protected function getFirstPrefixForm(Form $form): ?string
    {
        foreach ($form->all() as $field) {
            preg_match('/^(.*)\[.*\]$/', $field->getName(), $matches);
            if ($matches) {
                return $matches[1];
            }
        }

        return null;
    }
    
    protected function addUserCvImage(string $cv): void
    {
        $path = $this->client->getContainer()->get('kernel')->getProjectDir();
        $filesystem = new Filesystem();
        if ($filesystem->exists($path.'/tests/Fixtures/'.$cv)) {
            $filesystem->copy($path.'/tests/Fixtures/'.$cv, $path.'/data/'.$cv);
            $filesystem->copy($path.'/tests/Fixtures/'.$cv, $path.'/data/large/'.$cv);
            $filesystem->copy($path.'/tests/Fixtures/'.$cv, $path.'/data/medium/'.$cv);
            $filesystem->copy($path.'/tests/Fixtures/'.$cv, $path.'/data/small/'.$cv);
        } 
    }

    protected function removeUploadedImage(string $logo): void
    {
        $path = $this->client->getContainer()->get('kernel')->getProjectDir();

        $filesystem = new Filesystem();
        if ($filesystem->exists($path.'/data/'.$logo)) {
            unlink($path.'/data/'.$logo); 
        }          
        if ($filesystem->exists($path.'/data/large/'.$logo)) {
            unlink($path.'/data/large/'.$logo);
        }  
        if ($filesystem->exists($path.'/data/medium/'.$logo)) {
            unlink($path.'/data/medium/'.$logo);
        }  
        if ($filesystem->exists($path.'/data/small/'.$logo)) {
            unlink($path.'/data/small/'.$logo);
        } 
    }

    protected function kill()
    {
        $this->client = null;
        $this->em = null;
        $this->translator = null;    
    }     

    protected function tearDown(): void
    {
        $this->kill();
        parent::tearDown();
    }    
}