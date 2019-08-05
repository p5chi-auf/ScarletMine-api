<?php

namespace App\Controller;

use App\DTO\UserDTO;
use App\Entity\User;
use App\Services\UserHandler;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Serializer\ValidationErrorSerializer;

class UserController extends AbstractController
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var UserHandler
     */
    private $userHandler;

    /**
     * @var ValidationErrorSerializer
     */
    private $validationErrorSerializer;

    public function __construct(
        UserHandler $userHandler,
        SerializerInterface $serializer,
        ValidationErrorSerializer $validationErrorSerializer
    ) {
        $this->serializer = $serializer;
        $this->userHandler = $userHandler;
        $this->validationErrorSerializer = $validationErrorSerializer;
    }

    /**
     * @Route("/api/user-register", name="user_register", methods={"POST"})
     * @Route("/api/users", name="user_add", methods={"POST"})
     */
    public function addUser(Request $request): JsonResponse
    {
        $data = $request->getContent();

        /** @var DeserializationContext $context */
        $context = DeserializationContext::create()->setGroups(array('UserAdd'));

        $addUserDTO = $this->serializer->deserialize(
            $data,
            UserDTO::class,
            'json',
            $context
        );

        $errors = $this->userHandler->updateUser($addUserDTO);
        if ($errors->count()) {
            return new JsonResponse(
                [
                    'code' => Response::HTTP_BAD_REQUEST,
                    'message' => 'Bad Request',
                    'errors' => $this->validationErrorSerializer->serialize($errors),
                ],
                Response::HTTP_BAD_REQUEST
            );
        }

        return new JsonResponse(['message' => 'User added successfully'], Response::HTTP_OK);
    }

    /**
     * @Route("/api/users/{user}", name="user_edit", methods={"POST"})
     */
    public function editUser(Request $request, User $user): JsonResponse
    {
        $data = $request->getContent();

        /** @var DeserializationContext $context */
        $context = DeserializationContext::create()->setGroups(array('UserEdit'));

        $editUserDTO = $this->serializer->deserialize(
            $data,
            UserDTO::class,
            'json',
            $context
        );

        $errors = $this->userHandler->updateUser($editUserDTO, $user);
        if ($errors->count()) {
            return new JsonResponse(
                [
                    'code' => Response::HTTP_BAD_REQUEST,
                    'message' => 'Bad Request',
                    'errors' => $this->validationErrorSerializer->serialize($errors),
                ],
                Response::HTTP_BAD_REQUEST
            );
        }

        return new JsonResponse(['message' => 'User successfully edited!'], Response::HTTP_OK);
    }

    /**
     * @Route("/api/users", name="user_List", methods={"Get"})
     */
    public function listUser(): JsonResponse
    {
        $list = $this->userHandler->getList();

        return new JsonResponse($list);
    }

}
