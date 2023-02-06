<?php

namespace App\Entity\UserManagement;

use App\Entity\PSMFManagement\PSMF;
use App\Entity\LovManagement\EntitType;
use App\Entity\LovManagement\WorkRole;
use App\Entity\LovManagement\Gender;
use App\Entity\PSMFManagement\PSMFHistory;
use App\Entity\PSMFManagement\PublishedDocument;
use App\Traits\ActorTrait;
use App\Traits\DateTrait;
use App\Traits\IsEnableTrait;
use App\Traits\IsDeletedTrait;
use App\Traits\ReasonTrait;
use DH\DoctrineAuditBundle\Annotation as Audit;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserManagement\UserRepository")
 * @ORM\Table(name="user")
 * @UniqueEntity("email")
 * @ORM\HasLifecycleCallbacks()
 * @Audit\Auditable
 * @Audit\Security(view={"ROLE_AUDIT"})   
 * @Vich\Uploadable 
 */
class User implements UserInterface
{
    public const ROLES = [
                            'User'   => 'ROLE_UTILISATEUR',
                            'Consultant' => 'ROLE_CONSULTANT',
                            'Super Consultant' => 'ROLE_SUPER_CONSULTANT',
                            'Administrator' => 'ROLE_ADMIN',
                         ];

    use ActorTrait;
    use DateTrait;
    use IsEnableTrait;    
    use IsDeletedTrait;
    use ReasonTrait;

    /**
     * Requests older than this many seconds will be considered expired.
     */
    public const RETRY_TTL = 720; # 2h
    /**
     * Maximum time that the confirmation token will be valid.
     */
    public const TOKEN_TTL = 180; # 30 minutes.

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", unique=true)
     * @Assert\NotBlank()
     * @Assert\Length(min=2, max=50)
     */
    protected $username;

    /**
     * @var string
     *
     * @ORM\Column(name="firstname", type="string", length=100, nullable=true)
     * @Assert\Length(
     *      min = 2,
     *      max = 100,
     *      minMessage = "Your first name must be at least {{ limit }} characters long",
     *      maxMessage = "Your first name cannot be longer than {{ limit }} characters"
     * )
     * @ORM\OrderBy({"firstname" = "DESC"})
     */
    private $firstName;

    /**
     * @var string
     *
     * @ORM\Column(name="lastname", type="string", length=100, nullable=true)
     * @Assert\Length(
     *      min = 2,
     *      max = 100,
     *      minMessage = "Your last name must be at least {{ limit }} characters long",
     *      maxMessage = "Your last name cannot be longer than {{ limit }} characters"
     * )
     * @ORM\OrderBy({"lastname" = "DESC"})
     */
    protected $lastName;

    /**
     * @var string
     *
     * @ORM\Column(name="mobile", type="string", length=40, nullable=true)
     * @Assert\Length(
     *      min = 6,
     *      max = 40,
     *      minMessage = "Your phone must be at least {{ limit }} characters long",
     *      maxMessage = "Your phone cannot be longer than {{ limit }} characters"
     * )
     */
    protected $mobile;

    /**
     * @var string
     *
     * @ORM\Column(name="fixe", type="string", length=40, nullable=true)
     * @Assert\Length(
     *      min = 6,
     *      max = 40,
     *      minMessage = "Your phone must be at least {{ limit }} characters long",
     *      maxMessage = "Your phone cannot be longer than {{ limit }} characters"
     * )
     */
    protected $fixe;

    /**
     * @var string
     *
     * @ORM\Column(name="adresse", type="string", length=150, nullable=true)
     * @Assert\Length(
     *      min = 2,
     *      max = 150,
     *      minMessage = "Your address must be at least {{ limit }} characters long",
     *      maxMessage = "Your address cannot be longer than {{ limit }} characters"
     * )
     */
    protected $adresse;

