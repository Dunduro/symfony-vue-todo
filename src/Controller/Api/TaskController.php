<?php

namespace App\Controller\Api;

use App\Entity\Task;
use App\Entity\TaskList;
use App\Entity\User;
use App\Repository\TaskListRepository;
use App\Repository\TaskRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route("/api/task_list/{listId}/task")
 */
class TaskController extends AbstractController
{
    use FailedValidationTrait;

    protected $serializer;
    protected $taskRepository;
    protected $taskListRepository;
    protected $validator;

    const TASK_COMPLETE = 'complete';
    const TASK_ARCHIVE = 'archive';

    public function __construct(TaskRepository $taskRepository, TaskListRepository $taskListRepository, ValidatorInterface $validator)
    {
        $this->taskListRepository = $taskListRepository;
        $this->taskRepository = $taskRepository;
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
     * @param int $listId
     * @return JsonResponse
     * @Route("/list", name="api_task_list", methods={"GET"})
     */
    public function list(Request $request, int $listId): JsonResponse
    {
        /** @var User $user */
        $user = $request->getUser();

        $taskList = $this->taskListRepository->findOwnersList($user, $listId, !is_null($request->get('deleted')));
        if (!$taskList instanceof TaskList) {
            return new JsonResponse(['message' => 'Task list not found'], 404);
        }

        $tasks = $this->taskRepository->findAllByList($taskList, !is_null($request->get('deleted')), !is_null($request->get('archived')));

        return new JsonResponse($this->serializer->serialize($tasks, 'json'), 200, [], true);
    }

    /**
     * @param Request $request
     * @param int $listId
     * @param int $id
     * @return JsonResponse
     * @Route("/{id}", name="api_task_show", requirements={"id"="\d+"}, methods={"GET"})
     */
    public function show(Request $request, int $listId, int $id): JsonResponse
    {
        /** @var User $user */
        $user = $request->getUser();

        $taskList = $this->taskListRepository->findOwnersList($user, $listId, !is_null($request->get('deleted')));
        if (!$taskList instanceof TaskList) {
            return new JsonResponse(['message' => 'Task list not found'], 404);
        }

        $task = $this->taskRepository->findByList($taskList, $id, !is_null($request->get('deleted')), !is_null($request->get('archived')));

        if (!$task instanceof Task) {
            return new JsonResponse(['message' => 'Task not found'], 404);
        }

        return new JsonResponse($this->serializer->serialize($task, 'json'), 200, [], true);
    }

    /**
     * @param Request $request
     * @param int $listId
     * @return JsonResponse
     * @Route("/", name="api_task_create", methods={"POST"})
     */
    public function create(Request $request, int $listId): JsonResponse
    {
        /** @var User $user */
        $user = $request->getUser();

        $taskList = $this->taskListRepository->findOwnersList($user, $listId, !is_null($request->get('deleted')));
        if (!$taskList instanceof TaskList) {
            return new JsonResponse(['message' => 'Task list not found'], 404);
        }

        $task = new Task($taskList);

        try {
            $task = $this->createOrUpdate($task, $request->toArray());
        } catch (ValidationFailedException $exception) {
            return $this->handleFailedValidation($exception);
        }

        return new JsonResponse($this->serializer->serialize($task, 'json'), 200, [], true);
    }

    /**
     * @param Request $request
     * @param int $listId
     * @param int $id
     * @return JsonResponse
     * @Route("/{id}", name="api_task_update", requirements={"id"="\d+"}, methods={"PUT"})
     */
    public function update(Request $request, int $listId, int $id): JsonResponse
    {
        /** @var User $user */
        $user = $request->getUser();

        $taskList = $this->taskListRepository->findOwnersList($user, $listId, !is_null($request->get('deleted')));
        if (!$taskList instanceof TaskList) {
            return new JsonResponse(['message' => 'Task list not found'], 404);
        }

        $task = $this->taskRepository->findByList($taskList, $id, !is_null($request->get('deleted')), !is_null($request->get('archived')));

        if (!$task instanceof Task) {
            return new JsonResponse(['message' => 'Task not found'], 404);
        }

        try {
            $task = $this->createOrUpdate($task, $request->toArray());
        } catch (ValidationFailedException $exception) {
            return $this->handleFailedValidation($exception);
        }

        return new JsonResponse($this->serializer->serialize($task, 'json'), 200, [], true);
    }

    /**
     * @param Task $task
     * @param $data
     * @return mixed
     */
    protected function createOrUpdate(Task $task, $data)
    {
        if (array_key_exists('name', $data)) {
            $task->setName($data['name']);
        }
        if (array_key_exists('sort', $data) && is_numeric($data['sort'])) {
            $task->setSort($data['sort']);
        }
        if (array_key_exists('description', $data) && is_numeric($data['description'])) {
            $task->set($data['description']);
        }

        $errors = $this->validator->validate($task);
        if ($errors->count()) {
            throw new ValidationFailedException(null, $errors);
        }

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($task);
        $entityManager->flush();
        $entityManager->refresh($task);

        return $task;
    }

    /**
     * @param Request $request
     * @param int $listId
     * @param int $id
     * @return JsonResponse
     * @Route("/{id}", name="api_taskt_delete", requirements={"id"="\d+"}, methods={"DELETE"})
     */
    public function delete(Request $request, int $listId, int $id): JsonResponse
    {
        /** @var User $user */
        $user = $request->getUser();

        $taskList = $this->taskListRepository->findOwnersList($user, $listId, !is_null($request->get('deleted')));
        if (!$taskList instanceof TaskList) {
            return new JsonResponse(['message' => 'Task list not found'], 404);
        }

        $task = $this->taskRepository->findByList($taskList, $id, !is_null($request->get('deleted')), !is_null($request->get('archived')));

        if (!$task instanceof Task) {
            return new JsonResponse(['message' => 'Task not found'], 404);
        }

        $em = $this->getDoctrine()->getManager();
        if (is_null($request->get('hard_delete'))) {
            $task->setDeletedAt(new \DateTime('now'));
            $em->persist($task);
        } else {
            $em->remove($task);
        }
        $em->flush();

        return new JsonResponse(['message' => 'successfully deleted task list']);
    }

    /**
     * @param Request $request
     * @param int $listId
     * @param int $id
     * @return JsonResponse
     * @Route("/{id}/archive", name="api_task_archive", requirements={"id"="\d+"}, methods={"PUT"})
     */
    public function archive(Request $request, int $listId, int $id): JsonResponse
    {
        return $this->archiveOrComplete($request, $listId, $id, self::TASK_ARCHIVE);
    }

    /**
     * @param Request $request
     * @param int $listId
     * @param int $id
     * @return JsonResponse
     * @Route("/{id}/complete", name="api_task_complete", requirements={"id"="\d+"}, methods={"PUT"})
     */
    public function complete(Request $request, int $listId, int $id): JsonResponse
    {
        return $this->archiveOrComplete($request, $listId, $id, self::TASK_COMPLETE);
    }

    /**
     * @param Request $request
     * @param int $listId
     * @param int $id
     * @param string $method
     * @return JsonResponse
     */
    protected function archiveOrComplete(Request $request, int $listId, int $id, string $method): JsonResponse
    {
        /** @var User $user */
        $user = $request->getUser();

        $taskList = $this->taskListRepository->findOwnersList($user, $listId, !is_null($request->get('deleted')));
        if (!$taskList instanceof TaskList) {
            return new JsonResponse(['message' => 'Task list not found'], 404);
        }

        $task = $this->taskRepository->findByList($taskList, $id, !is_null($request->get('deleted')), !is_null($request->get('archived')));

        if (!$task instanceof Task) {
            return new JsonResponse(['message' => 'Task not found'], 404);
        }

        if ($method === self::TASK_COMPLETE) {
            $task->setCompletedAt(new \DateTime('now'));
        } elseif ($method === self::TASK_COMPLETE) {
            $task->setArchivedAt(new \DateTime('now'));
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($task);
        $em->flush();

        return new JsonResponse(['message' => 'successfully deleted task list']);
    }
}
