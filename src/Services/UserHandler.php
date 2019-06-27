<?php

namespace App\Services;

use App\Entity\Project;
use App\Entity\Role;
use App\Entity\RoleProject;
use App\Entity\User;
use App\Entity\UserProjectRole;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserHandler
{
    private $em;
    private $validator;
    private $passwordEncoder;

    public function __construct(
        EntityManagerInterface $em,
        ValidatorInterface $validator,
        UserPasswordEncoderInterface $passwordEncoder
    ) {
        $this->em = $em;
        $this->validator = $validator;
        $this->passwordEncoder = $passwordEncoder;
    }

    public function updateUser($data, User $user): ConstraintViolationListInterface
    {
        if ($user->getId() === null) {
            $user->setUsername($data['username']);
        }

        $user->setFullName($data['fullName']);

        if (!empty($data['newPassword'])) {
            $user->setPassword($this->passwordEncoder->encodePassword($user, $data['newPassword']));
        }

        $userRolesErrors = $this->updateUserRoles($data, $user);
        $userProjectRolesErrors = $this->updateUserProjectRole($data, $user);

        $errors = $this->validator->validate($user);

        foreach ($userRolesErrors as $error) {
            $errors->add($error);
        }
        foreach ($userProjectRolesErrors as $error) {
            $errors->add($error);
        }

        if ($errors->count() === 0) {
            $this->em->persist($user);
            $this->em->flush();
        }

        return $errors;
    }

    public function getList(): array
    {
        $repository = $this->em->getRepository(User::class);
        $users = $repository->findAll();
        $arr = [];
        foreach ($users as $user) {
            $roles = [];
            foreach ($user->getUserRoles() as $role) {
                $roles[] = $role->getRole();
            }

            $arr[] = [
                'id' => $user->getId(),
                'username' => $user->getUsername(),
                'fullName' => $user->getFullName(),
                'newPassword' => $user->getPassword(),
                'roles' => $roles,
            ];
        }
        return $arr;
    }

    public function updateUserRoles($data, User $user): array
    {

        $errors = [];
        $user->clearUserRole();

        foreach ($data['roles'] as $id) {
            $repository = $this->em->getRepository(Role::class);
            $roleEntity = $repository->find($id);
            if (!$roleEntity) {
                $errors[] =
                    new ConstraintViolation(
                        \sprintf('Role %s not found', $id),
                        '',
                        [],
                        $id,
                        'userRoles',
                        $id
                    );

                continue;
            }
            $user->addUserRole($roleEntity);
        }

        return $errors;
    }

    public function updateUserProjectRole($data, User $user): array
    {
        $errors = [];
        foreach ($user->getProjectRole() as $role) {
            $this->em->remove($role);
        }

        $user->clearProjectRole();
        $repositoryProject = $this->em->getRepository(Project::class);
        $repositoryRole = $this->em->getRepository(RoleProject::class);

        foreach ($data['projectRoles'] as $projectId => $roleId) {
            $roleEntity = $repositoryRole->find($roleId);
            $projectEntity = $repositoryProject->find($projectId);

            if (!$projectEntity) {
                $errors[] =
                    new ConstraintViolation(
                        \sprintf('Project %s not found', $projectId),
                        '',
                        [],
                        $projectId,
                        'projectRoles',
                        $projectId
                    );

                continue;
            }

            if (!$roleEntity) {
                $errors[] =
                new ConstraintViolation(
                    \sprintf('Role Project %s not found', $roleId),
                    '',
                    [],
                    $roleId,
                    'projectRoles',
                    $roleId
                );

                continue;
            }

            $userProjectRole = new UserProjectRole();
            $userProjectRole->setProject($projectEntity);
            $userProjectRole->setUser($user);
            $userProjectRole->setProjectRole($roleEntity);
            $this->em->persist($userProjectRole);
            $user->addProjectRole($userProjectRole);
        }
        return $errors;
    }
}
