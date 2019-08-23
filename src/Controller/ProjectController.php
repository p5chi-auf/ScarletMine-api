<?php

namespace App\Controller;

use App\DTO\ProjectDTO;
use App\Entity\Project;
use App\Serializer\ValidationErrorSerializer;
use App\Services\ProjectHandler;
use JMS\Serializer\SerializerInterface;
use JMS\Serializer\DeserializationContext;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProjectController extends AbstractController
{
    /**
     * @var ProjectHandler
     */
    private $handler;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var ValidationErrorSerializer
     */
    private $validationErrorSerializer;

    public function __construct(
        ProjectHandler $handler,
        SerializerInterface $serializer,
        ValidationErrorSerializer $validationErrorSerializer
    )
    {
        $this->handler = $handler;
        $this->serializer = $serializer;
        $this->validationErrorSerializer = $validationErrorSerializer;
    }

    /**
     * @Route("/api/projects", name="project_add", methods={"POST"})
     */
    public function addAction(Request $request): JsonResponse
    {
        $data = $request->getContent();

        /** @var DeserializationContext $context */
        $context = DeserializationContext::create()->setGroups(array('ProjectAdd'));

        $addProjectDTO = $this->serializer->deserialize(
            $data,
            ProjectDTO::class,
            'json',
            $context
        );

        $errors = $this->handler->updateProject($addProjectDTO);
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

        return new JsonResponse(['message' => 'Project added successfully'], Response::HTTP_OK);
    }

    /**
     * @Route("/api/projects/{project}", name="project_edit", methods={"POST"})
     */
    public function editAction(Request $request, Project $project): JsonResponse
    {
        $data = $request->getContent();

        /** @var DeserializationContext $context */
        $context = DeserializationContext::create()->setGroups(array('ProjectEdit'));

        $editProjectDTO = $this->serializer->deserialize(
            $data,
            ProjectDTO::class,
            'json',
            $context
        );

        $errors = $this->handler->updateProject($editProjectDTO, $project);
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

        return new JsonResponse(['message' => 'Project successfully edited!'], Response::HTTP_OK);
    }

    /**
     * @Route("/api/projects", name="project_list", methods={"GET"})
     */
    public function listAction(): JsonResponse
    {
        $list = $this->handler->getList();

        return new JsonResponse($list);
    }

    /**
     * @Route("/api/projects/{project}", name="project_delete", methods={"DELETE"})
     */
    public function deleteAction(Project $project): JsonResponse
    {
        $this->handler->delete($project);

        return new JsonResponse(null, Response::HTTP_OK);
    }
}
