<?php

namespace App\Utils;

interface PasswordGeneratorInterface
{
    public function generateStrongPassword(): string;
}
