<?php


namespace App\Transformer;


use App\DTO\TaskDTO;
use App\Entity\Task;
use Symfony\Component\Security\Core\Security;

class TaskTransformer
{

    /** @var Security */
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
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
