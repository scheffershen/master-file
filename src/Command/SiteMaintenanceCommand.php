<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\KernelInterface;

class SiteMaintenanceCommand extends Command
{
	protected static $defaultName = 'app:maintenance:lock';
	private $kernel;

    public function __construct(KernelInterface $kernel)
    {
        parent::__construct();
        $this->kernel = $kernel;
    }

    protected function configure()
    {
        $this
            ->setName(self::$defaultName)
            ->setDescription('Enable/Disable maintenance mode')
            ->setDefinition(array(
                new InputArgument('status', InputArgument::REQUIRED, 'The final status')
            ))
            ->setHelp(<<<EOF
Enable or Disable the Maintenance mode

    <info>php app/console app:maintenance:lock on</info>
EOF
            );
    } 

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $status = $input->getArgument("status");

        if ($status != 'on' && $status != 'off') {
            throw new \InvalidArgumentException("You have to use 'on' or 'off'");
        }

        $this->enableMaintenance(($status == 'off') ? false : true);
        return 0;
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        if (!$input->getArgument('status')) {
            $helper = $this->getHelper("question");
            $question = new ChoiceQuestion('Please choose the final status: [on/off]', ['on', 'off'], 1);
            $question->setErrorMessage('Status %s is invalid.');

            $status = $helper->ask($input, $output, $question);

            $input->setArgument('status', $status);
        }
    }  

    private function enableMaintenance($status)
    {      
    	$filesystem = new Filesystem();
        if ($status) {
            if ($filesystem->exists($this->kernel->getProjectDir().'/templates/maintenance.html')) {
                return $filesystem->copy($this->kernel->getProjectDir() .'/templates/maintenance.html', $this->kernel->getProjectDir().'/public/maintenance.html');
            }          
        } else {
            if ($filesystem->exists($this->kernel->getProjectDir().'/public/maintenance.html')) {
                return $filesystem->remove($this->kernel->getProjectDir() .'/public/maintenance.html');
            }
        }

        return false;
    }            
}