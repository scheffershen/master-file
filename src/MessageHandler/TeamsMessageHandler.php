<?php

namespace App\MessageHandler;

use App\Message\TeamsMessage;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class TeamsMessageHandler implements MessageHandlerInterface
{
    private $msTeamsConnector;

    public function __construct(string $msTeamsConnector)
    {
        $this->msTeamsConnector = $msTeamsConnector;
    }

    public function __invoke(TeamsMessage $message)
    {
        $connector = new \Sebbmyr\Teams\TeamsConnector($this->msTeamsConnector);
        if (!empty($message->getUrl())) {
            $card  = new \Sebbmyr\Teams\Cards\CustomCard($message->getSubject(), $message->getBody());
            $card->addAction('Visitez le site', $message->getUrl());
            $connector->send($card);
        } else {
            $card  = new \Sebbmyr\Teams\Cards\SimpleCard(['title' => $message->getSubject(), 'text' => $message->getBody()]);
            $connector->send($card);
        }
    }
}