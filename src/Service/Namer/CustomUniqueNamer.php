<?php
namespace App\Service\Namer;

use App\Trait\UniqueNameGeneratorTrait;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Vich\UploaderBundle\Mapping\PropertyMapping;
use Vich\UploaderBundle\Naming\NamerInterface;

/**
 * Namer service we are using for unique when uploaded from the easy admin 
 * it will pass input file string to UniqueNameGenerator for unique name to store in db
 */

class CustomUniqueNamer implements NamerInterface
{
    use UniqueNameGeneratorTrait;

    public function name($object, PropertyMapping $mapping): string
    {
        /** @var UploadedFile|null $file */
        $file = $mapping->getFile($object);
        if (!$file) {
            throw new \RuntimeException('No file found to name.');
        }

        return $this->generateUniqueFilename($file->guessExtension() ?? $file->getClientOriginalExtension());
    }
}
