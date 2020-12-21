<?php
declare(strict_types=1);

namespace App\Tests\Controller\Api;

use App\Tests\Traits\CreateOrUpdateTaskListTrait;
use App\Tests\Traits\HasFakerTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class TaskListControllerTest extends WebTestCase
{
    use CreateOrUpdateTaskListTrait;

    public function testTaskListCreation()
    {
        $client = static::createClient();

        $data = $this->generateTaskListData();
        $taskListData = $this->createOrUpdateTaskList($client, $data['name'], $data['sort']);

        $this->assertResponseIsSuccessful('Failed to create task list');
        $this->assertEquals($data['name'], $taskListData['name'], "Name of created list isn't equal to given name");
        $this->assertEquals($data['sort'], $taskListData['sort'], "Sort value of created list isn't equal to given sort value");
    }

    public function testTaskListCreationFailure()
    {
        $client = static::createClient();

        $data = $this->generateTaskListData('a');
        $this->createOrUpdateTaskList($client, $data['name'], $data['sort']);

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST, "Creation of task list should have failed but it didn't");
    }

    public function testTaskListUpdate()
    {
        $client = static::createClient();

        $data = $this->generateTaskListData();
        $taskListData = $this->createOrUpdateTaskList($client, $data['name'], $data['sort']);

        $this->assertResponseIsSuccessful('Failed to create task list');

        $updateData = $this->generateTaskListData();
        $updatedTaskListData = $this->createOrUpdateTaskList($client, $updateData['name'], $updateData['sort'], $taskListData['id']);

        $this->assertResponseIsSuccessful('Failed to update task list');
        $this->assertEquals($updateData['name'], $updatedTaskListData['name'], "Name of created list isn't equal to given name");
        $this->assertEquals($updateData['sort'], $updatedTaskListData['sort'], "Sort value of created list isn't equal to given sort value");
    }

    public function testTaskListUpdateFailure()
    {
        $client = static::createClient();

        $data = $this->generateTaskListData();
        $taskListData = $this->createOrUpdateTaskList($client, $data['name'], $data['sort']);

        $this->assertResponseIsSuccessful('Failed to create task list');

        $updateData = $this->generateTaskListData('a');
        $this->createOrUpdateTaskList($client, $updateData['name'], $updateData['sort'], $taskListData['id']);

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST, "Update of task list should have failed but it didn't");
    }


    public function testTaskListUpdateNonExisting()
    {
        $client = static::createClient();

        $updateData = $this->generateTaskListData('a');
        $this->createOrUpdateTaskList($client, $updateData['name'], $updateData['sort'], 999999);

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND, "Update of non existing task list should have failed but it didn't");
    }

    public function testTaskListShow()
    {
        $client = static::createClient();

        $data = $this->generateTaskListData();
        $creationData = $this->createOrUpdateTaskList($client, $data['name'], $data['sort']);

        $this->assertResponseIsSuccessful('Failed to create task list');

        $client->xmlHttpRequest('GET', '/api/task_list/' . $creationData['id']);
        $this->assertResponseIsSuccessful('Failed to retrieve task list');

        if ($client->getResponse()->getStatusCode() === Response::HTTP_OK) {
            $showData = json_decode($client->getResponse()->getContent(), true);

            $this->assertEquals($creationData, $showData, "Creation data and show data aren't identical");
        }
    }

    public function testTaskListShowNonExisting()
    {
        $client = static::createClient();

        $client->xmlHttpRequest('GET', '/api/task_list/999999');

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND, "Retrieving of non existing task list should have failed but it didn't");
    }

    public function testTaskListSoftDelete()
    {
        $client = static::createClient();

        $data = $this->generateTaskListData();
        $creationData = $this->createOrUpdateTaskList($client, $data['name'], $data['sort']);

        $this->assertResponseIsSuccessful('Failed to create task list');

        $client->xmlHttpRequest('DELETE', '/api/task_list/' . $creationData['id']);
        $this->assertResponseIsSuccessful('Failed to delete task list');

        $client->xmlHttpRequest('GET', '/api/task_list/' . $creationData['id']);
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND, "Retrieving of task list should have failed but it didn't");

        $client->xmlHttpRequest('GET', '/api/task_list/' . $creationData['id'] . '?deleted');
        $this->assertResponseIsSuccessful('Failed to delete task list');
    }


    public function testTaskListHardDelete()
    {
        $client = static::createClient();

        $data = $this->generateTaskListData();
        $creationData = $this->createOrUpdateTaskList($client, $data['name'], $data['sort']);

        $this->assertResponseIsSuccessful('Failed to create task list');

        $client->xmlHttpRequest('DELETE', '/api/task_list/' . $creationData['id'] . '?hard_delete');
        $this->assertResponseIsSuccessful('Failed to delete task list');

        $client->xmlHttpRequest('GET', '/api/task_list/' . $creationData['id']);
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND, "Retrieving of task list should have failed but it didn't");

        $client->xmlHttpRequest('GET', '/api/task_list/' . $creationData['id'] . '?deleted');
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND, "Retrieving of task list should have failed but it didn't");
    }
}
