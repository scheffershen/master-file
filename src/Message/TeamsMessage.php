<?php

namespace App\Message;

class TeamsMessage
{
    private $url;
    private $subject;
    private $body;

    public function __construct(string $url=null, string $subject, string $body)
    {
        $this->url = $url;
        $this->subject = $subject;
        $this->body = $body;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function getBody(): string
    {
        return $this->body;
    }
}