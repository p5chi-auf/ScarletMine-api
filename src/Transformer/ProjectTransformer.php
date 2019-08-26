<?php

namespace App\Transformer;

use App\DTO\ProjectDTO;
use App\Entity\Project;

class ProjectTransformer
{
    public function transformDTOToEntity(ProjectDTO $dto, ?Project $project): Project
    {
        if ($project === null) {
            $project = new Project();
            $project->setName($dto->name);
        }

        return $project;
    }

    public function transformEntityToDTO(Project $project): ProjectDTO
    {
        $projectDTO = new ProjectDTO();
        $projectDTO->id = $project->getId();
        $projectDTO->name = $project->getName();

        return $projectDTO;
    }
}
