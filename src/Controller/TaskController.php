<?php

namespace App\Controller;

use App\Entity\Task;
use App\Services\TaskHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class TaskController extends AbstractController
{
    private $handler;

    public function __construct(TaskHandler $handler)
    {
        $this->handler = $handler;
    }

    /**
     * @Route("/api/task", name="task_add", methods={"POST"})
     */
    public function addTask(Request $request): JsonResponse
    {
        $task = new Task();
        $data = \json_decode($request->getContent(), true);
        $errors = $this->handler->updateTask($data, $task);
        if ($errors->count()) {
            return new JsonResponse(['errors' => (string)$errors], Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse($task->getId());
    }

}

