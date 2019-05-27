<?php
//public function addUserRole(Role $role): void
//{
//    $this->userRoles->contains($role)
//    }


//        foreach ($data['roles'] as $role){
//        $repository = $em->getRepository(Role::class);
//        $existing = $repository->findOneBy(['role' => $role]);
//        $user->addUserRole($existing);
//        }














////
////
////namespace App\Entity;
////
////
////class ProjectRole
////{
////
////}
///
//* @ORM\Entity(repositoryClass="App\Repository\ActivityRepository")
//* @Serializer\ExclusionPolicy("all")
//*/
//class Activity
//{
//    public const STATUS_NEW = 0;
//    public const STATUS_FINISHED = 1;
//    public const STATUS_CLOSED = 2;
//
///
//* @ORM\Id()
//* @ORM\GeneratedValue()
//* @ORM\Column(type="integer")
//* @Serializer\Expose()
//* @Groups({"ActivityList", "ActivityDetails"})
//     */
//    protected $id;
//
//    /
//     * @ORM\Column(type="string")
//* @Serializer\Expose()
//* @Groups({"ActivityList", "ActivityDetails"})
//     */
//    private $name;
//
//    /
//     * @ORM\Column(type="text")
//* @Serializer\Expose()
//* @Groups({"ActivityDetails"})
//     */
//    private $description;
//
//    /
//     * @ORM\Column(type="datetime")
//* @Serializer\Expose()
//* @Groups({"ActivityDetails"})
//     */
//    private $applicationDeadline;
//
//    /
//     * @ORM\Column(type="datetime")
//* @Serializer\Expose()
//* @Groups({"ActivityDetails"})
//     */
//    private $finalDeadline;
//
//    /
//     * @ORM\Column(type="integer")
//* @Serializer\Expose()
//* @Groups({"ActivityList", "ActivityDetails"})
//     */
//    private $status = self::STATUS_NEW;
//
//    /
//     * @ORM\ManyToOne(targetEntity="User")
//* @Serializer\Expose()
//* @Groups({"ActivityDetails"})
//     */
//    private $owner;
//
//    /
//     * @ORM\Column(type="datetime")
//* @Serializer\Expose()
//* @Groups({"ActivityDetails"})
//     */
//    private $createdAt;
//
//    /
//     * @ORM\Column(type="datetime")
//* @Serializer\Expose()
//* @Groups({"ActivityDetails"})
//     */
//    private $updatedAt;
//
//    /
//     * @var Collection|Technology[]
//* @ORM\ManyToMany(targetEntity="Technology")
//* @Serializer\Expose()
//* @Groups({"ActivityDetails"})
//     */
//    protected $technologies;
//
//    /
//     * @var Collection|Type[]
//* @ORM\ManyToMany(targetEntity="Type")
//* @Serializer\Expose()
//* @Groups({"ActivityDetails"})
//     */
//    protected $types;
//
//
//class User
//{
//    public const SENIORITY_JUNIOR = 0;
//    public const SENIORITY_MIDDLE = 1;
//    public const SENIORITY_SENIOR = 2;
//
///
//* @ORM\Id()
//* @ORM\GeneratedValue()
//* @ORM\Column(type="integer")
//* @Serializer\Expose()
//* @Groups({"ActivityDetails"})
//     */
//    protected $id;
//
//    /
//     * @ORM\Column(type="string")
//* @Serializer\Expose()
//*/
//    private $username;
//
//    /
//     * @ORM\Column(type="string")
//*/
//    private $password;
//
//    /
//     * @ORM\Column(type="string")
//* @Serializer\Expose()
//*/
//    private $position;
//
//    /
//     * @ORM\Column(type="integer")
//* @Serializer\Expose()
//*/
//    private $seniority = self::SENIORITY_JUNIOR;
//
//    /
//     * @ORM\Column(type="string")
//* @Serializer\Expose()
//*/
//    private $name;
//
//    /
//     * @ORM\Column(type="string")
//* @Serializer\Expose()
//*/
//    private $surname;
//
//    /
//     * @ORM\Column(type="datetime")
//* @Serializer\Expose()
//*/
//    private $createdAt;
//
//    /
//     * @ORM\Column(type="datetime")
//* @Serializer\Expose()
//*/
//    private $updatedAt;
//
//    /
//     * @var Collection|Technology[]
//* @ORM\ManyToMany(targetEntity="Technology")
//* @Serializer\Expose()
//*/
//    protected $technologies;
