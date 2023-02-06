<?php

namespace App\Message\UserManagement;

use App\Entity\UserManagement\User;

class SendResetPasswordLink
{
    private $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function getUser(): User
    {
        return $this->user;
    }
}
