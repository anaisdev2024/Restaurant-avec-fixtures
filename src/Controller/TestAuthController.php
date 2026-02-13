<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class TestAuthController extends AbstractController
{
    #[Route('/test-password', name: 'test_password', methods: ['GET'])]
    public function testPassword(
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher
    ): JsonResponse {
        $user = $em->getRepository(User::class)->findOneBy(['email' => 'debug@test.com']);
        
        if (!$user) {
            return $this->json(['error' => 'User not found']);
        }
        
        $isValid = $passwordHasher->isPasswordValid($user, 'test123');
        
        return $this->json([
            'email' => $user->getEmail(),
            'password_hash' => $user->getPassword(),
            'password_valid' => $isValid,
            'roles' => $user->getRoles(),
        ]);
    }
}
