<?php

namespace App\Controller;

use App\Entity\Post;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry as PersistenceManagerRegistry;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\JsonResponse;

class PostController extends AbstractController
{
    #[Route(path: '/lista', name: 'post_index')]
    public function index(PersistenceManagerRegistry $doctrine, Security $security): Response
    {
        if ($security->isGranted('ROLE_USER')) {
            $posts = $doctrine->getRepository(Post::class)->findAll();
            return $this->render('post/index.html.twig', [
                'posts' => $posts,
            ]);
        } else {
            return $this->redirectToRoute('app_login');
        }
    }

    #[Route(path: '/posts', name: 'api_posts_index')]
    public function apiIndex(PersistenceManagerRegistry $doctrine): JsonResponse
    {
        $posts = $doctrine->getRepository(Post::class)->findAll();
        $data = [];

        foreach ($posts as $post) {
            $data[] = [
                'id' => $post->getId(),
                'title' => $post->getTitle(),
                'body' => $post->getBody(),
                'author' => $post->getAuthor(),
            ];
        }

        return new JsonResponse($data);
    }

    #[Route(path: '/posts/{id}', name: 'api_post_show', requirements: ['id' => '\d+'])]
    public function apiShowPost($id, PersistenceManagerRegistry $doctrine): JsonResponse
    {
        if (!is_numeric($id)) {
            return new JsonResponse(['message' => 'API przyjmuje tylko ID numeryczne.'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $post = $doctrine->getRepository(Post::class)->find($id);

        if (!$post) {
            return new JsonResponse(['message' => 'Nie znaleziono postu o takim id'], JsonResponse::HTTP_NOT_FOUND);
        }

        $data = [
            'id' => $post->getId(),
            'title' => $post->getTitle(),
            'body' => $post->getBody(),
            'author' => $post->getAuthor(),
        ];

        return new JsonResponse($data);
    }

    #[Route(path: '/usun-post/{id}', name: 'post_delete')]
    public function delete(Request $request, Post $post, PersistenceManagerRegistry $doctrine, Security $security): Response
    {
        if ($security->isGranted('ROLE_USER')) {    
            $entityManager = $doctrine->getManager();
            $entityManager->remove($post);
            $entityManager->flush();
        } else {
            return $this->redirectToRoute('app_login');
        }
        
        return $this->redirectToRoute('post_index');
    }
}
