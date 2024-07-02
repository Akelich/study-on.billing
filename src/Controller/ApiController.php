<?php

namespace App\Controller;

use App\DTO\UserDTO;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\Serializer;
use JMS\Serializer\SerializerBuilder;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasher;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
//use OpenApi\Annotations as OA;
use OpenApi\Attributes as OA;


class ApiController extends AbstractController
{
    private Serializer $serializer;

    private ValidatorInterface $validator;

    private UserPasswordHasherInterface $hasher;

    public function __construct(
        ValidatorInterface $validator,
        UserPasswordHasherInterface $hasher
    )
    {
        $this->validator = $validator;
        $this->serializer = SerializerBuilder::create()->build();
        $this->hasher = $hasher;
    }

    #[Route('/api/v1/auth', name: 'api_auth', methods: ['POST'])]
    
    #[OA\Post( 
        path: '/api/v1/auth', 
        description: "Входные данные: email и пароль.", 
        summary: "Аутентификация пользователя" 
    )] 
 
    #[OA\RequestBody( 
        required: true, 
        content: new OA\JsonContent( 
            properties: [ 
                new OA\Property(property: 'username', type: 'string'), 
                new OA\Property(property: 'password', type: 'string') 
            ], 
            type: 'object' 
        ) 
    )] 
 
    #[OA\Response( 
        response: 200, 
        description: 'Успешная аутентификация', 
        content: new OA\JsonContent( 
            properties: [ 
                new OA\Property(property: 'token', type: 'string')
            ], 
            type: 'object' 
        ) 
    )] 
 
    #[OA\Response( 
        response: 401, 
        description: 'Невалидные данные', 
        content: new OA\JsonContent( 
            properties: [ 
                new OA\Property(property: 'code', type: 'string', example: 401), 
                new OA\Property( 
                    property: 'message', 
                    type: 'string', 
                    example: 'Invalid credentials.' 
                ) 
            ], 
            type: 'object' 
        ) 
    )] 
 
    #[OA\Tag( 
        name: "User" 
    )]

    public function auth(): void
    {  

    }

    #[Route('/api/v1/register', name: 'api_register', methods: ['POST'])]
    
    #[OA\Post( 
        path: '/api/v1/register', 
        description: "Входные данные: email и пароль.", 
        summary: "Регистрация пользователя" 
    )] 
 
    #[OA\RequestBody( 
        required: true, 
        content: new OA\JsonContent( 
            properties: [ 
                new OA\Property(property: 'username', type: 'string'), 
                new OA\Property(property: 'password', type: 'string') 
            ], 
            type: 'object' 
        ) 
    )] 
 
    #[OA\Response( 
        response: 201, 
        description: 'Успешная регистрация', 
        content: new OA\JsonContent( 
            properties: [ 
                new OA\Property(property: 'token', type: 'string'), 
                new OA\Property( 
                    property: 'roles', 
                    type: 'array', 
                    items: new OA\Items(type: "string") 
                ), 
            ], 
            type: 'object' 
        ) 
    )] 
     
 
    #[OA\Response( 
        response: 400, 
        description: 'Невалидные данные', 
        content: new OA\JsonContent( 
            properties: [ 
                new OA\Property(property: 'code', type: 'string', example: 400), 
                new OA\Property( 
                    property: 'errors', 
                    type: 'array', 
                    items: new OA\Items(type: "string") 
                ) 
            ], 
            type: 'object' 
        ) 
    )] 

    #[OA\Tag( 
        name: "User" 
    )]

    public function register(Request $request, UserRepository $userRepository, JWTTokenManagerInterface $jwtTokenManager, EntityManagerInterface $entityManager): JsonResponse
    {
        $dto = $this->serializer->deserialize($request->getContent(), UserDTO::class, 'json');
        $errors = $this->validator->validate($dto);

        if (count($errors) > 0) {
            $jsonErrors = [];
            foreach ($errors as $error) {
                $jsonErrors[$error->getPropertyPath()] = $error->getMessage();
            }
            return new JsonResponse(['error' => $jsonErrors], Response::HTTP_BAD_REQUEST);
        }

        if ($userRepository->findOneBy(['email' => $dto->username])) {
            return new JsonResponse(['error' => 'Такой email уже зарегистрирован.'], Response::HTTP_CONFLICT);
        }
        $user = User::fromDTO($dto);
        $user->setPassword($this->hasher->hashPassword($user, $dto->password));
        $userRepository->add($user, true);
        
        return new JsonResponse([
            'token' => $jwtTokenManager->create($user),
            'roles' => $user->getRoles(),
        ], Response::HTTP_CREATED);
    }
      
    #[Route('/api/v1/users/current', name: 'api_current_user', methods: ['GET'])]
      
    #[OA\Get( 
        path: '/api/v1/users/current', 
        description: "Входные данные - JWT-токен.", 
        summary: "Получение текущего пользователя" 
    )] 
 
    #[OA\Response( 
        response: 200, 
        description: 'Успешное получение пользователя', 
        content: new OA\JsonContent( 
            properties: [ 
                new OA\Property(property: 'username', type: 'string'), 
                new OA\Property( 
                    property: 'roles', 
                    type: 'array', 
                    items: new OA\Items(type: "string") 
                ), 
                new OA\Property(property: 'balance', type: 'integer', example: 0) 
            ], 
            type: 'object' 
        ) 
    )] 
 
    #[OA\Response( 
        response: 401, 
        description: 'Невалидный JWT-токен', 
        content: new OA\JsonContent( 
            properties: [ 
                new OA\Property(property: 'code', type: 'string', example: 401), 
                new OA\Property(property: 'errors', type: 'string', example: "Invalid JWT Token") 
            ], 
            type: 'object' 
        ) 
    )] 
 
    #[OA\Response( 
        response: 500, 
        description: 'Ошибка сервера', 
        content: new OA\JsonContent( 
            properties: [ 
                new OA\Property(property: 'error', type: 'string') 
            ], 
            type: 'object' 
        ) 
    )] 
    #[OA\Tag( 
        name: "User" 
    )] 
 
    #[Security(name: "Bearer")]

    public function currentUser(): JsonResponse
    {
        return new JsonResponse([
            'username' => $this->getUser()->getUserIdentifier(),
            'roles' => $this->getUser()->getRoles(),
            'balance' => $this->getUser()->getBalance(),
        ], Response::HTTP_OK);
    }
    
}
