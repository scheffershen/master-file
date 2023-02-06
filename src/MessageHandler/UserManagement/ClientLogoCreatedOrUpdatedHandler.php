<?php

namespace App\MessageHandler\UserManagement;

use App\Message\UserManagement\ClientLogoCreatedOrUpdated;
use Gregwar\Image\Image;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\HttpKernel\KernelInterface;

class ClientLogoCreatedOrUpdatedHandler implements MessageHandlerInterface
{    
    private $kernel;
    private $parameter;

    public function __construct(KernelInterface $kernel, ParameterBagInterface $parameter)  
    {
        $this->kernel = $kernel;
        $this->parameter = $parameter;        
    }

    public function __invoke(ClientLogoCreatedOrUpdated $clientLogoCreatedOrUpdated)
    {
        $client = $clientLogoCreatedOrUpdated->getClient();

        $filesystem = new Filesystem();
        
        if (!empty($client->getLogoUri()) && $filesystem->exists($this->kernel->getProjectDir() .'/data/'.$client->getLogoUri())) {
            // Small
            Image::open($this->kernel->getProjectDir() .'/data/'.$client->getLogoUri())
                ->zoomCrop($this->parameter->get('client_small_logo_width'), $this->parameter->get('client_small_logo_height'), 'transparent', 'center', 'center')
                ->save($this->kernel->getProjectDir() .'/data/small/'.$client->getLogoUri());

            // Medium
            Image::open($this->kernel->getProjectDir() .'/data/'.$client->getLogoUri())
                ->zoomCrop($this->parameter->get('client_medium_logo_width'), $this->parameter->get('client_medium_logo_height'), 'transparent', 'center', 'center')
                ->save($this->kernel->getProjectDir() .'/data/medium/'.$client->getLogoUri());

            // Large
            Image::open($this->kernel->getProjectDir() .'/data/'.$client->getLogoUri())
                ->cropResize($this->parameter->get('client_large_logo_width'), $this->parameter->get('client_large_logo_height'), 'transparent')
                ->save($this->kernel->getProjectDir() .'/data/large/'.$client->getLogoUri());  
        }
       
    }
}
