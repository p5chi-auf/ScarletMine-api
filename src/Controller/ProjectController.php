<?php

namespace App\Controller;

use App\Entity\Project;
use App\Services\ProjectHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProjectController extends AbstractController
{
    private $handler;

    public function __construct(ProjectHandler $handler)
    {
        $this->handler = $handler;
    }

    /**
     * @Route("/api/projects", name="project_add", methods={"POST"})
     */
    public function addAction(Request $request): JsonResponse
    {
        $project = new Project();
        $data = \json_decode($request->getContent(), true);

        $errors = $this->handler->updateProject($data, $project);
        if ($errors->count()) {
            return new JsonResponse(['errors' => (string)$errors], Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse($project->getId());
    }

    /**
     * @Route("/api/projects/{project}", name="project_edit", methods={"POST"})
     */
    public function editAction(Request $request, Project $project): JsonResponse
    {
        $data = \json_decode($request->getContent(), true);

        $errors = $this->handler->updateProject($data, $project);

        if ($errors->count()) {
            return new JsonResponse(['errors' => (string)$errors], Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse($project->getId());
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
