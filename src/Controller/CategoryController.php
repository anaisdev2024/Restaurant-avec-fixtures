<?php

namespace App\Controller;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


#[Route('/category', name: 'app_category')]
class CategoryController extends AbstractController
{
    private $repository;
    private $manager;

    public function __construct(CategoryRepository $Repository, EntityManagerInterface $manager)
    {
        $this->repository = $Repository;
        $this->manager = $manager;
    }

    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/CategoryController.php',
        ]);
    }

    #[Route('', name: 'new', methods: 'POST')]
    public function new(Request $request): Response
{
    // Récupère les données JSON envoyées par Postman
    $data = json_decode($request->getContent(), true);
    
    $category= new Category();
    $category->setTitle($data['title'] ?? 'Category par défaut');
    $category->setCreatedAt(new \DateTimeImmutable());
    
    $this->manager->persist($category);
    $this->manager->flush();
    
    return $this->json(
        ['message' => "Category resource created with {$category->getId()} id"],
        Response::HTTP_CREATED
    );
}
    #[Route('/{id}', name: 'show', methods: 'GET')]

    public function show (int $id): Response
    {
         $category= $this->repository->findOneBy(['id' => $id]);
         if (!$category) {
            throw $this->createNotFoundException("No Category found for {$id} id");
            }

        return $this->json(
            ['message' => "A Category was found : {$category->getTitle()} for {$category->getId()} id"]
        );
    }
    #[Route('/{id}', name: 'edit', methods: 'PUT')]
        public function edit(int $id): Response     {
            $category= $this->repository->findOneBy(['id' => $id]);
            if (!$category) {
                throw $this->createNotFoundException("No category found for {$id} id");
                 }
            $category->setTitle('Category title updated');
            $this->manager->flush();
            return $this->redirectToRoute('app_restaurantcategory', ['id' => $category->getId()]);       
     }
    #[Route('/{id}', name: 'delete', methods: 'DELETE')]
        public function delete(int $id): Response           
         {
                
            $category = $this->repository->findOneBy(['id' => $id]);
            if (!$category) {
                throw $this->createNotFoundException("No category found for {$id} id");
                }
                    $this->manager->remove($category);
                    $this->manager->flush();
                            return $this->json(['message' => "category resource deleted"], Response::HTTP_NO_CONTENT);
                }
        }

