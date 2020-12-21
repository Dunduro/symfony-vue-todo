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
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route("/api/task_list")
 */
class TaskListController extends AbstractController
{
    use FailedValidationTrait;

    protected $serializer;
    protected $taskListRepository;
    protected $validator;

    public function __construct(TaskListRepository $taskListRepository, ValidatorInterface $validator)
    {
        $this->taskListRepository = $taskListRepository;
        $this->validator = $validator;

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
     * @Route("/list", name="api_task_list_list", methods={"GET"})
     */
    public function list(Request $request): JsonResponse
    {
        $user = $request->getUser();
        if ($user instanceof User) {
            $taskLists = $user->getTaskLists();
        } else {
            $taskLists = $this->taskListRepository->findOwnerlessLists(!is_null($request->get('deleted')));
        }

        return new JsonResponse($this->serializer->serialize($taskLists, 'json'), 200, [], true);
    }

    /**
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     * @Route("/{id}", name="api_task_list_show", requirements={"id"="\d+"}, methods={"GET"})
     */
    public function show(Request $request, int $id): JsonResponse
    {
        /** @var User $user */
        $user = $request->getUser();

        $taskList = $this->taskListRepository->findOwnersList($user, $id, !is_null($request->get('deleted')));
        if (!$taskList instanceof TaskList) {
            return new JsonResponse(['message' => 'Task list not found'], 404);
        }

        return new JsonResponse($this->serializer->serialize($taskList, 'json'), 200, [], true);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @Route("/", name="api_task_list_create", methods={"POST"})
     */
    public function create(Request $request): JsonResponse
    {
        try {
            $taskList = $this->createOrUpdate(new TaskList(), $request->toArray());
        } catch (ValidationFailedException $exception) {
            return $this->handleFailedValidation($exception);
        }

        return new JsonResponse($this->serializer->serialize($taskList, 'json'), 200, [], true);
    }

    /**
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     * @Route("/{id}", name="api_task_list_update", requirements={"id"="\d+"}, methods={"PUT"})
     */
    public function update(Request $request, int $id): JsonResponse
    {
        /** @var User $user */
        $user = $request->getUser();

        $taskList = $this->taskListRepository->findOwnersList($user, $id, !is_null($request->get('deleted')));
        if (!$taskList instanceof TaskList) {
            return new JsonResponse(['message' => 'Task list not found'], 404);
        }

        try {
            $taskList = $this->createOrUpdate($taskList, $request->toArray());
        } catch (ValidationFailedException $exception) {
            return $this->handleFailedValidation($exception);
        }

        return new JsonResponse($this->serializer->serialize($taskList, 'json'), 200, [], true);
    }

    /**
     * @param TaskList $taskList
     * @param $data
     * @return TaskList
     */
    protected function createOrUpdate(TaskList $taskList, $data)
    {
        if (array_key_exists('name', $data)) {
            $taskList->setName($data['name']);
        }
        if (array_key_exists('sort', $data) && is_numeric($data['sort'])) {
            $taskList->setSort($data['sort']);
        }

        $errors = $this->validator->validate($taskList);
        if ($errors->count()) {
            throw new ValidationFailedException(null, $errors);
        }

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($taskList);
        $entityManager->flush();
        $entityManager->refresh($taskList);

        return $taskList;
    }

    /**
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     * @Route("/{id}", name="api_task_list_delete", requirements={"id"="\d+"}, methods={"DELETE"})
     */
    public function delete(Request $request, int $id): JsonResponse
    {
        /** @var User $user */
        $user = $request->getUser();

        $taskList = $this->taskListRepository->findOwnersList($user, $id, true);
        if (!$taskList instanceof TaskList) {
            return new JsonResponse(['message' => 'Task list not found'], 404);
        }

        $em = $this->getDoctrine()->getManager();
        if (is_null($request->get('hard_delete'))) {
            $taskList->setDeletedAt(new \DateTime('now'));
            $em->persist($taskList);
        } else {
            $em->remove($taskList);
        }
        $em->flush();


        return new JsonResponse(['message' => 'successfully deleted task list']);
    }
}
