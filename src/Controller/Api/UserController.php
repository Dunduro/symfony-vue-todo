<?php

namespace App\Controller\Api;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

/**
 * @Route("/api/user")
 */
class UserController extends AbstractController
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
     * @Route("/list", name="api_user_list", methods={"GET"})
     */
    public function list(): JsonResponse
    {
        $users = $this->userRepository->findAll();
        return new JsonResponse($this->serializer->serialize($users, 'json'), 200, [], true);
    }
}
