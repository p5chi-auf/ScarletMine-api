<?php

namespace App\DTO;

use JMS\Serializer\Annotation as Serializer;
use JMS\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

class TaskDTO
{
    /**
     * Users
     * @var array
     * @Assert\NotBlank(groups={"TaskAdd"})
     * @Serializer\Type("array")
     * @Serializer\Expose()
     * @Serializer\SerializedName("users")
     * @Groups({"TaskAdd"})
     */
    public $users;

    /**
     * Task ID
     * @var integer
     * @Serializer\Type("integer")
     * @Serializer\Expose()
     * @Serializer\SerializedName("ID")
     */
    public $id;

    /**
     * The title for Task
     * @var string
     * @Serializer\Type("string")
     * @Assert\NotBlank(groups={"TaskAdd"})
     * @Assert\Length(
     *      min = 4,
     *      max = 50,
     *      minMessage = "Your title must be at least 4 characters long",
     *      maxMessage = "Your title cannot be longer than 50 characters",
     *     groups={"TaskAdd"}
     * )
     * @Serializer\Expose()
     * @Groups({"TaskAdd"})
     * @Serializer\SerializedName("title")
     */
    public $title;

    /**
     * Description for Task
     * @var string
     * @Serializer\Type("string")
     * @Serializer\Expose()
     * @Groups({"TaskAdd"})
     * @Serializer\SerializedName("description")
     */
    public $description;

    /**
     * Task status
     * @var integer
     * @Assert\NotNull(groups={"TaskAdd"})
     * @Serializer\Type("integer")
     * @Serializer\Expose()
     * @Serializer\SerializedName("status")
     * @Groups({"TaskAdd"})
     */
    public $status;

    /**
     * Project Task
     * @var integer
     * @Assert\NotNull(groups={"TaskAdd"})
     * @Serializer\Type("integer")
     * @Serializer\Expose()
     * @Serializer\SerializedName("project")
     * @Groups({"TaskAdd"})
     */
    public $project;

    /**
     * Task was created at...
     * @var \DateTime
     * @Serializer\Type("DateTime")
     * @Serializer\Expose()
     * @Serializer\SerializedName("createdAt")
     * @Groups({"TaskAdd"})
     */
    public $createdAt;

    /**
     * Task was updated at...
     * @var \DateTime
     * @Serializer\Type("DateTime")
     * @Serializer\Expose()
     * @Serializer\SerializedName("updatedAt")
     * @Groups({"TaskEdit"})
     */
    public $updatedAt;

    /**
     * Task was created by...
     * @var string
     * @Serializer\Type("string")
     * @Serializer\Expose()
     * @Serializer\SerializedName("createdBy")
     * @Groups({"TaskAdd"})
     */
    public $createdBy;

    /**
     * Task was updated by...
     * @var string
     * @Serializer\Type("string")
     * @Serializer\Expose()
     * @Serializer\SerializedName("updatedBy")
     * @Groups({"TaskEdit"})
     */
    public $updatedBy;
}
