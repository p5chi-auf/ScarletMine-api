<?php

namespace App\Services;

use App\DTO\ProjectDTO;
use App\Entity\Project;
use App\Repository\ProjectRepository;
use App\Transformer\ProjectTransformer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProjectHandler
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var ProjectTransformer
     */
    private $transformer;

    /**
     * @var ProjectRepository
     */
    private $projectRepository;

    public function __construct(
        EntityManagerInterface $em,
        ValidatorInterface $validator,
        ProjectTransformer $transformer
    )
    {
        $this->em = $em;
        $this->validator = $validator;
        $this->transformer = $transformer;
        $this->projectRepository = $this->em->getRepository(Project::class);
    }

    public function updateProject(ProjectDTO $dto, ?Project $project = null): ConstraintViolationListInterface
    {
        $group = $project === null ? 'ProjectAdd' : 'ProjectEdit';

        $project = $this->transformer->transformDTOToEntity($dto, $project);

        $errors = $this->validator->validate($project);
        $dtoErrors = $this->validator->validate($dto, null, [$group]);

        foreach ($dtoErrors as $error) {
            $errors->add($error);
        }

        if ($errors->count() === 0) {
            $this->em->persist($project);
            $this->em->flush();
        }

        return $errors;
    }

    public function getList(): array
    {
        $projects = $this->projectRepository->findAll();
        $arr = [];
        foreach ($projects as $project) {
            $projectDTO = $this->transformer->transformEntityToDTO($project);
            $arr[] = $projectDTO;
        }

        return $arr;
    }

    public function delete(Project $project): void
    {
        $this->em->remove($project);
        $this->em->flush();
    }
}
