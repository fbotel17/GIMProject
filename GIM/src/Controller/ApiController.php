<?php


namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ApiController extends AbstractController
{
    private $passwordHasher;
    private $entityManager;
    private $JWTManager;

    public function __construct(UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager, JWTTokenManagerInterface $JWTManager)
    {
        $this->passwordHasher = $passwordHasher;
        $this->entityManager = $entityManager;
        $this->JWTManager = $JWTManager;
    }

    #[Route('/api/login', name: 'api_login', methods: ['POST'])]
    public function login(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $identifier = $data['identifier'];
        $password = $data['password'];

        $user = $this->entityManager->getRepository(User::class)
            ->findOneBy(['email' => $identifier]);

        if (!$user) {
            $user = $this->entityManager->getRepository(User::class)
                ->findOneBy(['username' => $identifier]);
        }

        if (!$user || !$this->passwordHasher->isPasswordValid($user, $password)) {
            return new JsonResponse(['error' => 'Invalid credentials'], JsonResponse::HTTP_UNAUTHORIZED);
        }

        // Générer un JWT pour l'utilisateur
        $token = $this->JWTManager->create($user);

        return new JsonResponse(['token' => $token]);
    }

    #[Route('/api/register', name: 'api_register', methods: ['POST'])]
    public function register(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Vérification des champs obligatoires
        if (empty($data['nom']) || empty($data['prenom']) || empty($data['username']) || 
            empty($data['email']) || empty($data['password']) || empty($data['dateNaissance'])) {
            return new JsonResponse(['error' => 'Tous les champs sont obligatoires.'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $nom = $data['nom'];
        $prenom = $data['prenom'];
        $username = $data['username'];
        $email = $data['email'];
        $password = $data['password'];
        $dateNaissance = new \DateTime($data['dateNaissance']);

        // Vérifier si l'email est valide
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return new JsonResponse(['error' => 'L\'email fourni est invalide.'], JsonResponse::HTTP_BAD_REQUEST);
        }

        // Vérifier si l'utilisateur existe déjà (email ou username)
        $existingEmail = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
        if ($existingEmail) {
            return new JsonResponse(['error' => 'Cet email est déjà utilisé.'], JsonResponse::HTTP_CONFLICT);
        }

        $existingUsername = $this->entityManager->getRepository(User::class)->findOneBy(['username' => $username]);
        if ($existingUsername) {
            return new JsonResponse(['error' => 'Ce nom d\'utilisateur est déjà pris.'], JsonResponse::HTTP_CONFLICT);
        }

        // Création de l'utilisateur
        $user = new User();
        $user->setNom($nom);
        $user->setPrenom($prenom);
        $user->setUsername($username);
        $user->setEmail($email);
        $user->setDateNaissance($dateNaissance);
        $user->setRoles(['ROLE_USER']);

        // Hashage du mot de passe
        $hashedPassword = $this->passwordHasher->hashPassword($user, $password);
        $user->setPassword($hashedPassword);

        // Sauvegarde en base de données
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        // Générer un JWT
        $token = $this->JWTManager->create($user);

        return new JsonResponse([
            'message' => 'Inscription réussie',
            'token' => $token
        ]);
    }




}
