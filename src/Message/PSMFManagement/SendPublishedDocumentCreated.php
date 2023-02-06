<?php

namespace App\Message\PSMFManagement;

use App\Entity\PSMFManagement\PublishedDocument;

class SendPublishedDocumentCreated
{
    private $publishedDocument;

    public function __construct(PublishedDocument $publishedDocument)
    {
        $this->publishedDocument = $publishedDocument;
    }

    public function getPublishedDocument(): PublishedDocument
    {
        return $this->publishedDocument;
    }

}
