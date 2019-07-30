<?php


namespace App\Services;

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
        Security $security
    ) {
        $this->em = $em;
        $this->userRepository = $em->getRepository(User::class);
        $this->projectRepository = $em->getRepository(Project::class);
        $this->statusRepository = $em->getRepository(Status::class);
        $this->validator = $validator;
        $this->security = $security;
    }

    public function updateTask(array $data, Task $task): ConstraintViolationListInterface
    {
        $task->setTitle(trim($data['title']));
        $task->setDescription($data['description']);
        $task->setCreatedBy($this->security->getUser());
        $task->setCreatedAt(new \DateTime());
        $task->setUpdatedAt(new \DateTime());

        $relationErrors = [];

        $task->clearUsersTask();

        foreach ($data['users'] as $userId) {
            $userEntity = $this->userRepository->find($userId);

            if (!$userEntity) {
                $relationErrors[] =
                    new ConstraintViolation(
                        \sprintf('User %s not found', $userId),
                        '',
                        [],
                        $userId,
                        'user',
                        $userId
                    );
                continue;
            }
            $task->addUser($userEntity);
        }

        $project = $this->projectRepository->find($data['project']);
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

        $status = $this->statusRepository->find($data['status']);
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
}
