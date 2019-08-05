<?php


namespace App\Services;

use App\Transformer\UserTransformer;
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
use Symfony\Component\Security\Core\Security;


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

    /** @var Security */
    private $security;

    public function __construct(
        EntityManagerInterface $em,
        ValidatorInterface $validator,
        Security $security,
        UserTransformer $transformer
    ) {
        $this->em = $em;
        $this->userRepository = $em->getRepository(User::class);
        $this->projectRepository = $em->getRepository(Project::class);
        $this->statusRepository = $em->getRepository(Status::class);
        $this->validator = $validator;
        $this->security = $security;
        $this->transformer = $transformer;
    }

    public function updateTask(TaskDTO $dto, Task $task): ConstraintViolationListInterface
    {
        $task->setTitle($dto->title);
        $task->setDescription($dto->description);
        $task->setCreatedBy($this->security->getUser());
        $task->setCreatedAt(new \DateTime());
        $task->setUpdatedAt(new \DateTime());

        $relationErrors = $this->updateUsersTask($dto, $task);

        $project = $this->projectRepository->find($dto->project);
        if (!$project) {
            $relationErrors[] =
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

        $status = $this->statusRepository->find($dto->status);
        if (!$status) {
            $relationErrors[] =
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

        $errors = $this->validator->validate($task);

        foreach ($relationErrors as $error) {
            $errors->add($error);
        }

        if ($errors->count() === 0) {
            $this->em->persist($task);
            $this->em->flush();
        }

        return $errors;
    }

    public function updateUsersTask(TaskDTO $dto, Task $task): array
    {
        $relationErrors = [];
        $task->clearUsersTask();

        foreach ($dto->users as $userId) {
            $userEntity = $this->userRepository->find($userId);

            if (!$userEntity) {
                $relationErrors[] =
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

        return $relationErrors;
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
            'description' => $task->getDescription(),
            'status' => $task->getStatus()->getId(),
            'users' => $listUsers,
        ];

        return $arr;
    }

}
