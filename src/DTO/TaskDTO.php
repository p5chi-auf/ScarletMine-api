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
     * @Assert\NotBlank(groups={"TaskAdd","TaskEdit"})
     * @Serializer\Type("array")
     * @Serializer\Expose()
     * @Serializer\SerializedName("users")
     * @Groups({"TaskAdd","TaskEdit"})
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
     * @Assert\NotBlank(groups={"TaskAdd","TaskEdit"})
     * @Assert\Length(
     *      min = 4,
     *      max = 50,
     *      minMessage = "Your title must be at least 4 characters long",
     *      maxMessage = "Your title cannot be longer than 50 characters",
     *     groups={"TaskAdd","TaskEdit"}
     * )
     * @Serializer\Expose()
     * @Groups({"TaskAdd","TaskEdit"})
     * @Serializer\SerializedName("title")
     */
    public $title;

    /**
     * Description for Task
     * @var string
     * @Serializer\Type("string")
     * @Serializer\Expose()
     * @Groups({"TaskAdd","TaskEdit"})
     * @Serializer\SerializedName("description")
     */
    public $description;

    /**
     * Task status
     * @var integer
     * @Assert\NotNull(groups={"TaskAdd","TaskEdit"})
     * @Serializer\Type("integer")
     * @Serializer\Expose()
     * @Serializer\SerializedName("status")
     * @Groups({"TaskAdd","TaskEdit"})
     */
    public $status;

    /**
     * Project Task
     * @var integer
     * @Assert\NotNull(groups={"TaskAdd","TaskEdit"})
     * @Serializer\Type("integer")
     * @Serializer\Expose()
     * @Serializer\SerializedName("project")
     * @Groups({"TaskAdd","TaskEdit"})
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
