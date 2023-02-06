<?php

namespace App\Message\UserManagement;

use App\Entity\UserManagement\User;

class UserUpdated
{
    private $user;
    private $old;
    private $action;

    public function __construct(string $action, array $old, User $user)
    {
        $this->user = $user;
        $this->old = $old;
        $this->action = $action;        
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getOld(): array 
    {
    	return $this->old;
    }

    public function getAction(): string 
    {
        return $this->action;
    }       
}
