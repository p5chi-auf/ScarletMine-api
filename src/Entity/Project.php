<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Tests\Mapping\Loader\AbstractStaticLoader;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

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

    /**
     * @ORM\OneToMany(targetEntity="Task", mappedBy="project")
     */
    private $tasks;

    public function __construct()
    {
        $this->tasks = new ArrayCollection();
    }

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

    /**
     * @return Collection|Task[]
     */
    public function getTask(): Collection
    {
        return $this->tasks;
    }

    public function addTask(Task $tasks): self
    {
        if (!$this->tasks->contains($tasks)) {
            $this->tasks[] = $tasks;
            $tasks->setProject($this);
        }

        return $this;
    }

    public function removeTask(Task $tasks): self
    {
        if ($this->tasks->contains($tasks)) {
            $this->tasks->removeElement($tasks);
            if ($tasks->getProject() === $this) {
                $tasks->setProject(null);
            }
        }

        return $this;
    }
}
