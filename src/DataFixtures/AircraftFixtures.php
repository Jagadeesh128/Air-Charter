<?php

namespace App\DataFixtures;

use App\Entity\Aircraft;
use App\Trait\UniqueNameGeneratorTrait;
use App\Trait\UploadFilenameGeneratorTrait;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;

class AircraftFixtures extends Fixture implements FixtureGroupInterface
{
    use UniqueNameGeneratorTrait;

    private string $projectDir;
    private string $uploadDir;

    public function __construct(
        KernelInterface $kernel,
        #[Autowire('%aircraft_upload_dir%')] string $uploadDir
    ) {
        $this->projectDir = $kernel->getProjectDir();
        $this->uploadDir = $uploadDir;
    }

    public function load(ObjectManager $manager): void
    {
        $fs = new Filesystem();
        $sampleImages = [
            'boeing.jpeg',
            'falcon.jpeg',
            'g650.jpeg',
        ];

        $aircraftData = [
            ['Falcon 2000', 'Dassault', 10, 5500, 'active', $sampleImages[0]],
            ['Gulfstream G650', 'Gulfstream', 12, 7000, 'maintenance', $sampleImages[1]],
            ['Citation XLS+', 'Cessna', 8, 3500, 'inactive', $sampleImages[2]],
        ];

        foreach ($aircraftData as [$name, $model, $capacity, $rangeKm, $status, $imageFile]) {
            $aircraft = new Aircraft();
            $aircraft->setName($name)
                ->setModel($model)
                ->setCapacity($capacity)
                ->setRangeKm($rangeKm)
                ->setStatus($status)
                ->setUpdatedAt(new \DateTimeImmutable());

            if ($imageFile) {
                $sourcePath = "{$this->projectDir}/public/sample-aircrafts/{$imageFile}";
                $uniqueName = $this->generateUniqueFilename(pathinfo($imageFile, PATHINFO_EXTENSION));
                $destinationPath = "{$this->projectDir}/{$this->uploadDir}/{$uniqueName}";

                $fs->copy($sourcePath, $destinationPath, true);
                $aircraft->setImage($uniqueName);
            }

            $manager->persist($aircraft);
        }

        $manager->flush();
    }

    public static function getGroups(): array
    {
        return ['aircraft'];
    }
}