    /**
     * @var string
     *
     * @ORM\Column(name="city", type="string", length=80, nullable=true)
     * @Assert\Length(
     *      min = 2,
     *      max = 80,
     *      minMessage = "Your city must be at least {{ limit }} characters long",
     *      maxMessage = "Your city cannot be longer than {{ limit }} characters"
     * )
     */
    protected $city;

    /**
     * @var string
     *
     * @ORM\Column(name="zip_code", type="string", length=40, nullable=true)
     * @Assert\Length(
     *      min = 2,
     *      max = 40,
     *      minMessage = "Your zip code must be at least {{ limit }} characters long",
     *      maxMessage = "Your zip code cannot be longer than {{ limit }} characters"
     * )
     */
    protected $zipCode;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $cvUri;

    /**
     * @Vich\UploadableField(mapping="uploads_private", fileNameProperty="cvUri")
     * @var File
     */
    protected $cv;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection()
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\LovManagement\Gender")
     * @ORM\JoinColumn(nullable=true)
     */
    protected $gender;

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    protected $password;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     * @Assert\Email()
     */
    protected $email;

    /**
     * @ORM\Column(type="json")
     */
    protected $roles = [];

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $confirmation_token;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $password_requested_at;
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $fullName;

    /**
     * @var bool
     *
     * @ORM\Column(name="change_password", type="boolean", nullable=false)
     */
    protected $change_password = false;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    protected $fax;

    /**
     * @ORM\ManyToMany(targetEntity=WorkRole::class, inversedBy="users")
     */
    protected $workRoles;

    /**
     * @ORM\ManyToOne(targetEntity=EntitType::class)
     * @ORM\JoinColumn(nullable=true)
     */
    protected $workAttachment;

    /**
     * @ORM\ManyToMany(targetEntity=Client::class, inversedBy="users")
     */
    protected $clients;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $workFunction;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $workName;

    /**
     * @ORM\OneToMany(targetEntity=PublishedDocument::class, mappedBy="author", orphanRemoval=true)
     */
    protected $publishedDocuments;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $pvUser;

    /**
     * @ORM\OneToMany(targetEntity=PSMFHistory::class, mappedBy="pvuser", orphanRemoval=true)
     */
    protected $pSMFHistories;

    /**
     * @ORM\OneToMany(targetEntity=PSMF::class, mappedBy="euQPPV", orphanRemoval=true)
     */
    protected $euQPPVPSMFs;

    /**
     * @ORM\OneToMany(targetEntity=PSMF::class, mappedBy="deputyEUQPPV", orphanRemoval=true)
     */
    protected $deputyEUQPPVPSMFs;

    /**
     * @ORM\OneToMany(targetEntity=PSMF::class, mappedBy="contactPvClient", orphanRemoval=true)
     */
    protected $contactPvClientPSMFs;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $mailAlerte;

    public function __construct()
    {
        $this->clients = new ArrayCollection();
        $this->workRoles = new ArrayCollection();
        $this->publishedDocuments = new ArrayCollection();
        $this->pSMFHistories = new ArrayCollection();
        $this->euQPPVPSMFs = new ArrayCollection();
        $this->deputyEUQPPVPSMFs = new ArrayCollection();
        $this->contactPvClientPSMFs = new ArrayCollection();
    }

