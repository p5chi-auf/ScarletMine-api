<?php

namespace App\DTO;

use App\Entity\Role;
use App\Entity\UserProjectRole;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation as Serializer;
use JMS\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

class UserDTO
{
    /**
     * User ID
     * @var integer
     * @Serializer\Type("integer")
     * @Serializer\Expose()
     * @Serializer\SerializedName("ID")
     */
    public $id;

    /**
     * The username of User
     * @var string
     * @Serializer\Type("string")
     * @Assert\NotBlank(groups={"UserAdd"})
     * @Assert\Length(
     *      min = 4,
     *      max = 50,
     *      minMessage = "Your username must be at least 4 characters long",
     *      maxMessage = "Your username cannot be longer than 50 characters",
     *     groups={"UserAdd"}
     * )
     * @Serializer\Expose()
     * @Groups({"UserAdd"})
     * @Serializer\SerializedName("username")
     */
    public $username;

    /**
     * User email
     * @var string
     * @Serializer\Type("string")
     * @Assert\NotBlank(groups={"UserAdd"})
     * @Assert\Email(
     *     message = "The email '{{ value }}' is not a valid email.",
     *     groups={"UserAdd"}
     * )
     * @Serializer\Expose()
     * @Groups({"UserAdd"})
     * @Serializer\SerializedName("email")
     */
    public $email;

    /**
     * The name of User
     * @var string
     * @Serializer\Type("string")
     * @Assert\NotBlank(groups={"UserEdit", "UserAdd"})
     * @Serializer\Expose()
     * @Groups({"UserEdit", "UserAdd"})
     * @Serializer\SerializedName("fullName")
     */
    public $fullName;

    /**
     * Password
     * @var string
     * @Serializer\Type("string")
     * @Serializer\Expose()
     * @Groups({"UserEdit", "UserAdd"})
     * @Assert\NotNull(groups={"UserAdd"})
     * @Assert\Regex(
     *     "/^\S*(?=\S{8,})(?=\S*[a-z])(?=\S*[A-Z])(?=\S*[\d])(?=\S*[-_!@#$%^&*])\S*$/",
     *     message = "Password requirements(at least):length >8, 1 uppercase, 1 lowercase, 1 digit, 1 special",
     *     groups={"UserEdit", "UserAdd"}
     * )
     * @Serializer\SerializedName("newPassword")
     */
    public $password;

    /**
     * confirmPassword
     * @var string
     * @Serializer\Type("string")
     * @Assert\NotNull(groups={"UserAdd"})
     * @Assert\EqualTo(propertyPath="password",
     *     message="Passwords do not match.",
     *     groups={"UserEdit", "UserAdd"}
     * )
     * @Serializer\Expose()
     * @Groups({"UserEdit", "UserAdd"})
     * @Serializer\SerializedName("confirmPassword")
     */
    public $confirmPassword;

    /**
     * User Roles (Role Collection)
     * @var ArrayCollection|Role[]
     * @Serializer\Type("array")
     * @Serializer\Expose()
     * @Groups({"UserAdd","UserEdit"})
     * @Serializer\SerializedName("roles")
     */
    public $role;

    /**
     * User UserProjectRole (Technology Collection)
     * @var UserProjectRole[]|ArrayCollection
     * @Serializer\Type("array")
     * @Serializer\Expose()
     * @Groups({"UserAdd","UserEdit"})
     * @Serializer\SerializedName("projectRoles")
     */
    public $projectRoles;

}
