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

class _QualiosTokenCommand extends Command
{
    protected static $defaultName = 'psmf:qualios-token';

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
            ->setHelp('This command allows you to send alert mail for the history of the psmf modifications');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        return Command::SUCCESS;
        $begin = new \DateTime();
        $output->writeln('<comment>Begin : ' . $begin->format('d-m-Y G:i:s') . ' ---</comment>');

        $data = [
            'Key' => $this->parameter->get('qualios_key'),
            'ExistingToken' => "",
            'Login' => $this->parameter->get('qualios_login'),
            'Password' => $this->parameter->get('qualios_password'),
            'PasswordEncrypted' => false,
        ];

        $ch = curl_init();

        try {
            $headers    = [];
            $headers[]  = 'Content-Type: application/json';
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_URL, $this->parameter->get('qualios_token_url'));
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HEADER, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

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
                $output->writeln('<comment>curl_errno : ' . $error . ' </comment>');
                return Command::SUCCESS;
            }

            $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $result['header'] = substr($response, 0, $header_size);
            $result['body'] = substr($response, $header_size);
            $result['http_code'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $result['last_url'] = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);

            // result : The server cannot service the request because the media type is unsupported
            // 5077dc28-bea6-401f-a7b8-2c8a423191ee
            $output->writeln('<comment>body : ' . $result['body'] . ' </comment>');
            $output->writeln('<comment>http_code : ' . $result['http_code'] . ' </comment>');
            //httpcode : 200,

        } catch (\Throwable $e) {
            $output->writeln('<comment>End : ' . $e->getMessage() . '</comment>');
        } finally {
            curl_close($ch);
        }

        //$output->writeln('<comment>Mails send : ' . $ . ' ---</comment>');
        $end = new \DateTime();
        $output->writeln('<comment>End : ' . $end->format('d-m-Y G:i:s') . ' ---</comment>');

        return Command::SUCCESS;
    }
}
