<?php


namespace App\Services;

use App\Entity\Task;
use App\Repository\UserRepository;
use App\Repository\ProjectRepository;
use Doctrine\ORM\EntityManagerInterface;


class TaskHandler
{
    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var ProjectRepository
     */
    private $projectRepository;

    private $em;

    public function __construct(
        ProjectRepository $projectRepository,
        UserRepository $userRepository,
        EntityManagerInterface $em
    ) {

        $this->userRepository = $userRepository;
        $this->projectRepository = $projectRepository;
        $this->em = $em;
    }

    public function validate($data, ?Task $task = null): bool
    {
        $repository = $this->em->getRepository(Task::class);
        $existing = $repository->findOneBy(['title' => $data['title']]);

        if (empty($data['title']) || empty($data['description']) || empty($data['users'])) {
            return false;
        }

        if ($existing) {
            return false;
        }

        if ($task !== null) {
            return false;
        }

        return true;

    }

    public function save($data, ?Task $task = null): Task
    {
        if ($task === null) {
            $task = new Task();
        }
        $task->setTitle($data['title']);
        $task->setDescription($data['description']);
        $task->setStatus($data['status']);
        foreach ($data['users'] as $user) {
            $userToAdd = $this->userRepository->find($user);
            $task->addUser($userToAdd);
        }
        $projectToAdd = $this->projectRepository->find($data['project']);
        $task->setProject($projectToAdd);

        return $task;
    }

}
