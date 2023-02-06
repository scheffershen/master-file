<?php

namespace App\Controller\UserManagement;

use App\Entity\LovManagement\EntitType;
use App\Entity\UserManagement\User;
use App\Form\UserManagement\UserEditType;
use App\Form\UserManagement\UserDeleteType;
use App\Form\UserManagement\UserType;
use App\Message\UserManagement\UserUpdated;
use App\Repository\UserManagement\UserRepository;
use App\Serializer\UserManagement\UserSerializer;
use App\Service\UserManagement\UserService;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\DBAL\Exception as DBALException;
use Omines\DataTablesBundle\Adapter\Doctrine\Event\ORMAdapterQueryEvent;
use Omines\DataTablesBundle\Adapter\Doctrine\ORMAdapter;
use Omines\DataTablesBundle\Adapter\Doctrine\ORMAdapterEvents;
use Omines\DataTablesBundle\Column\DateTimeColumn;
use Omines\DataTablesBundle\Column\TextColumn;
use Omines\DataTablesBundle\Column\TwigStringColumn;
use Omines\DataTablesBundle\DataTableFactory;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Twig\Environment;
use Twig\Extension\StringLoaderExtension;

/**
 * @Route("/admin/user")
 */
class UserController extends AbstractController
{
    private $kernel;
    private $messageBus;
    private $slugger;
    private $translator;
    private $userSerializer;
    private $parameter;

    public function __construct(UserSerializer $userSerializer, MessageBusInterface $messageBus, TranslatorInterface $translator, KernelInterface $kernel, SluggerInterface $slugger, ParameterBagInterface $parameter)
    {
        $this->kernel = $kernel;
        $this->messageBus = $messageBus;  
        $this->slugger = $slugger;  
        $this->translator = $translator;
        $this->userSerializer = $userSerializer;        
        $this->parameter = $parameter;
    }

