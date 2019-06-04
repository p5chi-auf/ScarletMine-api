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

        foreach ($data['roles'] as $id) {
            $repository = $em->getRepository(Role::class);
            $roleEntity = $repository->findOneBy(['id' => $id]);
            if ($roleEntity === null) {
                return new JsonResponse(null, Response::HTTP_BAD_REQUEST);
            }
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

        $user->clearUserRole();

        foreach ($data['roles'] as $id) {
            $repository = $em->getRepository(Role::class);
            $roleEntity = $repository->findOneBy(['id' => $id]);
            if ($roleEntity === null) {
                return new JsonResponse(['error' => 'RoleUser not found'], Response::HTTP_BAD_REQUEST);
            }

            $user->addUserRole($roleEntity);
        }

        foreach($user->getProjectRole() as $role) {

            $em->remove($role);
        }
        $user->clearProjectRole();
        $em->flush();

        $repositoryProject = $em->getRepository(Project::class);
        $repositoryRole = $em->getRepository(RoleProject::class);
        foreach ($data['projectRoles'] as $projectId => $roleId) {
            $roleEntity = $repositoryRole->find($roleId);
            if ($roleEntity === null) {
                return new JsonResponse(['error' => 'RoleProject '.$roleId.' not found'], Response::HTTP_BAD_REQUEST);
            }

            $projectEntity = $repositoryProject->find($projectId);
            if ($projectEntity === null) {
                return new JsonResponse(['error' => 'Project not found'], Response::HTTP_BAD_REQUEST);
            }
            $userProjectRole = new UserProjectRole();
            $userProjectRole->setProject($projectEntity);
            $userProjectRole->setUser($user);
            $userProjectRole->setProjectRole($roleEntity);
            $em->persist($userProjectRole);

            $user->addProjectRole($userProjectRole);
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
