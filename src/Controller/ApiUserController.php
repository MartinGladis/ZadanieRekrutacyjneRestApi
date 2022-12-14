<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;
use Nelmio\ApiDocBundle\Annotation\Model;

class ApiUserController extends AbstractController
{
    private UserRepository $userRepository;

    private UserPasswordEncoderInterface $passwordEncoder;

    public function __construct(UserRepository $userRepository, UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->userRepository = $userRepository;
        $this->passwordEncoder = $passwordEncoder;
    }

    /**
     * User registration
     * 
     * @OA\Post(description="User registration")
     * 
     * @OA\RequestBody(
     *      description="User data",
     *      @OA\MediaType(
     *          mediaType="application/json",
     *          @OA\Schema(
     *              @OA\Property(
     *                  property="username",
     *                  type="string",
     *                  description="Username of account"
     *              ),
     *              @OA\Property(
     *                  property="password",
     *                  type="string",
     *                  description="Password of account"
     *              )
     *          )
     *      )
     * )
     * 
     * @OA\Response(
     *      response=201,
     *      description="Registration completed"
     * )
     * 
     * @Route("/api/users", name="register", methods={"POST"})
     */
    public function register(Request $request): JsonResponse
    {
        $parameters = json_decode($request->getContent(), true);
        $username = $parameters['username'];
        $password = $parameters['password'];
        $user = $this->userRepository->findOneBy([
            'username' => $username
        ]);

        if (!is_null($user)) {
            return $this->json(['message' => 'User already exist.'], Response::HTTP_CONFLICT);
        }

        $user = new User();
        $user->setUsername($username);
        $user->setPassword(
            $this->passwordEncoder->encodePassword($user, $password)
        );

        $this->userRepository->add($user, true);

        return $this->json([
            "message" => "Registration completed successfully"
        ], Response::HTTP_CREATED);
    }
}
