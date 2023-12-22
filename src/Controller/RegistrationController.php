<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\Persistence\ManagerRegistry as PersistenceManagerRegistry;

class RegistrationController extends AbstractController
{
    #[Route(path: '/create', name: 'app_create')]
    public function register(Request $request, UserPasswordHasherInterface $passwordHasher, PersistenceManagerRegistry $doctrine): Response
    {
        if ($request->isMethod('POST')) {
            $password = $request->request->get('password');
            $email = $request->request->get('email');

            $user = new User();
            $user->setEmail($email);
            
            $hashedPassword = $passwordHasher->hashPassword(
                $user,
                $password
            );
            $user->setPassword($hashedPassword);

            $entityManager = $doctrine->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('post_index');
        } else {
            return $this->redirectToRoute('app_register');
        }
    }
}
