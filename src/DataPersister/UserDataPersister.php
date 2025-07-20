<?php

namespace App\DataPersister;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use ApiPlatform\State\RemoveProcessorInterface;
use ApiPlatform\State\ProviderInterface;

class UserDataPersister implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $em,
        private UserPasswordHasherInterface $passwordHasher,
        private ProcessorInterface $decorated // The default processor
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        if ($data instanceof User && $data->getPassword()) {
            // Check if it's not already hashed (optional but safer)
            if (!str_starts_with($data->getPassword(), '$2y$')) {
                $hashed = $this->passwordHasher->hashPassword($data, $data->getPassword());
                $data->setPassword($hashed);
            }
        }

        return $this->decorated->process($data, $operation, $uriVariables, $context);
    }
}
