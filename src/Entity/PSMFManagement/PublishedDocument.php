<?php

namespace App\Entity\PSMFManagement;

use App\Entity\LovManagement\Status;
use App\Entity\UserManagement\User;
use App\Repository\PSMFManagement\PublishedDocumentRepository;
use DH\DoctrineAuditBundle\Annotation as Audit;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @ORM\Entity(repositoryClass=PublishedDocumentRepository::class)
 * @ORM\HasLifecycleCallbacks()
 * @Audit\Auditable
 * @Audit\Security(view={"ROLE_AUDIT"})   
 * @Vich\Uploadable  
 */
class PublishedDocument
{
    public const PUBLISHED = 'PUB';
    public const DOWNLOADED = 'DLD';
    public const APPLICABLE = 'APL';
    public const ARCHIVE = 'ARC';

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $version;

    /**
     * @ORM\Column(type="datetime")
     */
    private $publicationDate;

    /**
     * @ORM\Column(type="text")
     */
    private $comentary;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="publishedDocuments")
     * @ORM\JoinColumn(nullable=false)
     */
    private $author;

    /**
     * @ORM\Column(type="text")
     */
    private $updateVariableDetails;

    /**
     * @ORM\Column(type="text")
     */
    private $updateSectionDetails;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $htmlUri;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $pdfUri;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $wordUri;
    
    /**
     * @Vich\UploadableField(mapping="uploads_psmf_signe", fileNameProperty="pdfSigneUri")
     * 
     * @var File
     */
    protected $pdfSigne;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $pdfSigneUri;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $pdfSigneComentary;

    /**
     * @var int
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\UserManagement\User")
     * @ORM\JoinColumn(nullable=true)
     */
    protected $pdfSigneUploadUser;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $pdfSigneUploadDate;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isArchived;

    /**
     * @ORM\ManyToOne(targetEntity=PSMF::class, inversedBy="publishedDocuments")
     * @ORM\JoinColumn(nullable=false)
     */
    private $psmf;

    /**
     * @ORM\Column(type="integer")
     */
    private $nbDownload;

    /**
     * @var int
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\UserManagement\User")
     * @ORM\JoinColumn(nullable=true)
     */
    protected $pdfDownloadUser;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $pdfDownloadDate;
    
    /**
     * @ORM\ManyToOne(targetEntity=Status::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $status;

    /**
     * @ORM\OneToMany(targetEntity=PublishedDocumentImportHistory::class, mappedBy="publishedDocument", orphanRemoval=true)
     * @ORM\OrderBy({"version" = "DESC"})
     */
    private $publishedDocumentImportHistories;

    public function __construct()
    {
        $this->isArchived = false;
        $this->nbDownload = 0;
        $this->publishedDocumentImportHistories = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getVersion(): ?int
    {
        return $this->version;
    }

    public function setVersion(int $version): self
    {
        $this->version = $version;

        return $this;
    }

    public function getPublicationDate(): ?\DateTimeInterface
    {
        return $this->publicationDate;
    }

    /**
     * @ORM\PrePersist
     */
    public function setPublicationDate(): self
    {
        $this->publicationDate = new \DateTime();
        return $this;
    }

    public function getComentary(): ?string
    {
        return $this->comentary;
    }

    public function setComentary(string $comentary): self
    {
        $this->comentary = $comentary;

        return $this;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): self
    {
        $this->author = $author;

        return $this;
    }

    public function getUpdateVariableDetails(): ?array
    {
        return json_decode($this->updateVariableDetails, true);
    }

    public function setUpdateVariableDetails(?string $updateVariableDetails): self
    {
        $this->updateVariableDetails = $updateVariableDetails;

        return $this;
    }

    public function getUpdateSectionDetails(): ?array
    {
        return json_decode($this->updateSectionDetails, true);
    }

    public function setUpdateSectionDetails(?string $updateSectionDetails): self
    {
        $this->updateSectionDetails = $updateSectionDetails;

        return $this;
    }

    public function getPdfUri(): ?string
    {
        return $this->pdfUri;
    }

    public function setPdfUri(string $pdfUri): self
    {
        $this->pdfUri = $pdfUri;

        return $this;
    }

    public function getHtmlUri(): ?string
    {
        return $this->htmlUri;
    }

    public function setHtmlUri(string $htmlUri): self
    {
        $this->htmlUri = $htmlUri;

        return $this;
    }

    public function getWordUri(): ?string
    {
        return $this->wordUri;
    }

    public function setWordUri(string $wordUri): self
    {
        $this->wordUri = $wordUri;

        return $this;
    }

    public function getPdfSigneUri(): ?string
    {
        return $this->pdfSigneUri;
    }

    public function setPdfSigneUri(?string $pdfSigneUri): self
    {
        $this->pdfSigneUri = $pdfSigneUri;

        return $this;
    }

    public function getPdfSigne(): ?File
    {
        return $this->pdfSigne;
    }

    public function setPdfSigne(File $pdfSigne = null): self
    {
        $this->pdfSigne = $pdfSigne;
        return $this;
    }

    public function getPdfSigneComentary(): ?string
    {
        return $this->pdfSigneComentary;
    }

    public function setPdfSigneComentary(?string $pdfSigneComentary): self
    {
        $this->pdfSigneComentary = $pdfSigneComentary;

        return $this;
    }

    /**
     * Get pdfSigneUploadUser
     *
     * @return App\Entity\UserManagement\User
     */
    public function getPdfSigneUploadUser(): ?User
    {
        return $this->pdfSigneUploadUser;
    }

    /**
     * Set pdfSigneUploadUser
     *
     * @param App\Entity\UserManagement\User $pdfSigneUploadUser
     */
    public function setPdfSigneUploadUser(?User $pdfSigneUploadUser): self
    {
        $this->pdfSigneUploadUser = $pdfSigneUploadUser;

        return $this;
    }

    /**
     * Get pdfSigneUploadDate
     *
     * @return \DateTime
     */
    public function getPdfSigneUploadDate(): ?\DateTimeInterface
    {
        return $this->pdfSigneUploadDate;
    }

    /**
     * Set pdfSigneUploadDate
     *
     * @param \DateTime $pdfSigneUploadDate
     */
    public function setPdfSigneUploadDate(?\DateTimeInterface $pdfSigneUploadDate): self
    {
        $this->pdfSigneUploadDate = $pdfSigneUploadDate;

        return $this;
    }

    public function getIsArchived(): ?bool
    {
        return $this->isArchived;
    }

    public function setIsArchived(bool $isArchived): self
    {
        $this->isArchived = $isArchived;

        return $this;
    }

    public function getPsmf(): ?PSMF
    {
        return $this->psmf;
    }

    public function setPsmf(?PSMF $psmf): self
    {
        $this->psmf = $psmf;

        return $this;
    }

    public function getNbDownload(): ?int
    {
        return $this->nbDownload;
    }

    public function setNbDownload(int $nbDownload): self
    {
        $this->nbDownload = $nbDownload;

        return $this;
    }

    /**
     * Get pdfDownloadUser
     *
     * @return App\Entity\UserManagement\User
     */
    public function getPdfDownloadUser(): ?User
    {
        return $this->pdfDownloadUser;
    }

    /**
     * Set pdfDownloadUser
     *
     * @param App\Entity\UserManagement\User $pdfDownloadUser
     */
    public function setPdfDownloadUser(?User $pdfDownloadUser): self
    {
        $this->pdfDownloadUser = $pdfDownloadUser;

        return $this;
    }

    /**
     * Get pdfDownloadDate
     *
     * @return \DateTime
     */
    public function getPdfDownloadDate(): ?\DateTimeInterface
    {
        return $this->pdfDownloadDate;
    }

    /**
     * Set pdfDownloadDate
     *
     * @param \DateTime $pdfDownloadDate
     */
    public function setPdfDownloadDate(?\DateTimeInterface $pdfDownloadDate): self
    {
        $this->pdfDownloadDate = $pdfDownloadDate;

        return $this;
    }

    public function getStatus(): ?Status
    {
        return $this->status;
    }

    public function setStatus(?Status $status): self
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return Collection<int, PublishedDocumentImportHistory>
     */
    public function getPublishedDocumentImportHistories(): Collection
    {
        return $this->publishedDocumentImportHistories;
    }

    public function addPublishedDocumentImportHistory(PublishedDocumentImportHistory $publishedDocumentImportHistory): self
    {
        if (!$this->publishedDocumentImportHistories->contains($publishedDocumentImportHistory)) {
            $this->publishedDocumentImportHistories[] = $publishedDocumentImportHistory;
            $publishedDocumentImportHistory->setPublishedDocument($this);
        }

        return $this;
    }

    public function removePublishedDocumentImportHistory(PublishedDocumentImportHistory $publishedDocumentImportHistory): self
    {
        if ($this->publishedDocumentImportHistories->removeElement($publishedDocumentImportHistory)) {
            // set the owning side to null (unless already changed)
            if ($publishedDocumentImportHistory->getPublishedDocument() === $this) {
                $publishedDocumentImportHistory->setPublishedDocument(null);
            }
        }

        return $this;
    }

    /**
    * functions utiles
    */
    public function hasLastImport(): ?PublishedDocumentImportHistory
    {
        if ($this->publishedDocumentImportHistories->count() > 0) {
            return $this->publishedDocumentImportHistories->first();
        }
        return null;
    }

    public function getLastImport(): ?PublishedDocumentImportHistory
    {
        return $this->hasLastImport();
    }
}