    public function __toString()
    {
        if (!empty($this->lastName) && !empty($this->firstName)) {
            return $this->firstName . ' ' . $this->lastName;
        }
        if (!empty($this->fullName)) {
            return $this->fullName;
        }
        return $this->username;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    public function setFirstName(?string $firstName): void
    {
        $this->firstName = $firstName;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getMobile(): ?string
    {
        return $this->mobile;
    }

    public function setMobile(?string $mobile): void
    {
        $this->mobile = $mobile;
    }

    public function getFixe(): ?string
    {
        return $this->fixe;
    }

    public function setFixe(?string $fixe): void
    {
        $this->fixe = $fixe;
    }

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(?string $adresse): void
    {
        $this->adresse = $adresse;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): void
    {
        $this->city = $city;
    }

    public function getZipCode(): ?string
    {
        return $this->zipCode;
    }

    public function setZipCode(?string $zipCode): void
    {
        $this->zipCode = $zipCode;
    }

    public function getCvUri(): ?string
    {
        return $this->cvUri;
    }

    public function setCvUri(?string $cvUri): self
    {
        $this->cvUri = $cvUri;

        return $this;
    }

    public function getCv(): ?File
    {
        return $this->cv;
    }

    public function setCv(File $cv = null): self
    {
        $this->cv = $cv;
        return $this;
    }

    public function setGender(Gender $gender): void
    {
        $this->gender = $gender;
    }

    public function getGender(): ?Gender
    {
        return $this->gender;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getSalt(): ?string
    {
        return null;
    }

    public function eraseCredentials(): void
    {
    }

    public function getConfirmationToken(): ?string
    {
        return $this->confirmation_token;
    }

    public function setConfirmationToken(?string $confirmation_token): self
    {
        $this->confirmation_token = $confirmation_token;

        return $this;
    }

    public function getPasswordRequestedAt(): ?\DateTimeInterface
    {
        return $this->password_requested_at;
    }

    public function setPasswordRequestedAt(?\DateTimeInterface $password_requested_at): self
    {
        $this->password_requested_at = $password_requested_at;

        return $this;
    }

    public function isPasswordRequestNonExpired(int $ttl): bool
    {
        return $this->getPasswordRequestedAt() instanceof \DateTime &&
            $this->getPasswordRequestedAt()->getTimestamp() + $ttl > time();
    }

    public function getFullName(): ?string
    {
        if (!empty($this->fullName)) {
            return $this->fullName;
        } else {
            return $this->firstName . ' ' . $this->lastName;
        }
    }

    public function setFullName(?string $fullName): self
    {
        $this->fullName = $fullName;

        return $this;
    }

    public function setChangePassword(bool $change_password): ?self
    {
        $this->change_password = $change_password;

        return $this;
    }

    public function getChangePassword(): ?bool
    {
        return $this->change_password;
    }

    public function getFax(): ?string
    {
        return $this->fax;
    }

    public function setFax(?string $fax): self
    {
        $this->fax = $fax;

        return $this;
    }

    /**
     * @return Collection|Client[]
     */
    public function getWorkRoles(): Collection
    {
        return $this->workRoles;
    }

    public function addWorkRole(WorkRole $workRole): self
    {
        if (!$this->workRoles->contains($workRole)) {
            $this->workRoles[] = $workRole;
        }

        return $this;
    }

    public function removeWorkRole(WorkRole $workRole): self
    {
        if ($this->workRoles->contains($workRole)) {
            $this->workRoles->removeElement($workRole);
        }

        return $this;
    }

    public function getWorkAttachment(): ?EntitType
    {
        return $this->workAttachment;
    }

    public function setWorkAttachment(?EntitType $workAttachment): self
    {
        $this->workAttachment = $workAttachment;

        return $this;
    }

    /**
     * @return Collection|Client[]
     */
    public function getClients(): Collection
    {
        return $this->clients;
    }

    public function addClient(Client $client): self
    {
        if (!$this->clients->contains($client)) {
            $this->clients[] = $client;
        }

        return $this;
    }

    public function removeClient(Client $client): self
    {
        if ($this->clients->contains($client)) {
            $this->clients->removeElement($client);
        }

        return $this;
    }

    public function getWorkFunction(): ?string
    {
        return $this->workFunction;
    }

    public function setWorkFunction(?string $workFunction): self
    {
        $this->workFunction = $workFunction;

        return $this;
    }

    public function getWorkName(): ?string
    {
        return $this->workName;
    }

    public function setWorkName(?string $workName): self
    {
        $this->workName = $workName;

        return $this;
    }

    /**
     * @return Collection|PublishedDocument[]
     */
    public function getPublishedDocuments(): Collection
    {
        return $this->publishedDocuments;
    }

    public function addPublishedDocument(PublishedDocument $publishedDocument): self
    {
        if (!$this->publishedDocuments->contains($publishedDocument)) {
            $this->publishedDocuments[] = $publishedDocument;
            $publishedDocument->setAuthor($this);
        }

        return $this;
    }

    public function removePublishedDocument(PublishedDocument $publishedDocument): self
    {
        if ($this->publishedDocuments->contains($publishedDocument)) {
            $this->publishedDocuments->removeElement($publishedDocument);
            // set the owning side to null (unless already changed)
            if ($publishedDocument->getAuthor() === $this) {
                $publishedDocument->setAuthor(null);
            }
        }

        return $this;
    }

    public function getPvUser(): ?bool
    {
        return $this->pvUser;
    }

    public function setPvUser(bool $pvUser): self
    {
        $this->pvUser = $pvUser;

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
            $pSMFHistory->setPvuser($this);
        }

        return $this;
    }

    public function removePSMFHistory(PSMFHistory $pSMFHistory): self
    {
        if ($this->pSMFHistories->removeElement($pSMFHistory)) {
            // set the owning side to null (unless already changed)
            if ($pSMFHistory->getPvuser() === $this) {
                $pSMFHistory->setPvuser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|euQPPVPSMFs[]
     */
    public function getEuQPPVPSMFs(): Collection
    {
        return $this->euQPPVPSMFs;
    }

    public function addEuQPPVPSMF(PSMF $pSMF): self
    {
        if (!$this->euQPPVPSMFs->contains($pSMF)) {
            $this->euQPPVPSMFs[] = $pSMF;
            $pSMF->setEuQPPV($this);
        }

        return $this;
    }

    public function removeEuQPPVPSMF(PSMF $pSMF): self
    {
        if ($this->euQPPVPSMFs->removeElement($pSMF)) {
            // set the owning side to null (unless already changed)
            if ($pSMF->getEuQPPV() === $this) {
                $pSMF->setEuQPPV(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|deputyEUQPPVPSMFs[]
     */
    public function getDeputyEUQPPVPSMFs(): Collection
    {
        return $this->deputyEUQPPVPSMFs;
    }

    public function addDeputyEUQPPVPSMF(PSMF $pSMF): self
    {
        if (!$this->deputyEUQPPVPSMFs->contains($pSMF)) {
            $this->deputyEUQPPVPSMFs[] = $pSMF;
            $pSMF->setDeputyEUQPPV($this);
        }

        return $this;
    }

    public function removeDeputyEUQPPVPSMF(PSMF $pSMF): self
    {
        if ($this->deputyEUQPPVPSMFs->removeElement($pSMF)) {
            // set the owning side to null (unless already changed)
            if ($pSMF->getDeputyEUQPPV() === $this) {
                $pSMF->setDeputyEUQPPV(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|contactPvClientPSMFs[]
     */
    public function getContactPvClientPSMFs(): Collection
    {
        return $this->contactPvClientPSMFs;
    }

    public function addContactPvClientPSMF(PSMF $pSMF): self
    {
        if (!$this->contactPvClientPSMFs->contains($pSMF)) {
            $this->contactPvClientPSMFs[] = $pSMF;
            $pSMF->setContactPvClient($this);
        }

        return $this;
    }

    public function removeContactPvClientPSMF(PSMF $pSMF): self
    {
        if ($this->contactPvClientPSMFs->removeElement($pSMF)) {
            // set the owning side to null (unless already changed)
            if ($pSMF->getContactPvClient() === $this) {
                $pSMF->setContactPvClient(null);
            }
        }

        return $this;
    }   

    public function getMailAlerte(): ?bool
    {
        return $this->mailAlerte;
    }

    public function setMailAlerte(?bool $mailAlerte): self
    {
        $this->mailAlerte = $mailAlerte;

        return $this;
    }

    public function isChangePassword(): ?bool
    {
        return $this->change_password;
    }

    public function isPvUser(): ?bool
    {
        return $this->pvUser;
    }

    public function isMailAlerte(): ?bool
    {
        return $this->mailAlerte;
    }
    
}
