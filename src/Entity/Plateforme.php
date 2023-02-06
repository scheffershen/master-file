<?php

namespace App\Entity;

use App\Repository\PlateformeRepository;
use App\Traits\ActorTrait;
use App\Traits\DateTrait;
use DH\DoctrineAuditBundle\Annotation as Audit;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @ORM\Entity(repositoryClass=PlateformeRepository::class)
 * @ORM\HasLifecycleCallbacks()
 * @Audit\Auditable
 * @Audit\Security(view={"ROLE_AUDIT"})
 * @Vich\Uploadable 
 */
class Plateforme
{
    public const PSMF = 'psmf';

    use ActorTrait;
    use DateTrait;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $nom;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $email;

    /**
     * maybe late.
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $logoUri;

    /**
     * maybe late.
     *
     * @Vich\UploadableField(mapping="uploads_private", fileNameProperty="logoUri")
     *
     * @var File
     */
    protected $logo;

    /**
     * @ORM\Column(type="text")
     */
    private $version;

    /**
     * @ORM\Column(type="text")
     */
    private $poweredBy;

    /**
     * @ORM\Column(type="datetime")
     */
    private $qualiosLastUpdate;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $code;

    public function __toString()
    {
        return $this->nom;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
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
    
    public function getVersion(): ?string
    {
        return $this->version;
    }

    public function setVersion(string $version): self
    {
        $this->version = $version;

        return $this;
    }

    public function getPoweredBy(): ?string
    {
        return $this->poweredBy;
    }

    public function setPoweredBy(string $poweredBy): self
    {
        $this->poweredBy = $poweredBy;

        return $this;
    }

    public function getQualiosLastUpdate(): ?\DateTimeInterface
    {
        return $this->qualiosLastUpdate;
    }

    public function setQualiosLastUpdate(\DateTimeInterface $qualiosLastUpdate): self
    {
        $this->qualiosLastUpdate = $qualiosLastUpdate;

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): self
    {
        $this->code = $code;

        return $this;
    }
}
