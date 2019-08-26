<?php

namespace App\Controller;

use App\DTO\TaskDTO;
use App\Entity\Status;
use App\Entity\Task;
use App\Serializer\ValidationErrorSerializer;
use App\Services\TaskHandler;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class TaskController extends AbstractController
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var TaskHandler
     */
    private $handler;

    /**
     * @var ValidationErrorSerializer
     */
    private $validationErrorSerializer;

    public function __construct(
        TaskHandler $handler,
        SerializerInterface $serializer,
        ValidationErrorSerializer $validationErrorSerializer
    )
    {
        $this->handler = $handler;
        $this->serializer = $serializer;
        $this->validationErrorSerializer = $validationErrorSerializer;
    }

    /**
     * @Route("/api/task", name="task_add", methods={"POST"})
     */
    public function addTask(Request $request): JsonResponse
    {
        $data = $request->getContent();

        /** @var DeserializationContext $context */
        $context = DeserializationContext::create()->setGroups(['TaskAdd']);

        $addTaskDTO = $this->serializer->deserialize(
            $data,
            TaskDTO::class,
            'json',
            $context
        );

        $errors = $this->handler->updateTask($addTaskDTO);
        if ($errors->count()) {
            return new JsonResponse(
                [
                    'code' => Response::HTTP_BAD_REQUEST,
                    'message' => 'Bad Request',
                    'errors' => $this->validationErrorSerializer->serialize($errors),
                ],
                Response::HTTP_BAD_REQUEST
            );
        }

        return new JsonResponse(['message' => 'Task added successfully'], Response::HTTP_OK);
    }

    /**
     * @Route("/api/task/{task}", name="task_edit", methods={"POST"})
     */
    public function editTask(Request $request, Task $task): JsonResponse
    {
        $data = $request->getContent();

        /** @var DeserializationContext $context */
        $context = DeserializationContext::create()->setGroups(['TaskEdit']);

        $editTaskDTO = $this->serializer->deserialize(
            $data,
            TaskDTO::class,
            'json',
            $context
        );

        $errors = $this->handler->updateTask($editTaskDTO, $task);
        if ($errors->count()) {
            return new JsonResponse(
                [
                    'code' => Response::HTTP_BAD_REQUEST,
                    'message' => 'Bad Request',
                    'errors' => $this->validationErrorSerializer->serialize($errors),
                ],
                Response::HTTP_BAD_REQUEST
            );
        }

        return new JsonResponse(['message' => 'Task successfully edited!'], Response::HTTP_OK);
    }

    /**
     * @Route("/api/task/{task}", name="Task_List", methods={"Get"})
     */
    public function listUser(Task $task): JsonResponse
    {
        $list = $this->handler->getList($task);

        return new JsonResponse($list);
    }

    /**
     * @Route("/api/task/{task}/{status}", name="Status_Edit", methods={"POST"})
     */

    public function editStatus(Task $task, Status $status): JsonResponse
    {
        $this->handler->updateTaskStatus($task, $status);

        return new JsonResponse(['message' => 'Status successfully edited!'], Response::HTTP_OK);
    }
}
