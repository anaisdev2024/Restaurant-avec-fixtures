<?php

namespace App\Controller;

use App\Entity\Food;
use App\Entity\Restaurant;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\RestaurantRepository;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\FoodRepository;


  #[Route('/food', name: 'app_food')]
class FoodController extends AbstractController
 {
    private $repository;
    private $manager;

    public function __construct(FoodRepository $Repository, EntityManagerInterface $manager)
    {
        $this->repository = $Repository;
        $this->manager = $manager;
    }

    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/RestaurantController.php',
        ]);
    }

    #[Route('', name: 'new', methods: 'POST')]

public function new(Request $request): Response
{
    // Récupère les données JSON envoyées par Postman
    $data = json_decode($request->getContent(), true);
    
    $food = new Food();
    $food->setTitle($data['title'] ?? 'Food par défaut');
    $food->setDescription($data['description'] ?? 'Description par défaut');
    $food->setPrice($data['price'] ?? 10.0);
    $food->setCreatedAt(new \DateTimeImmutable());
    
    $this->manager->persist($food);
    $this->manager->flush();
    
    return $this->json(
        ['message' => "Food resource created with {$food->getId()} id"],
        Response::HTTP_CREATED
    );
}
    #[Route('/{id}', name: 'show', methods: 'GET')]

    public function show (int $id): Response
    {
         $food = $this->repository->findOneBy(['id' => $id]);
         if (!$food) {
            throw $this->createNotFoundException("No Food found for {$id} id");
            }

        return $this->json(
            ['message' => "A Food was found : {$food->getTitle()} for {$food->getId()} id"]
        );
    }
    #[Route('/{id}', name: 'edit', methods: 'PUT')]
        public function edit(int $id): Response     {
            $food = $this->repository->findOneBy(['id' => $id]);
            if (!$food) {
                throw $this->createNotFoundException("No Food found for {$id} id");
                 }
            $food->setTitle('Food title updated');
            $this->manager->flush();
            return $this->redirectToRoute('app_restaurantfood', ['id' => $food->getId()]);       
     }
    #[Route('/{id}', name: 'delete', methods: 'DELETE')]
        public function delete(int $id): Response           
         {
                
            $food = $this->repository->findOneBy(['id' => $id]);
            if (!$food) {
                throw $this->createNotFoundException("No Food found for {$id} id");
                }
                    $this->manager->remove($food);
                    $this->manager->flush();
                    return $this->json(['message' => "food resource deleted"], Response::HTTP_NO_CONTENT);
        }
        }
