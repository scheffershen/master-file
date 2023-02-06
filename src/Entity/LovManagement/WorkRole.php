<?php

namespace App\Entity\LovManagement;

use App\Entity\UserManagement\User;
use App\Model\Lov as baseLov;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\LovManagement\WorkRoleRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class WorkRole extends baseLov
{
    public const RPV = 'PVFR'; 
    public const EUQPPV = 'PVEU'; 
    public const DEPUTY_EUQPPV = 'PVDEP'; 
    public const CONTACT_PV_CLIENT = 'PVCC'; 
    public const AUTRE = 'PVA';     
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToMany(targetEntity=User::class, mappedBy="workRoles")
     */
    private $users;

    public function __construct()
    {
        parent::__construct();
        $this->users = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Collection|User[]
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users[] = $user;
            $user->addWorkRole($this);
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->users->contains($user)) {
            $this->users->removeElement($user);
            $user->removeWorkRole($this);
        }

        return $this;
    }    
}
