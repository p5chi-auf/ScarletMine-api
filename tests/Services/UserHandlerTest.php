<?php

namespace App\Entity;

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
            ->willReturnMap([
                [1, new Role()],
                [3, new Role()],
                [2, null],
            ]);

        $projectRepositoryMock = $this->createMock(ObjectRepository::class);
        $projectRepositoryMock
            ->method('find')
            ->willReturnMap([
                [1, new Project()],
                [2, null],
            ]);

        $userProjectRoleRepositoryMock = $this->createMock(ObjectRepository::class);
        $userProjectRoleRepositoryMock
            ->method('find')
            ->willReturn(new UserProjectRole());

        $roleProjectRepositoryMock = $this->createMock(ObjectRepository::class);
        $roleProjectRepositoryMock
            ->method('find')
            ->willReturnMap([
                ['1', new RoleProject()],
                ['2', null],
            ]);

        $emMock = $this->createMock(EntityManagerInterface::class);
        $emMock
            ->method('getRepository')
            ->willReturnMap([
                [Role::class, $roleRepositoryMock],
                [RoleProject::class, $roleProjectRepositoryMock],
                [User::class, $repositoryMock],
                [Project::class, $projectRepositoryMock],
                [UserProjectRole::class, $userProjectRoleRepositoryMock],
            ]);

        return new UserHandler(
            $emMock,
            static::$container->get('validator'),
            static::$container->get('security.user_password_encoder.generic')
        );
    }

    public function testValidateEmptyUsername(): void
    {
        $handler = $this->getHandler();
        $result = $handler->updateUser([
            'username' => '',
            'newPassword' => '1234',
            'fullName' => 'Ion Guidea',
            'roles' => [1],
            'projectRoles' => ['1' => '1']
        ], new User());

        $this->assertCount(1, $result);
        $this->assertEquals('username', $result->get(0)->getPropertyPath());
        $this->assertEquals('This value should not be blank.', $result->get(0)->getMessage());
    }

    public function testValidateEmptyNewPassword(): void
    {
        $handler = $this->getHandler();
        $result = $handler->updateUser([
            'username' => 'iguidea20',
            'newPassword' => '',
            'fullName' => 'Ion Guidea',
            'roles' => [1],
            'projectRoles' => ['1' => '1']
        ], new User());

        $this->assertCount(1, $result);
        $this->assertEquals('password', $result->get(0)->getPropertyPath());
        $this->assertEquals('This value should not be blank.', $result->get(0)->getMessage());
    }

    public function testValidateEmptyFullName(): void
    {
        $handler = $this->getHandler();
        $result = $handler->updateUser([
            'username' => 'iguidea20',
            'newPassword' => '1234',
            'fullName' => '',
            'roles' => [1],
            'projectRoles' => ['1' => '1']
        ], new User());

        $this->assertCount(1, $result);
        $this->assertEquals('fullName', $result->get(0)->getPropertyPath());
        $this->assertEquals('This value should not be blank.', $result->get(0)->getMessage());
    }

    public function testValidateEmptyRoles(): void
    {
        $handler = $this->getHandler();
        $result = $handler->updateUser([
            'username' => 'iguidea21',
            'newPassword' => '1234',
            'fullName' => 'Ion Guidea',
            'roles' => [],
            'projectRoles' => ['1' => '1']
        ], new User());

        $this->assertCount(1, $result);
        $this->assertEquals('userRoles', $result->get(0)->getPropertyPath());
        $this->assertEquals('This collection should contain 1 element or more.', $result->get(0)->getMessage());
    }

    public function testValidateInvalidRole(): void
    {
        $handler = $this->getHandler();
        $result = $handler->updateUser([
            'username' => 'iguidea21',
            'newPassword' => '1234',
            'fullName' => 'Ion Guidea',
            'roles' => [1, 2],
            'projectRoles' => ['1' => '1']
        ], new User());

        $this->assertCount(1, $result);
        $this->assertEquals('userRoles', $result->get(0)->getPropertyPath());
        $this->assertEquals('Role 2 not found', $result->get(0)->getMessage());
    }

    public function testValidateEmptyUserProjectRoles(): void
    {
        $handler = $this->getHandler();
        $result = $handler->updateUser([
            'username' => 'iguidea22',
            'newPassword' => '1234',
            'fullName' => 'Ion Guidea',
            'roles' => [1],
            'projectRoles' => []
        ], new User());

        $this->assertCount(0, $result);
    }

    public function testValidateInvalidUserProjectRole(): void
    {
        $handler = $this->getHandler();
        $result = $handler->updateUser([
            'username' => 'iguidea21',
            'newPassword' => '1234',
            'fullName' => 'Ion Guidea',
            'roles' => [1],
            'projectRoles' => ['1' => '2']
        ], new User());

        $this->assertCount(1, $result);
        $this->assertEquals('projectRoles', $result->get(0)->getPropertyPath());
        $this->assertEquals('Role Project 2 not found', $result->get(0)->getMessage());
    }

    public function testValidateInvalidUserProject(): void
    {
        $handler = $this->getHandler();
        $result = $handler->updateUser([
            'username' => 'iguidea21',
            'newPassword' => '1234',
            'fullName' => 'Ion Guidea',
            'roles' => [1],
            'projectRoles' => ['2' => '1']
        ], new User());

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

        $result = $handler->updateUser([
            'username' => 'iguidea',
            'newPassword' => '1234',
            'fullName' => 'Ion',
            'roles' => [1],
            'projectRoles' => ['1' => '1']
        ], new User());

        $this->assertCount(1, $result);
        $this->assertEquals('username', $result->get(0)->getPropertyPath());
        $this->assertEquals('This value is already used.', $result->get(0)->getMessage());
    }

    public function testValidateEditOk(): void
    {
        $user = new User();

        $handler = $this->getHandler();
        $result = $handler->updateUser([
            'username' => 'iguidea2',
            'newPassword' => '1234',
            'fullName' => 'Ion',
            'roles' => [1],
            'projectRoles' => ['1' => '1'],
        ], $user);

        $this->assertCount(0, $result);
    }

    public function testGetListUser(): void
    {
        $user1 = new User();
        $user1->getId();
        $user1->setUsername('username1');
        $user1->setPassword('newPassword1');
        $user1->setFullName('fullName1');

        $user2 = new User();
        $user2->getId();
        $user2->setUsername('username2');
        $user2->setPassword('newPassword2');
        $user2->setFullName('fullName2');

        $repositoryMock = $this->createMock(ObjectRepository::class);
        $emMock = $this->createMock(EntityManagerInterface::class);
        $repositoryMock
            ->method('findAll')
            ->willReturn([$user1, $user2]);
        $emMock
            ->method('getRepository')
            ->willReturn($repositoryMock);

        $handler = new UserHandler($emMock,
            static::$container->get('validator'),
            static::$container->get('security.user_password_encoder.generic')
        );
        $result = $handler->getList();
        $this->assertEquals(
            [
                [
                    'id' => null,
                    'username' => 'username1',
                    'newPassword' => 'newPassword1',
                    'fullName' => 'fullName1',
                    'roles' => [],
                ],
                [
                    'id' => null,
                    'username' => 'username2',
                    'newPassword' => 'newPassword2',
                    'fullName' => 'fullName2',
                    'roles' => [],
                ]
            ],
            $result
        );

        $this->assertCount(2, $result);
    }
}
