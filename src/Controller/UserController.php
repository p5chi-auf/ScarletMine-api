<?php

namespace App\Controller;

use App\Entity\User;
use App\Services\UserHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController

{
    private $handler;

    public function __construct(UserHandler $handler)
    {
        $this->handler = $handler;
    }

    /**
     * @Route("/api/users", name="user_add", methods={"POST"})
     */
    public function addUser(Request $request): JsonResponse
    {

        $user = new User();
        $data = \json_decode($request->getContent(), true);

        $errors = $this->handler->updateUser($data, $user);
        if ($errors->count()) {
            return new JsonResponse(['errors' => (string)$errors], Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse($user->getId());
    }

    /**
     * @Route("/api/users/{user}", name="user_edit", methods={"POST"})
     */
    public function editUser(Request $request, User $user): JsonResponse
    {
        $data = \json_decode($request->getContent(), true);
        $errors = $this->handler->updateUser($data, $user);
        if ($errors->count()) {
            return new JsonResponse(['errors' => (string)$errors], Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse($user->getId());

    }

    /**
     * @Route("/api/users", name="user_List", methods={"Get"})
     */
    public function listUser(): JsonResponse
    {
        $list = $this->handler->getList();

        return new JsonResponse($list);
    }
}
