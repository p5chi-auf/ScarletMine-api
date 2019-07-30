<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @ORM\Table(name="users")
 * @UniqueEntity("username")
 */
class User implements UserInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var string
     * @Assert\NotBlank(groups={"Registration","Profile"})
     * @Assert\Regex(pattern="/^[a-zA-Z0-9_]+$/", groups={"Registration","Profile"})
     *
     * @ORM\Column(type="string", length=255)
     * @ORM\OneToMany(targetEntity="UserProjectRole", mappedBy="user")
     */
    private $username;

    /**
     * @var string
     * @Assert\NotBlank
     *
     * @ORM\Column(type="string", length=255)
     */
    private $fullName;

    /**
     * @var string|null
     * @Assert\NotBlank
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $password;

    /**
     * @var ArrayCollection|Role[]
     * @ORM\ManyToMany(targetEntity="Role")
     * @ORM\JoinTable(
     *     name="users_roles",
     *     joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="role_id", referencedColumnName="id")}
     * )
     */
    private $userRoles;

    /**
     * @var UserProjectRole[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="UserProjectRole", mappedBy="user")
     */
    private $projectRoles;

    /**
     * @ORM\OneToMany(targetEntity="Task", mappedBy="createdBy")
     */
    private $taskCreatedBy;

    public function __construct()
    {
        $this->userRoles = new ArrayCollection();
        $this->projectRoles = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getFullName(): ?string
    {
        return $this->fullName;
    }

    public function setFullName(string $fullName): self
    {
        $this->fullName = $fullName;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getRoles()
    {
        return [];
    }

    /**
     * @return Role[]|ArrayCollection
     */
    public function getUserRoles()
    {
        return $this->userRoles;
    }

    public function addUserRole(Role $role): void
    {
        if (!$this->userRoles->contains($role)) {
            $this->userRoles->add($role);
        }
    }

    public function clearUserRole(): void
    {
        $this->userRoles->clear();
    }

    public function getSalt()
    {
    }

    public function eraseCredentials()
    {
    }

    /**
     * @return UserProjectRole[]|ArrayCollection
     */
    public function getProjectRole()
    {
        return $this->projectRoles;
    }

    public function addProjectRole(UserProjectRole $role): void
    {
        if (!$this->projectRoles->contains($role)) {
            $this->projectRoles->add($role);
        }
    }

    public function clearProjectRole(): void
    {
        $this->projectRoles->clear();
    }

    public function getTaskCreatedBy()
    {
        return $this->taskCreatedBy;
    }
}
