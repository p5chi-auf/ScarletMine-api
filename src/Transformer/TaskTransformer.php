<?php

namespace App\Transformer;

use App\DTO\TaskDTO;
use App\Entity\Task;
use Symfony\Component\Security\Core\Security;

class TaskTransformer
{
    /**
     * @var Security
     */
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function transformEntityToDTO(Task $task): TaskDTO
    {
        $taskDTO = new TaskDTO();
        $taskDTO->id = $task->getId();
        $taskDTO->title = $task->getTitle();
        $taskDTO->description = $task->getDescription();
        $taskDTO->users = $task->getUsers();
        $taskDTO->status = $task->getStatus();
        $taskDTO->project = $task->getProject();
        $taskDTO->createdBy = $task->getCreatedBy();
        $taskDTO->createdAt = $task->getCreatedAt();

        return $taskDTO;
    }

    public function transformDTOToEntity(TaskDTO $dto, ?Task $task = null): Task
    {
        if ($task === null) {
            $task = new Task();
            $task->setCreatedAt(new \DateTime());
            $task->setCreatedBy($this->security->getUser());
        } else {
            $task->setUpdatedAt(new \DateTime());
            $task->setUpdatedBy($this->security->getUser());
        }
        $task->setTitle($dto->title);
        $task->setDescription($dto->description);

        return $task;
    }
}
