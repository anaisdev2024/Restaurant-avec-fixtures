<?php

namespace App\Controller;

use App\Entity\Restaurant;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Annotations as OA;
use App\Repository\RestaurantRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[Route('/restaurant', name: 'app_restaurant')]
class RestaurantController extends AbstractController
{
    private $repository;
    private $manager;
    private $serializer;
    private $urlGenerator;

    public function __construct(RestaurantRepository $repository, EntityManagerInterface $manager, SerializerInterface $serializer, UrlGeneratorInterface $urlGenerator)
    {
        $this->repository = $repository;
        $this->manager = $manager;
        $this->serializer = $serializer;
        $this->urlGenerator = $urlGenerator;
    }

    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/RestaurantController.php',
        ]);
    }
    #[Route('', name: 'new', methods: 'POST')]

     /** 
     * @OA\Post(
     *     path="/api/restaurant",
     *     summary="Créer un nouveau restaurant",
     *     tags={"Restaurant"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Données du restaurant à créer",
     *         @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="name", type="string", example="La bonne adresse"),
     *              @OA\Property(property="description", type="string",  example="Une superbe adresse pour manger"),
     *              @OA\Property(property="maxGuest", type="integer",format= "int32", example=50)
     *             
     *            )
     *         ),
     *     @OA\Response(
     *         response=201,
     *         description="Restaurant créé avec succès",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=1),         
     *             @OA\Property(property="name", type="string", example="La bonne adresse"),
     *             @OA\Property(property="description", type="string", example="Une superbe adresse pour manger"),
     *             @OA\Property(property="createdAt", type="string", format="date-time")
     *             )
     *     )
     *  )
     */

    public function new(Request $request): JsonResponse
{
    $restaurant = $this->serializer->deserialize($request->getContent(), Restaurant::class, 'json');
    $restaurant->setCreatedAt(new DateTimeImmutable());
    
    $this->manager->persist($restaurant);
    $this->manager->flush();

    $responseData = $this->serializer->serialize($restaurant, 'json');
    $location = $this->urlGenerator->generate('app_restaurantshow', ['id' => $restaurant->getId()],UrlGeneratorInterface::ABSOLUTE_URL,);

    return new JsonResponse($responseData, Response::HTTP_CREATED, ["Location" => $location], true);
    
}
    #[Route('/{id}', name: 'show', methods: 'GET', requirements: ['id' => '\d+'], priority: -1)]

     /** 
     * @OA\Get(
     *     path="/api/restaurant/{id}",
     *     summary="Afficher un restaurant par son ID",
     *     tags={"Restaurant"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *        description="ID du restaurant à afficher",
     *         @OA\Schema(type="integer")
     * ),
     *        
     *     @OA\Response(
     *         response=200,
     *         description="Restaurant trouvé avec succès",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=1),         
     *             @OA\Property(property="name", type="string", example="La bonne adresse"),
     *             @OA\Property(property="description", type="string", example="Une superbe adresse pour manger"),
     *             @OA\Property(property="createdAt", type="string", format="date-time")
     *             )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Restaurant non trouvé"
     *             )
     *  )
     */

    public function show (int $id): Response
    {
         $restaurant = $this->repository->findOneBy(['id' => $id]);
         if (!$restaurant) {
            throw $this->createNotFoundException("No Restaurant found for {$id} id");
            }

        return $this->json(
            ['message' => "A Restaurant was found : {$restaurant->getName()} for {$restaurant->getId()} id"]
        );
    }
    #[Route('/{id}', name: 'edit', methods: 'PUT', requirements: ['id' => '\d+'])]

/**
 * @OA\Put(
 *     path="/api/restaurant/{id}",
 *     summary="Modifier un restaurant par ID",
 *     tags={"Restaurant"},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID du restaurant à modifier",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         description="Données du restaurant à modifier",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="name", type="string", example="La bonne adresse"),
 *             @OA\Property(property="description", type="string", example="Une superbe adresse pour manger"),
 *             @OA\Property(property="maxGuest", type="integer", format="int32", example=50)
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Restaurant modifié avec succès",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="id", type="integer", example=1),
 *             @OA\Property(property="name", type="string", example="La bonne adresse"),
 *             @OA\Property(property="description", type="string", example="Une superbe adresse pour manger"),
 *             @OA\Property(property="maxGuest", type="integer", example=50),
 *             @OA\Property(property="updatedAt", type="string", format="date-time")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Restaurant non trouvé"
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Données invalides"
 *     )
 * )
 */

        public function edit(int $id, Request $request): Response     {
            $restaurant = $this->repository->findOneBy(['id' => $id]);
            if ($restaurant) {
                $restaurant = $this->serializer->deserialize
                ($request->getContent(),
                Restaurant::class, 
                'json', 
                ['object_to_populate' => $restaurant]);

                $restaurant->setUpdatedAt(new DateTimeImmutable());
                $this->manager->flush();
                return new JsonResponse(null, Response::HTTP_NO_CONTENT);  
            } 
            else 
            {
                return new JsonResponse(null, Response::HTTP_NOT_FOUND);
            }
        }
    
        #[Route('/{id}', name: 'delete', methods: 'DELETE')]
        
/**
 * @OA\Delete(
 *     path="/api/restaurant/{id}",
 *     summary="Supprimer un restaurant par ID",
 *     tags={"Restaurant"},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID du restaurant à supprimer",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=204,
 *         description="Restaurant supprimé avec succès"
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Restaurant non trouvé"
 *     )
 * )
 */
        
public function delete(int $id): Response
        {
            $restaurant = $this->repository->findOneBy(['id' => $id]);
            if ($restaurant) {
                $this->manager->remove($restaurant);
                $this->manager->flush();

                return new JsonResponse (null, Response::HTTP_NO_CONTENT);    
            }
            else
            {
                return new JsonResponse(null, Response::HTTP_NOT_FOUND);
            }   
        }
    }