<?php

namespace App\Trait;

/**
 * Generate Unique String for input file
 */

trait UniqueNameGeneratorTrait
{
    public function generateUniqueFilename(string $extension): string
    {
        return bin2hex(random_bytes(8)) . '.' . strtolower($extension);
    }
}