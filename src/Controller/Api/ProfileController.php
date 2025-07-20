<?php

namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;

class ProfileController extends AbstractController
{
    #[Route('/api/profile', name:'api_profile', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function __invoke(SerializerInterface $serializer): JsonResponse
    {
        $user = $this->getUser();

        //Serialize with "user:read" group
        $data = $serializer->serialize($user, 'json', ['groups' => ['user:read']]);

        return new JsonResponse($data, 200, [], true);
    }
}