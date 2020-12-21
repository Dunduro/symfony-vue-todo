<?php
declare(strict_types=1);

namespace App\Entity;

use App\Repository\TaskListRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;

/**
 * @ORM\Entity(repositoryClass=TaskListRepository::class)
 * @ORM\HasLifecycleCallbacks
 */
class TaskList
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
     *      minMessage = "Task list name requires a length of {{ limit }}",
     *      maxMessage = "Task list name max length is length {{ limit }}"
     * )
     * @ORM\Column(type="string", length=255)
     * @Groups({"task_list","task"})
     */
    private $name;

    /**
     * @ORM\Column(type="integer")
     * @Groups({"task_list","task"})
     */
    private $sort;

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
     * @ORM\OneToMany(targetEntity=Task::class, mappedBy="taskList", orphanRemoval=true)
     * @Groups({"task_list"})
     */
    private $tasks;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="taskLists")
     */
    private $owner;

    public function __construct(string $name = '', int $sort = 0)
    {
        $this->tasks = new ArrayCollection();
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

    public function getSort(): ?int
    {
        return $this->sort;
    }

    public function setSort(int $sort): self
    {
        $this->sort = $sort;

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

    /**
     * @return Collection|Task[]
     */
    public function getTasks(): Collection
    {
        return $this->tasks;
    }

    public function addTask(Task $task): self
    {
        if (!$this->tasks->contains($task)) {
            $this->tasks[] = $task;
            $task->setTaskList($this);
        }

        return $this;
    }

    public function removeTask(Task $task): self
    {
        if ($this->tasks->removeElement($task)) {
            // set the owning side to null (unless already changed)
            if ($task->getTaskList() === $this) {
                $task->setTaskList(null);
            }
        }

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

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): self
    {
        $this->owner = $owner;

        return $this;
    }
}
