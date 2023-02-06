<?php

namespace App\Command;

use App\Repository\UserManagement\UserRepository;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserChangePasswordCommand extends Command
{
    protected static $defaultName = 'app:change-password';
    private $container;
    private $userRepository;
    private $passwordEncoder;

    public function __construct(ContainerInterface $container, UserRepository $userRepository, UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
        $this->userRepository = $userRepository;
        $this->container = $container;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->addArgument('username', InputArgument::REQUIRED, 'The username')
            ->addArgument('password', InputArgument::REQUIRED, 'The password')            
            ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $username = $input->getArgument('username');
        $password = $input->getArgument('password');

        $em = $this->container->get('doctrine')->getManager();
        $user = $this->userRepository->findOneBy(['username' => $username]);

        if (null === $user) {
            $io->note(sprintf('<error>Error</error>: user <comment>%s</comment> not found.', $username));

            return Command::FAILURE;
        }

        if ($password) {
            $user->setPassword($this->passwordEncoder->encodePassword($user, $password));
            $em->persist($user);
            $em->flush();
            $io->note(sprintf('username: %s', $username));
            $io->note(sprintf('New password: %s', $password));
            $io->success(sprintf('Changed password for user %s', $username));
        }

        return Command::SUCCESS;
    }
}
