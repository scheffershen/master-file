<?php

namespace App\Library;

interface HtmlToPdfConverter
{
    public function convertToPdf(string $html);
}