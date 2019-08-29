<?php

namespace App\Services;

use App\Transformer\TaskTransformer;
use App\DTO\TaskDTO;
use App\Entity\Project;
use App\Entity\Status;
use App\Entity\Task;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Repository\StatusRepository;
use App\Repository\ProjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class TaskHandler
{
    /** @var UserRepository */
    private $userRepository;

    /** @var ProjectRepository */
    private $projectRepository;

    /** @var StatusRepository */
    private $statusRepository;

    /** @var EntityManagerInterface */
    private $em;

    /** @var ValidatorInterface */
    private $validator;

    /** @var UserTransformer */
    private $transformer;

    /** @var TaskTransformer */
    private $taskTransformer;

    public function __construct(
        EntityManagerInterface $em,
        ValidatorInterface $validator,
        UserTransformer $transformer,
        TaskTransformer $taskTransformer
    ) {
        $this->em = $em;
        $this->userRepository = $em->getRepository(User::class);
        $this->projectRepository = $em->getRepository(Project::class);
        $this->statusRepository = $em->getRepository(Status::class);
        $this->validator = $validator;
        $this->transformer = $transformer;
        $this->taskTransformer = $taskTransformer;
    }

    public function updateTask(TaskDTO $dto, ?Task $task = null): ConstraintViolationListInterface
    {
        $group = $task === null ? 'TaskAdd' : 'TaskEdit';
        $task = $this->taskTransformer->transformDTOToEntity($dto, $task);

        $errors = $this->validator->validate($task);

        $dtoErrors = $this->validator->validate($dto, null, [$group]);
        foreach ($dtoErrors as $error) {
            $errors->add($error);
        }

        $usersTaskErrors = $this->updateUsersTask($dto, $task);
        foreach ($usersTaskErrors as $error) {
            $errors->add($error);
        }

        $projectTasksError = $this->updateTaskProject($dto, $task);
        foreach ($projectTasksError as $error) {
            $errors->add($error);
        }

        $statusTaskError = $this->updateStatusTask($dto, $task);
        foreach ($statusTaskError as $error) {
            $errors->add($error);
        }

        if ($errors->count() === 0) {
            $this->em->persist($task);
            $this->em->flush();
        }

        return $errors;
    }

    public function getList(Task $task): array
    {
        $listUsers = [];
        foreach ($task->getUsers() as $user) {
            $userDTO = $this->transformer->transformEntityToDTO($user);
            $listUsers[] = $userDTO;
        }
        $arr[] = [
            'id' => $task->getId(),
            'title' => $task->getTitle(),
            'description' => $task->getDescription(),
            'status' => $task->getStatus()->getId(),
            'users' => $listUsers,
        ];

        return $arr;
    }

    private function updateTaskProject(TaskDTO $dto, Task $task): array
    {
        $errors = [];
        $project = $this->projectRepository->find($dto->project);
        if (!$project) {
            $errors[] =
                new ConstraintViolation(
                    \sprintf('Project %s not found', $project),
                    '',
                    [],
                    $project,
                    'project',
                    $project
                );
        } else {
            $task->setProject($project);
        }

        return $errors;
    }

    private function updateStatusTask(TaskDTO $dto, Task $task): array
    {
        $errors = [];
        $status = $this->statusRepository->find($dto->status);
        if (!$status) {
            $errors[] =
                new ConstraintViolation(
                    \sprintf('Status %s not found', $status),
                    '',
                    [],
                    $status,
                    'status',
                    $status
                );
        } else {
            $task->setStatus($status);
        }

        return $errors;
    }

    public function updateTaskStatus(Task $task, Status $status)
    {
        $task->setStatus($status);
        $this->em->persist($status);
        $this->em->flush();
    }

    private function updateUsersTask(TaskDTO $dto, Task $task): array
    {
        $errors = [];
        $task->clearUsersTask();

        foreach ($dto->users as $userId) {
            $userEntity = $this->userRepository->find($userId);

            if (!$userEntity) {
                $errors[] =
                    new ConstraintViolation(
                        \sprintf('User %s not found', $userId),
                        '',
                        [],
                        $userId,
                        'users',
                        $userId
                    );
                continue;
            }
            $task->addUser($userEntity);
        }

        return $errors;
    }
}
