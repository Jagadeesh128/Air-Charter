<?php

namespace App\EventListener;

use App\Entity\RefreshToken;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Symfony\Component\Security\Core\User\UserInterface;

class JWTCreatedListener
{
    public function __construct(private EntityManagerInterface $em) {}

    public function onAuthenticationSuccess(AuthenticationSuccessEvent $event): void
    {
        $user = $event->getUser();

        if (!$user instanceof UserInterface) {
            return;
        }

        // Try to find an existing refresh token
        $existingToken = $this->em->getRepository(RefreshToken::class)->findOneBy(['user' => $user]);
        
        $expiresAt = (new \DateTime())->modify('+30 days');

        if ($existingToken) {
            $existingToken->setExpiresAt($expiresAt);
            $refreshToken = $existingToken->getToken(); // reuse token string
        }else {

            // Generate refresh token and expiry
            $refreshToken = bin2hex(random_bytes(64));
    
            // Create and persist refresh token
            $token = new RefreshToken();
            $token->setToken($refreshToken);
            $token->setUser($user); // ✅ correct method
            $token->setExpiresAt($expiresAt);
    
            $this->em->persist($token);
        }

        $this->em->flush();

        // Add refresh token to response
        $data = $event->getData();
        $data['refresh_token'] = $refreshToken;
        $event->setData($data);
    }
}
