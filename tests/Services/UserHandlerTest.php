<?php

namespace App\Entity;

use App\DTO\UserDTO;
use App\Services\UserHandler;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UserHandlerTest extends KernelTestCase
{
    public function setUp()
    {
        self::bootKernel();
    }

    private function getHandler(): UserHandler
    {
        $repositoryMock = $this->createMock(ObjectRepository::class);

        $roleRepositoryMock = $this->createMock(ObjectRepository::class);
        $roleRepositoryMock
            ->method('find')
            ->willReturnMap(
                [
                    [1, new Role()],
                    [3, new Role()],
                    [2, null],
                ]
            );

        $projectRepositoryMock = $this->createMock(ObjectRepository::class);
        $projectRepositoryMock
            ->method('find')
            ->willReturnMap(
                [
                    [1, new Project()],
                    [2, null],
                ]
            );

        $userProjectRoleRepositoryMock = $this->createMock(ObjectRepository::class);
        $userProjectRoleRepositoryMock
            ->method('find')
            ->willReturn(new UserProjectRole());

        $roleProjectRepositoryMock = $this->createMock(ObjectRepository::class);
        $roleProjectRepositoryMock
            ->method('find')
            ->willReturnMap(
                [
                    ['1', new RoleProject()],
                    ['2', null],
                ]
            );

        $emMock = $this->createMock(EntityManagerInterface::class);
        $emMock
            ->method('getRepository')
            ->willReturnMap(
                [
                    [Role::class, $roleRepositoryMock],
                    [RoleProject::class, $roleProjectRepositoryMock],
                    [User::class, $repositoryMock],
                    [Project::class, $projectRepositoryMock],
                    [UserProjectRole::class, $userProjectRoleRepositoryMock],
                ]
            );

        return new UserHandler(
            $emMock,
            static::$container->get('validator'),
            static::$container->get('security.user_password_encoder.generic')
        );
    }

    public function testValidateEmptyUsername(): void
    {
        $handler = $this->getHandler();
        $dto = new UserDTO();
        $dto->username = '';
        $dto->fullName = 'Ion Guidea';
        $dto->password = 'Asdfgh123456!@#$%^';
        $dto->confirmPassword = 'Asdfgh123456!@#$%^';
        $dto->email = 'iguidea1@gmail.com';
        $dto->role = [1];
        $dto->projectRoles = ['1' => '1'];

        $result = $handler->updateUser($dto, new User());

        $this->assertCount(2, $result);
        $this->assertEquals('username', $result->get(0)->getPropertyPath());
        $this->assertEquals('This value should not be blank.', $result->get(0)->getMessage());
    }

    public function testValidateEmptyEmail(): void
    {
        $handler = $this->getHandler();
        $dto = new UserDTO();
        $dto->username = 'iguidea20';
        $dto->fullName = 'Ion Guidea';
        $dto->password = 'Asdfgh123456!@#$%^';
        $dto->confirmPassword = 'Asdfgh123456!@#$%^';
        $dto->email = '';
        $dto->role = [1];
        $dto->projectRoles = ['1' => '1'];

        $result = $handler->updateUser($dto, new User());

        $this->assertCount(2, $result);
        $this->assertEquals('email', $result->get(0)->getPropertyPath());
        $this->assertEquals('This value should not be blank.', $result->get(0)->getMessage());
    }

    public function testValidateEmptyNewPassword(): void
    {
        $handler = $this->getHandler();
        $dto = new UserDTO();
        $dto->username = 'iguidea20';
        $dto->fullName = 'Ion Guidea';
        $dto->password = '';
        $dto->confirmPassword = 'Asdfgh123456!@#$%^';
        $dto->email = 'iguidea1@gmail.com';
        $dto->role = [1];
        $dto->projectRoles = ['1' => '1'];

        $result = $handler->updateUser($dto, new User());

        $this->assertCount(2, $result);
        $this->assertEquals('password', $result->get(0)->getPropertyPath());
        $this->assertEquals('This value should not be blank.', $result->get(0)->getMessage());
    }

    public function testValidateEmptyConfirmPassword(): void
    {
        $handler = $this->getHandler();
        $dto = new UserDTO();
        $dto->username = 'iguidea20';
        $dto->fullName = 'Ion Guidea';
        $dto->password = 'Asdfgh123456!@#$%^';
        $dto->confirmPassword = '';
        $dto->email = 'iguidea1@gmail.com';
        $dto->role = [1];
        $dto->projectRoles = ['1' => '1'];

        $result = $handler->updateUser($dto, new User());

        $this->assertCount(1, $result);
        $this->assertEquals('confirmPassword', $result->get(0)->getPropertyPath());
        $this->assertEquals('Passwords do not match.', $result->get(0)->getMessage());
    }

    public function testValidateIncorrectConfirmPassword(): void
    {
        $handler = $this->getHandler();
        $dto = new UserDTO();
        $dto->username = 'iguidea20';
        $dto->fullName = 'Ion Guidea';
        $dto->password = 'Asdfgh123456!@#$%^';
        $dto->confirmPassword = 'ASDFGH123456!@#$%^';
        $dto->email = 'iguidea1@gmail.com';
        $dto->role = [1];
        $dto->projectRoles = ['1' => '1'];

        $result = $handler->updateUser($dto, new User());

        $this->assertCount(1, $result);
        $this->assertEquals('confirmPassword', $result->get(0)->getPropertyPath());
        $this->assertEquals('Passwords do not match.', $result->get(0)->getMessage());
    }

    public function testValidateEmptyFullName(): void
    {
        $handler = $this->getHandler();
        $dto = new UserDTO();
        $dto->username = 'iguidea20';
        $dto->fullName = '';
        $dto->password = 'Asdfgh123456!@#$%^';
        $dto->confirmPassword = 'Asdfgh123456!@#$%^';
        $dto->email = 'iguidea1@gmail.com';
        $dto->role = [1];
        $dto->projectRoles = ['1' => '1'];

        $result = $handler->updateUser($dto, new User());

        $this->assertCount(2, $result);
        $this->assertEquals('fullName', $result->get(0)->getPropertyPath());
        $this->assertEquals('This value should not be blank.', $result->get(0)->getMessage());
    }

    public function testValidateEmptyRoles(): void
    {
        $handler = $this->getHandler();
        $dto = new UserDTO();
        $dto->username = 'iguidea20';
        $dto->fullName = 'Ion Guidea';
        $dto->password = 'Asdfgh123456!@#$%^';
        $dto->confirmPassword = 'Asdfgh123456!@#$%^';
        $dto->email = 'iguidea1@gmail.com';
        $dto->role = [];
        $dto->projectRoles = ['1' => '1'];

        $result = $handler->updateUser($dto, new User());

        $this->assertCount(1, $result);
        $this->assertEquals('userRoles', $result->get(0)->getPropertyPath());
        $this->assertEquals('This collection should contain 1 element or more.', $result->get(0)->getMessage());
    }

    public function testValidateInvalidRole(): void
    {
        $handler = $this->getHandler();
        $dto = new UserDTO();
        $dto->username = 'iguidea20';
        $dto->fullName = 'Ion Guidea';
        $dto->password = 'Asdfgh123456!@#$%^';
        $dto->confirmPassword = 'Asdfgh123456!@#$%^';
        $dto->email = 'iguidea1@gmail.com';
        $dto->role = [1, 2];
        $dto->projectRoles = ['1' => '1'];

        $result = $handler->updateUser($dto, new User());

        $this->assertCount(1, $result);
        $this->assertEquals('userRoles', $result->get(0)->getPropertyPath());
        $this->assertEquals('Role 2 not found', $result->get(0)->getMessage());
    }

    public function testValidateEmptyUserProjectRoles(): void
    {
        $handler = $this->getHandler();
        $dto = new UserDTO();
        $dto->username = 'iguidea20';
        $dto->fullName = 'Ion Guidea';
        $dto->password = 'Asdfgh123456!@#$%^';
        $dto->confirmPassword = 'Asdfgh123456!@#$%^';
        $dto->email = 'iguidea1@gmail.com';
        $dto->role = [1];
        $dto->projectRoles = [];

        $result = $handler->updateUser($dto, new User());

        $this->assertCount(0, $result);
    }

    public function testValidateInvalidUserProjectRole(): void
    {
        $handler = $this->getHandler();
        $dto = new UserDTO();
        $dto->username = 'iguidea20';
        $dto->fullName = 'Ion Guidea';
        $dto->password = 'Asdfgh123456!@#$%^';
        $dto->confirmPassword = 'Asdfgh123456!@#$%^';
        $dto->email = 'iguidea1@gmail.com';
        $dto->role = [1];
        $dto->projectRoles = ['1' => '2'];

        $result = $handler->updateUser($dto, new User());

        $this->assertCount(1, $result);
        $this->assertEquals('projectRoles', $result->get(0)->getPropertyPath());
        $this->assertEquals('Role Project 2 not found', $result->get(0)->getMessage());
    }

    public function testValidateInvalidUserProject(): void
    {
        $handler = $this->getHandler();
        $dto = new UserDTO();
        $dto->username = 'iguidea20';
        $dto->fullName = 'Ion Guidea';
        $dto->password = 'Asdfgh123456!@#$%^';
        $dto->confirmPassword = 'Asdfgh123456!@#$%^';
        $dto->email = 'iguidea1@gmail.com';
        $dto->role = [1];
        $dto->projectRoles = ['2' => '1'];

        $result = $handler->updateUser($dto, new User());

        $this->assertCount(1, $result);
        $this->assertEquals('projectRoles', $result->get(0)->getPropertyPath());
        $this->assertEquals('Project 2 not found', $result->get(0)->getMessage());
    }

    public function testValidateNewExistingUsername(): void
    {
        $repositoryMock = $this->createMock(ObjectRepository::class);
        $emMock = $this->createMock(EntityManagerInterface::class);
        $repositoryMock
            ->method('findOneBy')
            ->willReturn(new User());
        $emMock
            ->method('getRepository')
            ->willReturn($repositoryMock);

        $handler = $this->getHandler();
        $dto = new UserDTO();
        $dto->username = 'iguidea';
        $dto->fullName = 'Ion Guidea';
        $dto->password = 'Asdfgh123456!@#$%^';
        $dto->confirmPassword = 'Asdfgh123456!@#$%^';
        $dto->email = 'iguidea1@gmail.com';
        $dto->role = [1];
        $dto->projectRoles = ['1' => '1'];

        $result = $handler->updateUser($dto, new User());

        $this->assertCount(1, $result);
        $this->assertEquals('username', $result->get(0)->getPropertyPath());
        $this->assertEquals('This value is already used.', $result->get(0)->getMessage());
    }

    public function testValidateNewExistingEmail(): void
    {
        $repositoryMock = $this->createMock(ObjectRepository::class);
        $emMock = $this->createMock(EntityManagerInterface::class);
        $repositoryMock
            ->method('findOneBy')
            ->willReturn(new User());
        $emMock
            ->method('getRepository')
            ->willReturn($repositoryMock);

        $handler = $this->getHandler();
        $dto = new UserDTO();
        $dto->username = 'iguidea20';
        $dto->fullName = 'Ion Guidea';
        $dto->password = 'Asdfgh123456!@#$%^';
        $dto->confirmPassword = 'Asdfgh123456!@#$%^';
        $dto->email = 'iguidea@gmail.com';
        $dto->role = [1];
        $dto->projectRoles = ['1' => '1'];

        $result = $handler->updateUser($dto, new User());

        $this->assertCount(1, $result);
        $this->assertEquals('email', $result->get(0)->getPropertyPath());
        $this->assertEquals('This value is already used.', $result->get(0)->getMessage());
    }

    public function testValidateEditOk(): void
    {
        $user = new User();

        $handler = $this->getHandler();
        $dto = new UserDTO();
        $dto->username = 'iguidea20';
        $dto->fullName = 'Ion Guidea';
        $dto->password = 'Asdfgh123456!@#$%^';
        $dto->confirmPassword = 'Asdfgh123456!@#$%^';
        $dto->email = 'iguidea1@gmail.com';
        $dto->role = [1, 3];
        $dto->projectRoles = ['1' => '1'];

        $result = $handler->updateUser($dto, $user);

        $this->assertCount(0, $result);
    }

    public function testGetListUser(): void
    {
        $user1 = new User();
        $user1->getId();
        $user1->setUsername('username1');
        $user1->setPassword('Asdfgh123456!@#$%^1');
        $user1->setFullName('fullName1');
        $user1->setEmail('username1@gmail.com');

        $user2 = new User();
        $user2->getId();
        $user2->setUsername('username2');
        $user2->setPassword('Asdfgh123456!@#$%^2');
        $user2->setFullName('fullName2');
        $user2->setEmail('username2@gmail.com');

        $repositoryMock = $this->createMock(ObjectRepository::class);
        $emMock = $this->createMock(EntityManagerInterface::class);
        $repositoryMock
            ->method('findAll')
            ->willReturn([$user1, $user2]);
        $emMock
            ->method('getRepository')
            ->willReturn($repositoryMock);

        $handler = new UserHandler(
            $emMock,
            static::$container->get('validator'),
            static::$container->get('security.user_password_encoder.generic')
        );
        $result = $handler->getList();
        $this->assertEquals(
            [
                [
                    'id' => null,
                    'username' => 'username1',
                    'newPassword' => 'Asdfgh123456!@#$%^1',
                    'fullName' => 'fullName1',
                    'email' => 'username1@gmail.com',
                    'roles' => [],
                ],
                [
                    'id' => null,
                    'username' => 'username2',
                    'newPassword' => 'Asdfgh123456!@#$%^2',
                    'fullName' => 'fullName2',
                    'email' => 'username2@gmail.com',
                    'roles' => [],
                ],
            ],
            $result
        );

        $this->assertCount(2, $result);
    }
}
