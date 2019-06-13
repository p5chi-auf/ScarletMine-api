<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class RoleProject
{
    /**
     * @var int
     *
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(type="string", length=50)
     */
    private $role;

    /**
     * @var UserProjectRole
     * @ORM\OneToMany(targetEntity="UserProjectRole", mappedBy="projectRole")
     */
    private $userProjectRole;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function getUserProjectRole(): UserProjectRole
    {
        return $this->userProjectRole;
    }

    public function setUserProjectRole(UserProjectRole $userProjectRole): RoleProject
    {
        $this->userProjectRole = $userProjectRole;

        return $this;
    }

}
