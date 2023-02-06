<?php

namespace App\Entity\UserManagement;

use App\Entity\PSMFManagement\PSMF;
use App\Entity\PSMFManagement\PSMFHistory;
use App\Traits\ActorTrait;
use App\Traits\DateTrait;
use App\Traits\IsValidTrait;
use App\Traits\IsDeletedTrait;
use App\Traits\IsMajeurTrait;
use App\Repository\UserManagement\ClientRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use DH\DoctrineAuditBundle\Annotation as Audit;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @ORM\Entity(repositoryClass=ClientRepository::class)
 * @ORM\HasLifecycleCallbacks()
 * @UniqueEntity(fields="name", message="client name is already taken.")
 * @Audit\Auditable
 * @Audit\Security(view={"ROLE_AUDIT"})   
 * @Vich\Uploadable 
 */
class Client
{
    use ActorTrait;
    use DateTrait;
    use IsValidTrait;    
    use IsDeletedTrait;
    use IsMajeurTrait;
    
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $adress;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $logoUri;

    /**
     * @Vich\UploadableField(mapping="uploads_private", fileNameProperty="logoUri")
     * @var File
     */
    protected $logo;

    /**
     * @ORM\ManyToMany(targetEntity=User::class, mappedBy="clients")
     */
    private $users;

    /**
     * @ORM\OneToMany(targetEntity=PSMF::class, mappedBy="client", orphanRemoval=true)
     */
    private $pSMFs;

    /**
     * @ORM\OneToMany(targetEntity=PSMFHistory::class, mappedBy="client")
     */
    private $pSMFHistories;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    public $reason;

    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->pSMFs = new ArrayCollection();
        $this->pSMFHistories = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->name;
    } 
    
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getAdress(): ?string
    {
        return $this->adress;
    }

    public function setAdress(?string $adress): self
    {
        $this->adress = $adress;

        return $this;
    }

    public function getLogoUri(): ?string
    {
        return $this->logoUri;
    }

    public function setLogoUri(?string $logoUri): self
    {
        $this->logoUri = $logoUri;

        return $this;
    }

    public function getLogo(): ?File
    {
        return $this->logo;
    }

    public function setLogo(File $logo = null): self
    {
        $this->logo = $logo;
        return $this;
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
            $user->addClient($this);
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->users->contains($user)) {
            $this->users->removeElement($user);
            $user->removeClient($this);
        }

        return $this;
    }

    /**
     * @return Collection|PSMF[]
     */
    public function getPSMFs(): Collection
    {
        return $this->pSMFs;
    }

    public function addPSMF(PSMF $pSMF): self
    {
        if (!$this->pSMFs->contains($pSMF)) {
            $this->pSMFs[] = $pSMF;
            $pSMF->setClient($this);
        }

        return $this;
    }

    public function removePSMF(PSMF $pSMF): self
    {
        if ($this->pSMFs->contains($pSMF)) {
            $this->pSMFs->removeElement($pSMF);
            // set the owning side to null (unless already changed)
            if ($pSMF->getClient() === $this) {
                $pSMF->setClient(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|PSMFHistory[]
     */
    public function getPSMFHistories(): Collection
    {
        return $this->pSMFHistories;
    }

    public function addPSMFHistory(PSMFHistory $pSMFHistory): self
    {
        if (!$this->pSMFHistories->contains($pSMFHistory)) {
            $this->pSMFHistories[] = $pSMFHistory;
            $pSMFHistory->setClient($this);
        }

        return $this;
    }

    public function removePSMFHistory(PSMFHistory $pSMFHistory): self
    {
        if ($this->pSMFHistories->removeElement($pSMFHistory)) {
            // set the owning side to null (unless already changed)
            if ($pSMFHistory->getClient() === $this) {
                $pSMFHistory->setClient(null);
            }
        }

        return $this;
    }

    public function getReason(): ?string
    {
        return $this->reason;
    }

    public function setReason(?string $reason): self
    {
        $this->reason = $reason;

        return $this;
    }
}
