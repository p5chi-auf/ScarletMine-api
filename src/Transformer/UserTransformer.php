<?php

namespace App\Transformer;

use App\DTO\UserDTO;
use App\Entity\User;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserTransformer
{
    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    public function transformDTOToEntity(UserDTO $dto, ?User $user = null): User
    {
        if ($user === null) {
            $user = new User();
            $user->setUsername($dto->username);
            $user->setEmail($dto->email);
        }

        $user->setfullName($dto->fullName);
        if (!empty($dto->password)) {
            $user->setPassword($this->passwordEncoder->encodePassword($user, $dto->password));
        }

        return $user;
    }

    public function transformEntityToDTO(User $user): UserDTO
    {
        $roles = [];
        foreach ($user->getUserRoles() as $role) {
            $roles[] = $role->getRole();
        }

        $projectRoles = [];
        foreach ($user->getProjectRoles() as $userProjectRole) {
            $projectRoles[] = [
                'projectID' => $userProjectRole->getProject()->getId(),
                'roleID' => $userProjectRole->getProjectRole()->getId(),
            ];
        }

        $userDTO = new UserDTO();
        $userDTO->id = $user->getId();
        $userDTO->username = $user->getUsername();
        $userDTO->fullName = $user->getFullName();
        $userDTO->email = $user->getEmail();
        $userDTO->role = $roles;
        $userDTO->projectRoles = $projectRoles;

        return $userDTO;
    }

}
