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

class QualiosDocsCommand extends Command
{
    protected static $defaultName = 'psmf:qualios-docs';
    
    private $twig; 
    private $mailer;
    private $parameter;
    private $translator;

    /*
    * récuperer les docs de qualios
    * $ php /var/www/psmf/bin/console psmf:qualios-docs > /var/www/psmf/data/qualios.xml
    */
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
                $this->mailer->sendMail([$this->parameter->get('admin_email')], $this->parameter->get('admin_email'), $this->translator->trans('qualio.curl_error'), $error);
                return Command::SUCCESS;
            }

            $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $result['header'] = substr($response, 0, $header_size);
            $result['body'] = substr($response, $header_size);
            $result['http_code'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $result['last_url'] = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);

            if ( $result['http_code'] === 200) {
                curl_close($ch);

                $ch2 = curl_init();
                curl_setopt($ch2, CURLOPT_URL, $this->parameter->get('qualios_docs_url').'?token='.$result['body']);
                $response2 = curl_exec($ch2);
                $error2 = curl_error($ch2);
                $result2 = array(
                    'header' => '',
                    'body' => '',
                    'curl_error' => '',
                    'http_code' => '',
                    'last_url' => ''
                );
                if ($error2 != "") {
                    $result2['curl_error'] = $error2;
                    return Command::SUCCESS;
                }
                $header_size2 = curl_getinfo($ch2, CURLINFO_HEADER_SIZE);
                $result2['header'] = substr($response2, 0, $header_size2);
                $result2['body'] = substr($response2, $header_size2);
                $result2['http_code'] = curl_getinfo($ch2, CURLINFO_HTTP_CODE);
                $result2['last_url'] = curl_getinfo($ch2, CURLINFO_EFFECTIVE_URL);

                if ( $result2['http_code'] != 200) {
                    // send a mail to admin
                    $this->mailer->sendMail([$this->parameter->get('admin_email')], $this->parameter->get('admin_email'), $this->translator->trans('qualio.curl_error'). ' - ' .$result['http_code'], $result['body']);
                }                
            }

        } catch (\Throwable $e) {
            $this->mailer->sendMail([$this->parameter->get('admin_email')], $this->parameter->get('admin_email'), $this->translator->trans('qualio.curl_error'). ' - ' .$result['http_code'], $result['body']);
        } finally {
            curl_close($ch2);
        }

        return Command::SUCCESS;
    }
}
