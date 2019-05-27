<?php

namespace App\Controller;

use App\Entity\RoleProject;
use App\Entity\User;
use App\Entity\Role;
use App\Entity\UserProjectRole;
use App\Entity\Project;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    /**
     * @Route("/api/users", name="user_add", methods={"POST"})
     */
    public function addUser(Request $request, EntityManagerInterface $em)
    {
        $data = \json_decode($request->getContent(), true);
        if (empty($data['fullName']) || empty($data['username']) || empty($data['newPassword'])) {
            return new JsonResponse(null, Response::HTTP_BAD_REQUEST);
        }
        $user = new User();
        $user->setFullName($data['fullName']);
        $user->setUsername($data['username']);
        $user->setPassword($data['newPassword']);

        foreach ($data['roles'] as $role) {
            $repository = $em->getRepository(Role::class);
            $roleEntity = $repository->findOneBy(['role' => $role]);
            $user->addUserRole($roleEntity);
        }

        $repository = $em->getRepository(User::class);
        $existing = $repository->findOneBy(['username' => $data["username"]]);
        if ($existing) {
            return new JsonResponse(null, Response::HTTP_BAD_REQUEST);
        }
        $em->persist($user);
        $em->flush();

        return new JsonResponse($user->getId());
    }

    /**
     * @Route("/api/users/{user}", name="user_edit", methods={"POST"})
     */
    public function editUser(Request $request, EntityManagerInterface $em, User $user)
    {
        $data = \json_decode($request->getContent(), true);
        if (empty($data['fullName'])) {
            return new JsonResponse(['error' => 'Empty fullName'], Response::HTTP_BAD_REQUEST);
        }
        if (!empty($data['newPassword'])) {
            $user->setPassword($data['newPassword']);
        }
        $user->setFullName($data['fullName']);
        $em->persist($user);
        $em->flush();

        return new JsonResponse($user->getUsername());
    }

    /**
     * @Route("/api/users", name="user_List", methods={"Get"})
     */
    public function listUser(EntityManagerInterface $em): JsonResponse
    {
        $repository = $em->getRepository(User::class);
        $users = $repository->findAll();
        $arr = [];
        foreach ($users as $user) {
            $roles = [];
            foreach ($user->getUserRoles() as $role) {
                $roles[] = $role->getRole();
            }

            $arr[] = [
                'id' => $user->getId(),
                'username' => $user->getUsername(),
                'fullName' => $user->getFullName(),
                'newPassword' => $user->getPassword(),
                'roles' => $roles,
            ];
        }
        return new JsonResponse($arr);
    }
}
