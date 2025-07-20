<?php

namespace App\Controller;

use App\Entity\RefreshToken;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class AuthController extends AbstractController
{
    #[Route('/api/login', name: 'api_login', methods: ['POST'])]
    public function login(): void
    {
        // This is handled automatically by LexikJWTAuthenticationBundle
        throw new \Exception('Configure firewall to use Lexik authenticator');
    }

    #[Route('/api/token/refresh', name: 'token_refresh', methods: ['POST'])]
    public function refresh(Request $request, EntityManagerInterface $em, JWTTokenManagerInterface $jwtManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $tokenStr = $data['refresh_token'] ?? null;

        if (!$tokenStr) {
            return new JsonResponse(['error' => 'Missing refresh token'], 400);
        }

        $refreshToken = $em->getRepository(RefreshToken::class)->findOneBy(['token' => $tokenStr]);

        if (!$refreshToken || $refreshToken->getExpiresAt() < new \DateTime()) {
            return new JsonResponse(['error' => 'Invalid or expired refresh token'], 401);
        }

        $user = $refreshToken->getUser();
        $newAccessToken = $jwtManager->create($user);

        return new JsonResponse(['new_access_token' => $newAccessToken]);
    }

    #[Route('/api/logout', name: 'api_logout', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function logout(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $tokenStr = $data['refresh_token'] ?? null;

        if ($tokenStr) {
            $token = $em->getRepository(RefreshToken::class)->findOneBy(['token' => $tokenStr]);
            if ($token) {
                $em->remove($token);
                $em->flush();
            }
        }

        return new JsonResponse(['message' => 'Logged out and refresh token invalidated']);
    }
}