    /**
     * @Route("/index", name="admin_users_index", methods={"GET"})
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('UserManagement/User/index.html.twig', [
             'users' => $userRepository->findBy(['isDeleted'=>false], ['firstName'=>'DESC'])
         ]);
    }

    /**
     * @Route("/download", name="admin_user_download", methods={"GET"})
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function download(UserRepository $userRepository, Environment $twig): Response
    {
        $content = $twig->render('UserManagement/User/download.html.twig', [
            'users' => $userRepository->findBy(['isDeleted'=>false], ['firstName'=>'DESC'])
        ]);

        return new Response($content, 200, [
            'Content-Type' => 'application/force-download;charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="users_' . (new \Datetime())->format('d-M-Y H:i:s') . '.xls"'
        ]);        
    }

    /**
     * @Route("/new", name="admin_user_new")
     *
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function new(Request $request, UserService $service): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
        $old = [];
        if ($form->isSubmitted() && $form->isValid()) {    
            if ( $user->getClients()->isEmpty() ) {
                $form->addError(new FormError($this->translator->trans('user.error.missing_client')));
            } elseif ( $user->getPvUser() && $user->getWorkRoles()->isEmpty() ) {
                $form->addError(new FormError($this->translator->trans('user.error.missing_workRole')));            
            } elseif ( empty($user->getRoles()) ) {
                $form->addError(new FormError($this->translator->trans('user.error.missing_role')));  
            } else {                                
                $user->setCreateUser($this->getUser());
                $user->setUpdateUser($this->getUser());    

                try {
                    $service->create($user);

                    //$this->messageBus->dispatch(new UserUpdated('add', $old, $user));
                    
                    return $this->redirectToRoute('admin_user_show', [
                        'id' => $user->getId()
                    ]);
                } catch (\Throwable $exception) {
                    $this->addFlash('error', $exception->getMessage());
                }               
            }
        }

        return $this->render('UserManagement/User/new.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/show/{id}", name="admin_user_show", methods={"GET"})
     */
    public function show(Request $request, User $user): Response
    {
        $form = $this->createForm(UserDeleteType::class, $user);

        return $this->render('UserManagement/User/show.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
            'action' => $request->get('action'), 
        ]);
    }

    /**
     * Displays a form to edit an existing User entity.
     *
     * @Route("/edit/{id}",methods={"GET", "POST"}, name="admin_user_edit")
     *
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function edit(Request $request, User $user, UserService $service): Response
    {
        $old = $this->userSerializer->serialize($user);

        $form = $this->createForm(UserEditType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ( $user->getClients()->isEmpty() ) {
                $form->addError(new FormError($this->translator->trans('user.error.missing_client')));
            } elseif ( $user->getPvUser() && $user->getWorkRoles()->isEmpty() ) {
                $form->addError(new FormError($this->translator->trans('user.error.missing_workRole')));                
            } elseif ( empty($user->getRoles()) ) {
                $form->addError(new FormError($this->translator->trans('user.error.missing_role')));  
            } else {                                                        
                $uploadFile = $form->get('cv')->getData();
                if ($uploadFile) {
                    $originalFilename = pathinfo($uploadFile->getClientOriginalName(), PATHINFO_FILENAME);

                    $safeFilename = $this->slugger->slug($originalFilename);
                    $newFilename = $safeFilename.'-'.uniqid().'.'.$uploadFile->guessExtension();

                    try {
                        $uploadFile->move($this->kernel->getProjectDir() .'/data/', $newFilename);
                    } catch (FileException $e) {
                        $this->addFlash('error', $e->getMessage());
                    }
                    $user->setCvUri($newFilename);
                }     
                
                $user->setUpdateUser($this->getUser());

                try {
                    $service->update($user);

                    //$this->messageBus->dispatch(new UserUpdated('edit', $old, $user));
                    
                    return $this->redirectToRoute('admin_user_show', [
                        'id' => $user->getId()
                    ]);
                } catch (\Throwable $exception) {
                    $this->addFlash('error', $exception->getMessage());
                }                 
            }
        }

        return $this->render('UserManagement/User/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Desactive/Active un user
     * @Route("/disable/{id}", name="admin_user_disable", methods={"POST"})
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function disable(Request $request, User $user)
    {
        $old = $this->userSerializer->serialize($user);

        $form = $this->createForm(UserDeleteType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {        
            $em = $this->getDoctrine()->getManager();
            try {
                $user->setUpdateUser($this->getUser());
                $user->setUpdateDate(new \Datetime());
                if ($user->getIsEnable()) {            
                    $user->setIsEnable(false);
                } else {
                    $user->setIsEnable(true);
                }
                $em->persist($user);
                $em->flush();
                if ($user->getIsEnable()) {
                    //$this->messageBus->dispatch(new UserUpdated('enable', $old, $user));
                    $this->addFlash('success', $this->translator->trans('user.flash.enable'));
                } else {
                    //$this->messageBus->dispatch(new UserUpdated('disable', $old, $user));
                    $this->addFlash('success', $this->translator->trans('user.flash.disable'));
                }  
                return $this->redirectToRoute('admin_users_index');

            } catch (DBALException $exception) {
                $this->addFlash('error', $exception->getMessage());
            }
        }

        if ($user->getIsEnable()) {
            return $this->redirectToRoute('admin_user_show', ['id'=>$user->getId(), 'action'=>'disable']);
        } else {
            return $this->redirectToRoute('admin_user_show', ['id'=>$user->getId(), 'action'=>'enable']);
        } 
    }

    /**
     * Deletes an User entity.
     *
     * @Route("/delete/{id}", methods={"POST"}, name="admin_user_delete")
     *
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function delete(Request $request, User $user): Response
    {
        $old = $this->userSerializer->serialize($user);

        $form = $this->createForm(UserDeleteType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            try {
                $user->setUpdateUser($this->getUser()); 
                $user->setUpdateDate(new \Datetime());       
                $user->setIsEnable(false);
                $user->setIsDeleted(true);                           
                $em->persist($user);
                $em->flush();

                //$this->messageBus->dispatch(new UserUpdated('delete', $old, $user));
                $this->addFlash('success', $this->translator->trans('user.flash.deleted'));
                return $this->redirectToRoute('admin_users_index');

            } catch (\Doctrine\DBAL\DBALException $exception) {
                $this->addFlash('error', $exception->getMessage());
            }
        }

        return $this->redirectToRoute('admin_user_show', ['id'=>$user->getId(), 'action'=>'delete']);
    }

    /**
     * @Route("/omines", name="admin_users_omines", methods={"GET", "POST"})
     * datatables Omines, useful, when your database are explosed
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function omines(Request $request, DataTableFactory $dataTableFactory, Environment $twig): Response
    {
        if (!$twig->hasExtension(StringLoaderExtension::class)) {
            $twig->addExtension(new \Twig\Extension\StringLoaderExtension());
        }

        $table = $dataTableFactory->create()
            ->add('actions', TwigStringColumn::class, [
                'template' => '<div class="btn-group" role="group"><a href="{{ url(\'admin_user_edit\', {id: row.id}) }}" class="btn btn-sm btn-outline-primary" data-toggle="tooltip" data-original-title="{{ \'action.edit\' | trans }}"><i class="fas fa-pencil-alt"></i></a>&nbsp;<a href="{{ url(\'admin_user_show\', {id: row.id, \'delete\':1}) }}" class="btn btn-sm btn-danger" data-toggle="tooltip" data-original-title="{{ \'action.delete\' | trans }}"><i class="fas fa-trash"></i></a>&nbsp;<a href="{{ url(\'admin_audit_show_entity_history\', {\'entity\': \'App-Entity-UserManagement-User\', id: row.id}) }}" class="btn btn-sm btn-outline-primary" data-toggle="tooltip" data-original-title="{{ \'action.audit\' | trans }}"><i class="fas fa-code-branch"></i></a></div>',
            ])
            ->add('fullName', TextColumn::class)
            ->add('firstName', TextColumn::class)
            ->add('lastName', TextColumn::class)
            ->add('email', TextColumn::class);

        $table->createAdapter(ORMAdapter::class, [
                'entity' => User::class,
                'hydrate' => Query::HYDRATE_ARRAY,
                'query' => function (QueryBuilder $builder) {
                    $builder
                        ->select('u')
                        //->addSelect('s')
                        ->from(User::class, 'u')
                        //->leftJoin('u.gender', 's')
                        //->where('u.roles LIKE :role')
                        //->andWhere('u.isDeleted = false')
                        //->setParameter('role', '%"' . $role . '"%')
                        ->orderBy('u.updateDate', 'DESC');
                },
            ])
            ->handleRequest($request);

        $table->addEventListener(ORMAdapterEvents::PRE_QUERY, function (ORMAdapterQueryEvent $event) {
            $event->getQuery()->useResultCache(true)->useQueryCache(true);
        });

        if ($table->isCallback()) {
            return $table->getResponse();
        }

        return $this->render('UserManagement/User/index.html.twig', ['datatable' => $table]);
    }    
}
