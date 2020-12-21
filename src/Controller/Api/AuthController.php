<?php

namespace App\Controller\Api;

use App\Entity\TaskList;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Repository\TaskListRepository;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route("/api/auth")
 */
class AuthController extends AbstractController
{
    protected $serializer;
    protected $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;

        $encoders = [new JsonEncoder()];
        $defaultContext = [
            AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object, $format, $context) {
                return $object->getId();
            },
        ];
        $normalizers = [
            new ObjectNormalizer(null, null, null, null, null, null, $defaultContext)
        ];

        $this->serializer = new Serializer($normalizers, $encoders);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @Route("/login", name="api_auth_login", methods={"POST"})
     */
    public function login(Request $request): JsonResponse
    {
        $user = $request->getUser();
        return new JsonResponse($this->serializer->serialize($user, 'json'), 200, [], true);
    }


    /**
     * @param UserRepository $userRepository
     * @param ValidatorInterface $validator
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param Request $request
     * @return JsonResponse
     * @Route("/register", name="api_auth_register", methods={"POST"})
     */
    public function register(
        UserRepository $userRepository,
        ValidatorInterface $validator,
        UserPasswordEncoderInterface $passwordEncoder,
        Request $request): JsonResponse
    {
        $data = $request->toArray();
        $user = new User(array_key_exists('email', $data) ? $data['email'] : null, array_key_exists('password', $data) ? $data['password'] : null);
        $errors = $validator->validate($user);

        if (count($errors)) {
            $errorMessages = [];
            for ($i = 0; $i < count($errors); $i++) {
                $error = $errors->get($i);
                $errorMessages[] = str_replace('"', "'", $error->getMessage());
            }
            return new JsonResponse(['messages' => $errorMessages], Response::HTTP_BAD_REQUEST);;
        }

        $existingUser = $userRepository->findOneBy(['email' => $user->getEmail()]);
        if (!$existingUser instanceof User) {
            $user->setPassword($passwordEncoder->encodePassword($user, $user->getPlainPassword()));
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();
        }

        return new JsonResponse(['messages' => ['success']], 200, []);
    }
}
