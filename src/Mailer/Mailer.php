<?php

namespace App\Mailer;

use App\Entity\UserManagement\LoggedMessage;
use App\Entity\UserManagement\User;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;

final class Mailer
{
    /**
     * @var \Swift_Mailer
     */
    private $mailer;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ManagerRegistry
     */
    private $doctrine;

    /**
     * constructor.
     * @param \Swift_Mailer $mailer
     * @param LoggerInterface $logger
     */
    public function __construct(\Swift_Mailer $mailer, LoggerInterface $logger, ManagerRegistry $doctrine)
    {
        $this->mailer = $mailer;
        $this->logger = $logger;
        $this->doctrine = $doctrine;
    }

    /**
     * @param User $user
     */
    public function sendMail(array $from, string $to, string $purpose, string $body): ?bool
    {
        $message = (new \Swift_Message($purpose))
            ->setFrom($from)
            ->setTo($to)
            ->setBody($body, 'text/html')
            ->setCharset('utf-8');

        try {
            $reponse = $this->mailer->send($message);
            $this->log($message);

            return true;
        } catch (\Swift_TransportException $e) {
            $this->logger->error($e->getMessage());
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }

        return false;
    }

    /**
     * @param $message
     * @param int $result
     * @param array $failures
     */
    public function log($message, $result = 1, $failures = [])
    {
        $loggedMessage = new LoggedMessage();
        $loggedMessage->setMessage($message);
        $loggedMessage->setResult($result);
        $loggedMessage->setFailedRecipients($failures);

        $em = $this->doctrine->getManagerForClass(LoggedMessage::class);

        // application should not crash when logging fails
        try {
            $em->persist($loggedMessage);
            $em->flush($loggedMessage);
        } catch (\Exception $e) {
            $error = 'Logging sent message with \SwiftmailerLoggerBundle\Logger\EntityLogger failed: ' .
                $e->getMessage();
            $this->logger->error($error);
        }
    }
}
