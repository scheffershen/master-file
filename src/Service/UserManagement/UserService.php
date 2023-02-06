<?php

declare(strict_types=1);

namespace App\Service\UserManagement;

use App\Entity\UserManagement\User;
use App\Message\UserManagement\SendUserCreated;
use App\Service\AbstractService;
use App\Utils\PasswordGeneratorInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\DBAL\Exception as DBALException;
use Psr\Container\ContainerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserService extends AbstractService
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;

    /**
     * @var PasswordGenerator
     */
    private $generator;

    /**
     * @var MessageBusInterface
     */
    protected $messageBus;

    protected $translator;

    public function __construct(
        EntityManagerInterface $entityManager,
        ContainerInterface $container,
        PasswordGeneratorInterface $generator,
        UserPasswordEncoderInterface $passwordEncoder,
        MessageBusInterface $messageBus,
        TranslatorInterface $translator
    ) {
        parent::__construct($container);
        $this->em = $entityManager;
        $this->generator = $generator;
        $this->passwordEncoder = $passwordEncoder;
        $this->messageBus = $messageBus;
        $this->translator = $translator;
    }

    public function create(User $user): bool
    {
        $password = $this->generator->generateStrongPassword();
        $user->setPassword($this->passwordEncoder->encodePassword($user, $password));
        $user->setChangePassword(true);
        $result = $this->save($user, 'create');
        if ($result) {
            //$this->clearCache('users_count' );
            $this->messageBus->dispatch(new SendUserCreated($user, $password));
        }

        return $result;
    }


    public function update(User $user): bool
    {
        return $this->save($user, 'update');
    }

    public function remove(User $user): bool
    {
        $user->setIsDeleted(true);
        $user->setIsEnable(false);
        $result = $this->save($user, 'remove');
        return $result;
    }

    private function save(User $user, string $action): bool
    {
        try {
            $this->em->persist($user);
            $this->em->flush();
            switch ($action) {
                case 'create':
                    $this->addFlash('success', $this->translator->trans('user.flash.created'));
                    break;
                case 'update':
                    $this->addFlash('success', $this->translator->trans('user.flash.updated'));
                    break;
                case 'remove':
                    $this->addFlash('success', $this->translator->trans('user.flash.deleted'));
                    break;
            }

            return true;
        } catch (DBALException $exception) {
            $this->addFlash('error', $exception->getMessage());
            throw new \RuntimeException($exception->getMessage());
        } catch (\Exception $exception) {
            $this->addFlash('error', $exception->getMessage());
            throw new \RuntimeException($exception->getMessage());
        }

        return false;
    }
}
