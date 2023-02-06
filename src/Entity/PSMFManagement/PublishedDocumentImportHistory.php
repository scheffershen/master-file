<?php

namespace App\Entity\PSMFManagement;

use App\Entity\UserManagement\User;
use App\Repository\PSMFManagement\PublishedDocumentImportHistoryRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PublishedDocumentImportHistoryRepository::class)
 */
class PublishedDocumentImportHistory
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=PublishedDocument::class, inversedBy="publishedDocumentImportHistories")
     * @ORM\JoinColumn(nullable=false)
     */
    private $publishedDocument;

    /**
     * @ORM\Column(type="integer")
     */
    private $version;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $uri;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $auteur;

    /**
     * @ORM\Column(type="datetime")
     */
    private $importDate;

    /**
     * @ORM\Column(type="text")
     */
    private $commentaire;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPublishedDocument(): ?PublishedDocument
    {
        return $this->publishedDocument;
    }

    public function setPublishedDocument(?PublishedDocument $publishedDocument): self
    {
        $this->publishedDocument = $publishedDocument;

        return $this;
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

    public function getUri(): ?string
    {
        return $this->uri;
    }

    public function setUri(string $uri): self
    {
        $this->uri = $uri;

        return $this;
    }

    public function getAuteur(): ?User
    {
        return $this->auteur;
    }

    public function setAuteur(?User $auteur): self
    {
        $this->auteur = $auteur;

        return $this;
    }

    public function getImportDate(): ?\DateTimeInterface
    {
        return $this->importDate;
    }

    public function setImportDate(\DateTimeInterface $importDate): self
    {
        $this->importDate = $importDate;

        return $this;
    }

    public function getCommentaire(): ?string
    {
        return $this->commentaire;
    }

    public function setCommentaire(string $commentaire): self
    {
        $this->commentaire = $commentaire;

        return $this;
    }
}
