<?php

namespace App\DTO;

use JMS\Serializer\Annotation as Serializer;
use JMS\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

class ProjectDTO
{
    /**
     * Project ID
     * @var integer
     * @Serializer\Type("integer")
     * @Serializer\Expose()
     * @Serializer\SerializedName("ID")
     */
    public $id;

    /**
     * The name of Project
     * @var string
     * @Serializer\Type("string")
     * @Assert\NotBlank(groups={"ProjectAdd", "ProjectEdit"})
     * @Assert\Length(
     *      min = 2,
     *      max = 25,
     *      minMessage = "Your project name must be at least 2 characters long",
     *      maxMessage = "Your project name cannot be longer than 25 characters",
     *     groups={"ProjectAdd", "ProjectEdit"}
     * )
     * @Serializer\Expose()
     * @Groups({"ProjectAdd", "ProjectEdit"})
     * @Serializer\SerializedName("name")
     */
    public $name;
}