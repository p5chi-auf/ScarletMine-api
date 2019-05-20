<?php

namespace App\Controller;

use App\Entity\Project;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProjectController extends AbstractController
{
    /**
     * @Route("/api/projects", name="project_add", methods={"POST"})
     */
    public function addAction(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = \json_decode($request->getContent(), true);

        $repository = $em->getRepository(Project::class);
        $existing = $repository->findOneBy(['name' => $data['name']]);
        if ($existing) {
            return new JsonResponse(null, Response::HTTP_BAD_REQUEST);
        }
        if (empty($data['name'])) {
            return new JsonResponse(null, Response::HTTP_BAD_REQUEST);
        }
        $project = new Project();
        $project->setName($data['name']);
        $em->persist($project);
        $em->flush();

        return new JsonResponse($project->getId());
    }

    /**
     * @Route("/api/projects/{project}", name="project_edit", methods={"POST"})
     */
    public function editAction(Request $request, EntityManagerInterface $em, Project $project): JsonResponse
    {
        $data = \json_decode($request->getContent(), true);
        $repository = $em->getRepository(Project::class);
        $existing = $repository->findOneBy(['name' => $data['name']]);
        if ($existing && $existing->getId() !== $project->getId()) {
            return new JsonResponse(null, Response::HTTP_BAD_REQUEST);
        }
        if (empty($data['name'])) {
            return new JsonResponse(null, Response::HTTP_BAD_REQUEST);
        }
        $project->setName($data['name']);
        $em->persist($project);
        $em->flush();

        return new JsonResponse($project->getId());
    }

    /**
     * @Route("/api/projects", name="project_list", methods={"GET"})
     */
    public function listAction(EntityManagerInterface $em): JsonResponse
    {
        $repository = $em->getRepository(Project::class);
        $projects = $repository->findAll();
        $arr = [];
        foreach ($projects as $proj) {
            $arr [] = [
                'id' => $proj->getId(),
                'name' => $proj->getName(),
            ];
        }

        return new JsonResponse($arr);
    }

    /**
     * @Route("/api/projects/{project}", name="project_delete", methods={"DELETE"})
     */
    public function deleteAction(EntityManagerInterface $em, Project $project): JsonResponse
    {
        $em->remove($project);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_OK);
    }
}
