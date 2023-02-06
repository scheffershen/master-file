<?php

namespace App\Utils;

interface DiffsInterface
{
    public function diffs(array $old, array $new): array;
}
