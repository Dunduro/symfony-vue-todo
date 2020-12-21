<?php


namespace App\Tests\Traits;


use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\Response;

trait CreateOrUpdateTaskListTrait
{
    use HasFakerTrait;

    /**
     * @param KernelBrowser $client
     * @param string $name
     * @param int $sort
     * @param int|null $id
     * @return array|null
     */
    protected function createOrUpdateTaskList(KernelBrowser $client, string $name, int $sort, ?int $id = null): ?array
    {
        $client->xmlHttpRequest($id ? 'PUT' : 'POST', '/api/task_list/' . $id, [], [], [], json_encode(['name' => $name, 'sort' => $sort]));

        if ($client->getResponse()->getStatusCode() === Response::HTTP_OK) {
            return json_decode($client->getResponse()->getContent(), true);
        }
        return null;
    }

    /**
     * @param string|null $name
     * @param int|null $sort
     * @return array
     */
    protected function generateTaskListData(?string $name = null, ?int $sort = null): array
    {
        return [
            'name' => $name ?: $this->getFaker()->words(3, true) . ' list',
            'sort' => $sort ?: $this->getFaker()->numberBetween(0, 100)
        ];
    }
}
