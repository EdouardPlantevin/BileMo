<?php

namespace App\Controller;

use App\Entity\Article;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api')]
class ArticleController extends AbstractController
{

    public function __construct(private ArticleRepository $articleRepository){}

    #[Route('/articles', name: 'article_get', methods: ["GET"])]
    public function get(): JsonResponse
    {
        return $this->json($this->articleRepository->findAll(), 200, [], ['groups' => 'article:read']);
    }

    #[Route('/articles', name: 'articles_post', methods: ["POST"])]
    public function post(Request $request, EntityManagerInterface $manager, SerializerInterface $serializer, ValidatorInterface $validator): JsonResponse
    {
        $data = $request->getContent();

        try {
            $article = $serializer->deserialize($data, Article::class, 'json');

            // Errors entity
            $errors = $validator->validate($article);
            if (count($errors)) { return $this->json($errors, 400); }

            $manager->persist($article);
            $manager->flush();

            return $this->json($article, 201, [], ['groups' => 'article:read']);

        } catch (NotEncodableValueException $e) {
            return $this->json([
               'status' => 400,
               'message' => $e->getMessage()
            ], 400);
        }

    }
}