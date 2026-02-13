<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Annotations as OA;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Security\Core\Security as CoreSecurity;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use DateTimeImmutable;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/', name: 'app_restaurant')]
class SecurityController extends AbstractController
{
    public function __construct(private EntityManagerInterface $manager,private SerializerInterface $serializer,private CoreSecurity $security)
{}

    
    #[Route('/registration', name: 'registration', methods: 'POST')]
     /** 
     * @OA\Post(
     *     path="/api/registration",
     *     summary="Inscription d'un nouvel utilisateur",
     *     tags={"User"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Données de l'utilisateur à inscrire",
     *         @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="email", type="string", example="adresse@email.com"),
     *              @OA\Property(property="password", type="string",  example="Mot de passe"),
     *              @OA\Property(property="firstName", type="string", example="Anais"),
     *              @OA\Property(property="lastName", type="string",  example="IBARI")
     *            )
     *         ),
     *     @OA\Response(
     *         response=201,
     *         description="Utilisateur inscrit avec succès",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="user", type="string", example="Nom d'utilisateur"),     *          
     *             @OA\Property(property="apiToken", type="string", example="31a023e212f116124a36af14ea0c1c3806eb9378"),
     *             @OA\Property(property="roles", type="array", @OA\Items(type="string", example="ROLE_USER"))
     *         )
     *     )
     * )
     */

    public function register(Request $request, UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        $user = $this->serializer->deserialize($request->getContent(), User::class, 'json');
        $user->setPassword($passwordHasher->hashPassword($user, $user->getPassword()));
        $user->setCreatedAt(new DateTimeImmutable());
        $this->manager->persist($user);
        $this->manager->flush();
        
        return new JsonResponse(
            ['user'  => $user->getUserIdentifier(), 'apiToken' => $user->getApiToken(), 'roles' => $user->getRoles()],
            Response::HTTP_CREATED
        );
    }
    
    #[Route('/login', name: 'login', methods: 'POST')]

     /** @OA\Post(
     *     path="/api/login",
     *     summary="Connecter un utilisateur",
     *     tags={"User"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Données de l’utilisateur pour se connecter",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="email", type="string", example="adresse@email.com"),
     *             @OA\Property(property="password", type="string", example="Mot de passe")
     *         )
     *     ),
     *      @OA\Response(
     *          response=200,
     *          description="Connexion réussie",
     *          @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="user", type="string", example="Nom d'utilisateur"),
     *             @OA\Property(property="apiToken", type="string", example="31a023e212f116124a36af14ea0c1c3806eb9378"),
     *             @OA\Property(property="roles", type="array", @OA\Items(type="string", example="ROLE_USER"))
     *          )
     *      )
     *   )
     */
    public function login(): JsonResponse
    {
        // Cette méthode sera appelée SEULEMENT si json_login réussit
        // car json_login intercepte et authentifie avant
        $user = $this->getUser();
        
        if (!$user instanceof User) {
            throw new \LogicException('User should be authenticated at this point');
        }
        
        return new JsonResponse([
            'user' => $user->getUserIdentifier(),
            'apiToken' => $user->getApiToken(),
            'roles' => $user->getRoles(),
        ]);
    }

    #[Route('/me',name: 'me', methods: ['GET'])]
    
/**
 * @OA\Get(
 *     path="/api/me",
 *     summary="Récupérer les informations de l'utilisateur connecté",
 *     tags={"User"},
 *     @OA\Response( 
 *         response=200,
 *         description="Informations de l'utilisateur connecté",
 *         @OA\JsonContent(
 *             ref=@Model(type=User::class, groups={"user:read"})
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Non authentifié - Token invalide ou manquant",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="Non authentifié")
 *         )
 *     )
 * )
 * @Security(name="apitoken")
 */
    public function me(): JsonResponse
    {
    $user = $this->getUser();
    if (!$user instanceof User) {
    return $this->json(['message' => 'Non authentifié'], 401);
        }
    return new JsonResponse([
    'user' => $user->getUserIdentifier(),
    'apiToken' => $user->getApiToken(),
    'roles' => $user->getRoles(),
        ]);
    }
 
    #[Route('/me/edit', name: 'me_edit', methods: ['PUT', 'PATCH'])]
    /**
 * @OA\Put(
 *     path="/api/me/edit",
 *     summary="Modifier les informations de l'utilisateur connecté",
 *     tags={"User"},
 *     @OA\RequestBody(
 *         required=true,
 *         description="Données à modifier",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="email", type="string", format="email", example="nouveau@email.com"),
 *             @OA\Property(property="firstName", type="string", example="Anais"),
 *             @OA\Property(property="lastName", type="string", example="IBARI"),
 *             @OA\Property(property="password", type="string", format="password", example="NouveauMotDePasse123!")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Utilisateur modifié avec succès",
 *         @OA\JsonContent(
 *             ref=@Model(type=User::class, groups={"user:read"})
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Données invalides",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="Email déjà utilisé")
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Non authentifié - Token invalide ou manquant",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="Non authentifié")
 *         )
 *     )
 * )
 * @Security(name="apitoken")
 */
    public function edit(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        
        if (!$user) {
            return $this->json(['message' => 'Non authentifié'], 401);
        }

        // Récupérer les données JSON de la requête
        $data = json_decode($request->getContent(), true);
        
        // Mettre à jour les champs si fournis
        if (isset($data['email'])) {
            $user->setEmail($data['email']);
        }
        
        if (isset($data['firstName'])) {
            $user->setFirstName($data['firstName']);
        }
        
        if (isset($data['lastName'])) {
            $user->setLastName($data['lastName']);
        }
        
        if (isset($data['guestNumber'])) {
            $user->setGuestNumber($data['guestNumber']);
        }
        
        if (isset($data['allergy'])) {
            $user->setAllergy($data['allergy']);
        }
        
        // Gérer le mot de passe s'il est fourni
        if (isset($data['password']) && !empty($data['password'])) {
            $hashedPassword = $passwordHasher->hashPassword($user, $data['password']);
            $user->setPassword($hashedPassword);
        }
        
        // Mettre à jour la date de modification
        $user->setUpdatedAt(new \DateTimeImmutable());
        
        // Persister en base de données
        $entityManager->flush();
        
        return $this->json(['message' => 'Utilisateur mis à jour avec succès'], 200);
    }
}