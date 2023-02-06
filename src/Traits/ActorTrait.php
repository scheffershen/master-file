<?php

namespace App\Traits;

use App\Entity\UserManagement\User;

/**
 * ActorTrait
 *
 * @ORM\HasLifecycleCallbacks()
 */
trait ActorTrait
{
    /**
     * @var int
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\UserManagement\User")
     * @ORM\JoinColumn(nullable=true)
     */
    protected $createUser;

    /**
     * @var int
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\UserManagement\User")
     * @ORM\JoinColumn(nullable=true)
     */
    protected $updateUser;

    /**
     * Set createUser
     *
     * @param App\Entity\UserManagement\User $createUser
     */
    public function setCreateUser(User $createUser)
    {
        $this->createUser = $createUser;

        return $this;
    }

    /**
     * Get createUser
     *
     * @return App\Entity\UserManagement\User
     */
    public function getCreateUser()
    {
        return $this->createUser;
    }

    /**
     * Set updateUser
     *
     * @param App\Entity\UserManagement\User $updateUser
     */
    public function setUpdateUser(User $updateUser)
    {
        $this->updateUser = $updateUser;

        return $this;
    }

    /**
     * Get updateUser
     *
     * @return App\Entity\UserManagement\User
     */
    public function getUpdateUser()
    {
        return $this->updateUser;
    }
}
