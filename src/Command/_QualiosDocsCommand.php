<?php

namespace App\Command;

use App\Mailer\Mailer;
use App\Entity\QualiosManagement\Document;
use App\Entity\QualiosManagement\TypeDocument;
use Metaer\CurlWrapperBundle\CurlWrapper;
use Metaer\CurlWrapperBundle\CurlWrapperException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class _QualiosDocsCommand extends Command
{
    protected static $defaultName = 'psmf:qualios-docs-test';
    
    private $twig; 
    private $mailer;
    private $parameter;
    private $translator;

    public function __construct(Environment $twig, Mailer $mailer, TranslatorInterface $translator, ParameterBagInterface $parameter)
    {
        $this->twig = $twig;
        $this->mailer = $mailer;
        $this->parameter = $parameter;
        $this->translator = $translator;
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
        //return Command::SUCCESS;

        $ch = curl_init();

        try {
            curl_setopt($ch, CURLOPT_URL, $this->parameter->get('qualios_docs_url').'?token=7a3cd2ba-c010-4edf-9c3d-d2ee41092c5d');

            $response = curl_exec($ch);
            $error = curl_error($ch);
            $result = array(
                'header' => '',
                'body' => '',
                'curl_error' => '',
                'http_code' => '',
                'last_url' => ''
            );

            if ($error != "") {
                $result['curl_error'] = $error;
                return Command::SUCCESS;
            }

            $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $result['header'] = substr($response, 0, $header_size);
            $result['body'] = substr($response, $header_size);
            $result['http_code'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $result['last_url'] = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);

            if ( $result['http_code'] === 200) {
            } else {
            }
            
        } catch (\Throwable $e) {
        } finally {
            curl_close ($ch);
        }

        return Command::SUCCESS;
    }
}
