<?php

namespace App\Services;

use App\DTO\UserDTO;
use App\Entity\RoleProject;
use App\Entity\User;
use App\Entity\UserProjectRole;
use App\Entity\Role;
use App\Entity\Project;
use App\Repository\ProjectRepository;
use App\Repository\RoleProjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\UserRepository;
use App\Repository\RoleRepository;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserHandler
{
    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var RoleRepository
     */
    private $roleRepository;

    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var ProjectRepository
     */
    private $projectRepository;

    /**
     * @var RoleProjectRepository
     */
    private $roleProjectRepository;

    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    public function __construct(
        EntityManagerInterface $em,
        ValidatorInterface $validator,
        UserPasswordEncoderInterface $passwordEncoder
    )
    {
        $this->em = $em;
        $this->validator = $validator;
        $this->passwordEncoder = $passwordEncoder;
        $this->userRepository = $this->em->getRepository(User::class);
        $this->roleRepository = $this->em->getRepository(Role::class);
        $this->projectRepository = $this->em->getRepository(Project::class);
        $this->roleProjectRepository = $this->em->getRepository(RoleProject::class);
    }

    public function updateUser(UserDTO $dto, User $user): ConstraintViolationListInterface
    {
        if ($user->getId() === null) {
            $user->setUsername($dto->username);
            $user->setPassword($dto->password);

        }

        $user->setfullName($dto->fullName);
        if (!empty($dto->password)) {
            $user->setPassword($this->passwordEncoder->encodePassword($user, $dto->password));
        }
        $userRolesErrors = $this->updateUserRoles($dto, $user);
        $userProjectRolesErrors = $this->updateUserProjectRole($dto, $user);
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
        $users = $this->userRepository->findAll();
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

    public function updateUserRoles(UserDTO $dto, User $user): array
    {
        $errors = [];
        $user->clearUserRole();
        foreach ($dto->role as $id) {
            $roleEntity = $this->roleRepository->find($id);
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

    public function updateUserProjectRole(UserDTO $dto, User $user): array
    {
        $errors = [];
        foreach ($user->getProjectRole() as $role) {
            $this->em->remove($role);
        }
        $user->clearProjectRole();

        foreach ($dto->projectRoles as $projectId => $roleId) {
            $roleEntity = $this->roleProjectRepository->find($roleId);
            $projectEntity = $this->projectRepository->find($projectId);
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
