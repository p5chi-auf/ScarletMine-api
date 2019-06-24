<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ProjectRepository")
 * @ORM\Table(name="project")
 * @UniqueEntity("name")
 */
class Project
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
     * @Assert\NotBlank
     *
     * @ORM\Column(type="string", length=50)
     */
    private $name;

    /**
     * @var UserProjectRole[]
     *
     * @ORM\OneToMany(targetEntity="UserProjectRole", mappedBy="project")
     */
    private $users;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param int $name
     * @return Project
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }
}
