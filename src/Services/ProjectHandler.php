<?php

namespace App\Services;

use App\Entity\Project;
use Doctrine\ORM\EntityManagerInterface;

class ProjectHandler
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function validate($data, ?Project $project = null): bool
    {
        $repository = $this->em->getRepository(Project::class);
        $existing = $repository->findOneBy(['name' => $data['name']]);
        if (empty($data['name'])) {
            return false;
        }

        if ($existing && $project === null) {
            return false;
        }

        if ($project !== null && $existing && $existing !== $project) {
            return false;

        }

        return true;
    }

    public function save($data, ?Project $project = null): Project
    {
        if ($project === null) {
            $project = new Project();
        }

        $project->setName($data['name']);
        $this->em->persist($project);
        $this->em->flush();

        return $project;
    }

    public function getList(): array
    {
        $repository = $this->em->getRepository(Project::class);
        $projects = $repository->findAll();
        $arr = [];
        foreach ($projects as $proj) {
            $arr [] = [
                'id' => $proj->getId(),
                'name' => $proj->getName(),
            ];
        }

        return $arr;
    }

    public function delete(Project $project): void
    {
        $this->em->remove($project);
        $this->em->flush();
    }

}
