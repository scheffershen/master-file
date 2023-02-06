<?php

namespace App\Controller\UserManagement;

use App\Entity\UserManagement\Client;
use App\Form\UserManagement\ClientEditType;
use App\Form\UserManagement\ClientDeleteType;
use App\Form\UserManagement\ClientType;
use App\Message\UserManagement\ClientLogoCreatedOrUpdated;
use App\Message\UserManagement\ClientUpdated;
use App\Repository\UserManagement\ClientRepository;
use App\Serializer\UserManagement\ClientSerializer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Twig\Environment;

/**
 * @Route("/admin/clients")
 */
class ClientController extends AbstractController
{    
    private $kernel;
    private $messageBus;
    private $slugger;
    private $translator;
    private $clientSerializer;

    public function __construct(ClientSerializer $clientSerializer, MessageBusInterface $messageBus, TranslatorInterface $translator, KernelInterface $kernel, SluggerInterface $slugger)
    {
        $this->kernel = $kernel;
        $this->messageBus = $messageBus;  
        $this->slugger = $slugger;  
        $this->translator = $translator;
        $this->clientSerializer = $clientSerializer;
    }
    /**
     * @Route("/index", name="admin_client_index", methods={"GET"})
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function index(ClientRepository $clientRepository): Response
    {
        return $this->render('UserManagement/Client/index.html.twig', [
            'clients' => $clientRepository->findBy(['isDeleted'=>false], ['updateDate'=>'DESC']),
        ]);
    }

    /**
     * @Route("/download", name="admin_client_download", methods={"GET"})
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function download(ClientRepository $clientRepository, Environment $twig): Response
    {
        $content = $twig->render('UserManagement/Client/download.html.twig', [
            'clients' => $clientRepository->findBy(['isDeleted'=>false], ['updateDate'=>'DESC']),
        ]);

        return new Response($content, 200, [
            'Content-Type' => 'application/force-download;charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="clients_' . (new \Datetime())->format('d-M-Y H:i:s') . '.xls"'
        ]);        
    }

    /**
     * @Route("/new", name="admin_client_new", methods={"GET","POST"})
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function new(Request $request): Response
    {
        $client = new Client();
        $form = $this->createForm(ClientType::class, $client);
        $form->handleRequest($request);
        $old = [];
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            try {
                $client->setCreateUser($this->getUser());
                $client->setUpdateUser($this->getUser());  
                             
                $em->persist($client);
                $em->flush();

                if (!empty($client->getLogoUri())) {
                    $this->messageBus->dispatch(new ClientLogoCreatedOrUpdated($client));          
                }
                $this->messageBus->dispatch(new ClientUpdated('add', $old, $client));

                $this->addFlash('success', $this->translator->trans('client.flash.created'));

                return $this->redirectToRoute('admin_client_index');
            } catch (DBALException $exception) {
                $this->addFlash('error', $exception->getMessage());
            } catch (\Throwable $exception) {
                $this->addFlash('error', $exception->getMessage());
            } 
        }

        return $this->render('UserManagement/Client/new.html.twig', [
            'client' => $client,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/show/{id}", name="admin_client_show", methods={"GET"})
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function show(Request $request, Client $client): Response
    {
        $form = $this->createForm(ClientDeleteType::class, $client);

        return $this->render('UserManagement/Client/show.html.twig', [
            'client' => $client,
            'form' => $form->createView(),
            'action' => $request->get('action'),              
        ]);
    }

    /**
     * @Route("/edit/{id}", name="admin_client_edit", methods={"GET","POST"})
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function edit(Request $request, Client $client): Response
    {
        $old = $this->clientSerializer->serialize($client);

        $client->setIsMajeur(false); // set isMajeur false
        $form = $this->createForm(ClientEditType::class, $client);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $client->setUpdateUser($this->getUser());
            $uploadFile = $form->get('logo')->getData();
            if ($uploadFile) {
                $originalFilename = pathinfo($uploadFile->getClientOriginalName(), PATHINFO_FILENAME);

                $safeFilename = $this->slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$uploadFile->guessExtension();

                try {
                    $uploadFile->move($this->kernel->getProjectDir() .'/data/', $newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', $e->getMessage());
                }
                $client->setLogoUri($newFilename);
            }

            try {
                $em->persist($client);
                $em->flush();

                if ($uploadFile) {
                    $this->messageBus->dispatch(new ClientLogoCreatedOrUpdated($client)); 
                }

                if ($client->getIsMajeur()) {
                    $this->messageBus->dispatch(new ClientUpdated('edit', $old, $client));
                }
                
                $this->addFlash('success', $this->translator->trans('client.flash.updated'));

                return $this->redirectToRoute('admin_client_index');
            } catch (DBALException $exception) {
                $this->addFlash('error', $exception->getMessage());
            }
        }

        return $this->render('UserManagement/Client/edit.html.twig', [
            'client' => $client,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Desactive/Active un client
     * @Route("/disable/{id}", name="admin_client_disable", methods={"POST"})
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function disable(Request $request, Client $client)
    {
        $old = $this->clientSerializer->serialize($client);
        $form = $this->createForm(ClientDeleteType::class, $client);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {  
            $em = $this->getDoctrine()->getManager();
            try {
                $client->setUpdateUser($this->getUser());
                $client->setUpdateDate(new \Datetime());  

                if ($client->getIsValid()) {
                    $client->setIsValid(false);
                } else {
                    $client->setIsValid(true);
                }

                $em->persist($client);
                $em->flush();

                if ($client->getIsValid()) {
                    $this->messageBus->dispatch(new ClientUpdated('enable', $old, $client));
                    $this->addFlash('success', $this->translator->trans('client.flash.enable'));
                } else {
                    $this->messageBus->dispatch(new ClientUpdated('disable', $old, $client));
                    $this->addFlash('success', $this->translator->trans('client.flash.disable'));
                }  
                return $this->redirectToRoute('admin_client_index');

            } catch (DBALException $exception) {
                $this->addFlash('error', $exception->getMessage());
            }
        }

        if ($client->getIsValid()) {
            return $this->redirectToRoute('admin_client_show', ['id'=>$client->getId(), 'action'=>'disable']);
        } else {
            return $this->redirectToRoute('admin_client_show', ['id'=>$client->getId(), 'action'=>'enable']);
        }   
    }

    /**
     * @Route("/delete/{id}", name="admin_client_delete", methods={"POST"})
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function delete(Request $request, Client $client): Response
    {
        $old = $this->clientSerializer->serialize($client);

        $form = $this->createForm(ClientDeleteType::class, $client);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            try {
                $client->setUpdateUser($this->getUser()); 
                $client->setUpdateDate(new \Datetime());                  
                $client->setIsValid(false);
                $client->setIsDeleted(true);            
                $em->persist($client);
                $em->flush();

                $this->messageBus->dispatch(new ClientUpdated('delete', $old, $client));
                $this->addFlash('success', $this->translator->trans('client.flash.deleted'));
                return $this->redirectToRoute('admin_client_index');
            } catch (DBALException $exception) {
                $this->addFlash('error', $exception->getMessage());
            }
            
        }

        return $this->redirectToRoute('admin_client_show', ['id'=>$client->getId(), 'action'=>'delete']);
    }
}
