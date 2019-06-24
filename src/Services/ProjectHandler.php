<?php

namespace App\Services;

use App\Entity\Project;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProjectHandler
{
    private $em;
    private $validator;

    public function __construct(EntityManagerInterface $em, ValidatorInterface $validator)
    {
        $this->em = $em;
        $this->validator = $validator;
    }

    public function updateProject(array $data, Project $project): ConstraintViolationListInterface
    {
        if ($project->getId() === null) {
            $project->setName(trim($data['name']));
        }

        $errors = $this->validator->validate($project);

        if ($errors->count() === 0) {
            $this->em->persist($project);
            $this->em->flush();
        }

        return $errors;
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
