<?php

namespace App\Tests\Controller\Api;

use App\Tests\Traits\CreateOrUpdateTaskListTrait;
use App\Tests\Traits\HasFakerTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class TaskControllerTest extends WebTestCase
{
    use CreateOrUpdateTaskListTrait;

    public function testTaskCreation()
    {
        $client = static::createClient();

        $listData = $this->generateTaskListData();
        $listResponseData = $this->createOrUpdateTaskList($client, $listData['name'], $listData['sort']);
        $this->assertResponseIsSuccessful('Failed to create task list');

        $taskData = $this->generateTaskData($listResponseData['id']);
        $this->createOrUpdateTask($client, $taskData['task_list_id'], $taskData['name'], $taskData['sort'], $taskData['description']);
        $this->assertResponseIsSuccessful('Failed to create task');

    }

    public function testTaskCreationFailureNonExistentList()
    {
        $client = static::createClient();

        $taskData = $this->generateTaskData(999999);
        $this->createOrUpdateTask($client, $taskData['task_list_id'], $taskData['name'], $taskData['sort'], $taskData['description']);

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND, "Creation of task should have failed but it didn't");
    }

    protected function createOrUpdateTask(KernelBrowser $client, int $taskListId, string $name, int $sort, string $description, ?int $id = null): ?array
    {
        $content = json_encode(['name' => $name, 'sort' => $sort, 'description' => $description,]);
        $client->xmlHttpRequest($id ? 'PUT' : 'POST', '/api/task_list/' . $taskListId . '/task/' . $id, [], [], [], $content);

        if ($client->getResponse()->getStatusCode() === Response::HTTP_OK) {
            return json_decode($client->getResponse()->getContent(), true);
        }
        return null;
    }

    /**
     * @param int $taskListId
     * @param string|null $name
     * @param int|null $sort
     * @param string|null $description
     * @return array
     */
    protected function generateTaskData(int $taskListId, ?string $name = null, ?int $sort = null, ?string $description = null): array
    {
        return [
            'task_list_id' => $taskListId,
            'name' => $name ?: $this->getFaker()->words(3, true),
            'sort' => $sort ?: $this->getFaker()->numberBetween(0, 100),
            'description' => $description ?: $this->getFaker()->paragraph(5),
        ];
    }
}
