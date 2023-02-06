<?php

namespace App\Command;

use App\Mailer\Mailer;
use App\Repository\PSMFManagement\PSMFRepository;
use App\Repository\PSMFManagement\PSMFHistoryRepository;
use App\Repository\UserManagement\UserRepository;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class MailAlerteCommand extends Command
{
    protected static $defaultName = 'psmf:alerte-mail';
    
    private $twig; 
    private $mailer;
    private $parameter;
    private $translator;
    private $pSMFRepository;
    private $pSMFHistoryRepository;
    private $userRepository;

    public function __construct(PSMFRepository $pSMFRepository,  PSMFHistoryRepository $pSMFHistoryRepository, UserRepository $userRepository, Environment $twig, Mailer $mailer, TranslatorInterface $translator, ParameterBagInterface $parameter)
    {
        $this->twig = $twig;
        $this->mailer = $mailer;
        $this->parameter = $parameter;
        $this->translator = $translator;
        $this->pSMFRepository = $pSMFRepository;
        $this->userRepository = $userRepository;
        $this->pSMFHistoryRepository = $pSMFHistoryRepository;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Add a short description for your command')
            ->setHelp('This command allows you to send alert mail for the history of the psmf modifications')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $begin = new \DateTime();
        $output->writeln('<comment>Begin : ' . $begin->format('d-m-Y G:i:s') . ' ---</comment>');
        
        $psmfs = $this->pSMFRepository->findBy(['isDeleted'=>false], ['updateDate'=>'DESC']);
        $nb_mails = 0;
        foreach ($psmfs as $psmf) {
            $users = $this->userRepository->findAllByClientWithAlertMail($psmf->getClient());
            $pSMFVariableHistories = $this->pSMFHistoryRepository->findByVariableLastWeekModification($psmf); // last wekk

            $pSMFSectionHistories = $this->pSMFHistoryRepository->findBySectionLastWeekModification($psmf); // last wekk

            $body = $this->twig->render('PSMFManagement/PSMFHistory/emails/psmfhistory_weekly.html.twig', [
                'psmf' => $psmf,
                'pSMFVariableHistories' => $pSMFVariableHistories,
                'pSMFSectionHistories' => $pSMFSectionHistories,
            ]);

            foreach ($users as $user) {
                $this->mailer->sendMail([$this->parameter->get('admin_email')], $user->getEmail(), $this->translator->trans('psmfHistory.last_week'), $body);
                $nb_mails++;
            }
        }

        $output->writeln('<comment>Mails send : ' . $nb_mails . ' ---</comment>');
        $end = new \DateTime();
        $output->writeln('<comment>End : ' . $end->format('d-m-Y G:i:s') . ' ---</comment>');

        return Command::SUCCESS;
    }
}
