<?php
declare(strict_types=1);

namespace App\Entity;

use App\Repository\TaskRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=TaskRepository::class)
 * @ORM\HasLifecycleCallbacks
 */
class Task
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"task_list","task"})
     */
    private $id;

    /**
     * @Assert\NotBlank(
     *     message = "Name is required."
     * )
     * @Assert\Length(
     *      min = 3,
     *      max = 255,
     *      minMessage = "Task name requires a length of {{ limit }}",
     *      maxMessage = "Task name max length is length {{ limit }}"
     * )
     * @ORM\Column(type="string", length=255)
     * @Groups({"task_list","task"})
     */
    private $name;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"task_list","task"})
     */
    private $completedAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"task_list","task"})
     */
    private $archivedAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"task_list","task"})
     */
    private $deletedAt;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"task_list","task"})
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"task_list","task"})
     */
    private $updatedAt;

    /**
     * @ORM\ManyToOne(targetEntity=TaskList::class, inversedBy="tasks")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"task"})
     */
    private $taskList;

    /**
     * @ORM\Column(type="integer")
     * @Groups({"task_list","task"})
     */
    private $sort;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    public function __construct(TaskList $taskList, ?string $name='', ?int $sort = 0)
    {
        $this->taskList = $taskList;
        $this->name = $name;
        $this->sort = $sort;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getCompletedAt(): ?\DateTimeInterface
    {
        return $this->completedAt;
    }

    public function setCompletedAt(?\DateTimeInterface $completedAt): self
    {
        $this->completedAt = $completedAt;

        return $this;
    }

    public function getArchivedAt(): ?\DateTimeInterface
    {
        return $this->archivedAt;
    }

    public function setArchivedAt(?\DateTimeInterface $archivedAt): self
    {
        $this->archivedAt = $archivedAt;

        return $this;
    }

    public function getDeletedAt(): ?\DateTimeInterface
    {
        return $this->deletedAt;
    }

    public function setDeletedAt(?\DateTimeInterface $deletedAt): self
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getTaskList(): ?TaskList
    {
        return $this->taskList;
    }

    public function setTaskList(?TaskList $taskList): self
    {
        $this->taskList = $taskList;

        return $this;
    }

    /**
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function updatedTimestamps(): void
    {
        $this->setUpdatedAt(new \DateTime('now'));
        if ($this->getCreatedAt() === null) {
            $this->setCreatedAt(new \DateTime('now'));
        }
    }

    public function getSort(): ?int
    {
        return $this->sort;
    }

    public function setSort(int $sort): self
    {
        $this->sort = $sort;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }
}
