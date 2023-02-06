<?php

namespace App\Message\UserManagement;

class SendUserImportLog
{
    private $log;

    public function __construct(array $log)
    {
        $this->log = $log;
    }

    public function getLog(): array
    {
        return $this->log;
    }
}
