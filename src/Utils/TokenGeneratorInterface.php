<?php

namespace App\Utils;

interface TokenGeneratorInterface
{
    public function generateToken(): string;
}
