<?php


namespace App\Controller;

use App\Entity\Task;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class TaskController extends AbstractController
{
    /**
     * @Route("/api/task", name="task_add", methods={"POST"})
     */
    public function addTask(Request $request, EntityManagerInterface $em)
    {
        $data = \json_decode($request->getContent(), true);
        if (empty($data['title']) || empty($data['description']) || empty($data['users'])) {
            return new JsonResponse(null, Response::HTTP_BAD_REQUEST);
        }
        $task = new Task();
        $task->setTitle($data['title']);
        $task->setDescription($data['description']);
        $task->setStatus($data['status']);
        $task->setUsers($data['users']);
        $task->setProjects($data['project']);

        $repository = $em->getRepository(Task::class);
        $em->persist($task);
        $em->flush();

        return new JsonResponse($task->getId());

    }
}
