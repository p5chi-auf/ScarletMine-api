<?php


namespace App\Controller;

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
        $data = \json_decode($request->getContent(), true);

        $isValid = $this->handler->validate($data);
        if (!$isValid) {
            return new JsonResponse(null, Response::HTTP_BAD_REQUEST);
        }

        $task = $this->handler->save($data);

        return new JsonResponse($task->getId());

    }

}

