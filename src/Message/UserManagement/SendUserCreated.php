<?php

namespace App\Message\UserManagement;

use App\Entity\UserManagement\User;

class SendUserCreated
{
    private $user;
    private $password;

    public function __construct(User $user, string $password)
    {
        $this->user = $user;
        $this->password = $password;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getPassword(): string
    {
        return $this->password;
    }
}
