<?php

namespace App\Entity;

use App\DTO\UserDTO;
use App\Services\UserHandler;
use App\Transformer\UserTransformer;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UserHandlerTest extends KernelTestCase
{
    private $em;

    public function setUp()
    {
        self::bootKernel();

        parent::setUp();

        $this->em = static::$container->get('doctrine')->getManager();
        $this->em->getConnection()->beginTransaction();
    }

    protected function tearDown()
    {
        if ($this->em->getConnection()->isTransactionActive()) {
            try {
                $this->em->getConnection()->rollBack();
            } catch (\Exception $e) {
            }
        }

        parent::tearDown();

        $this->em = null;
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
            static::$container->get(UserTransformer::class)
        );
    }

    public function testValidateEmptyUsername(): void
    {
        $handler = $this->getHandler();
        $dto = $this->getUserDTO();
        $dto->username = '';

        $result = $handler->updateUser($dto);

        $this->assertCount(2, $result);
        $this->assertEquals('username', $result->get(0)->getPropertyPath());
        $this->assertEquals('This value should not be blank.', $result->get(0)->getMessage());
    }

    public function testValidateEmptyEmail(): void
    {
        $handler = $this->getHandler();
        $dto = $this->getUserDTO();
        $dto->email = '';

        $result = $handler->updateUser($dto);

        $this->assertCount(2, $result);
        $this->assertEquals('email', $result->get(0)->getPropertyPath());
        $this->assertEquals('This value should not be blank.', $result->get(0)->getMessage());
    }

    public function testValidateEmptyNewPassword(): void
    {
        $handler = $this->getHandler();
        $dto = $this->getUserDTO();
        $dto->password = '';

        $result = $handler->updateUser($dto);

        $this->assertCount(2, $result);
        $this->assertEquals('password', $result->get(0)->getPropertyPath());
        $this->assertEquals('This value should not be blank.', $result->get(0)->getMessage());
    }

    public function testValidateEmptyConfirmPassword(): void
    {
        $handler = $this->getHandler();
        $dto = $this->getUserDTO();
        $dto->confirmPassword = '';

        $result = $handler->updateUser($dto);

        $this->assertCount(1, $result);
        $this->assertEquals('confirmPassword', $result->get(0)->getPropertyPath());
        $this->assertEquals('Passwords do not match.', $result->get(0)->getMessage());
    }

    public function testValidateIncorrectConfirmPassword(): void
    {
        $handler = $this->getHandler();
        $dto = $this->getUserDTO();
        $dto->confirmPassword = 'ASDFGH123456!@#$%^';

        $result = $handler->updateUser($dto);

        $this->assertCount(1, $result);
        $this->assertEquals('confirmPassword', $result->get(0)->getPropertyPath());
        $this->assertEquals('Passwords do not match.', $result->get(0)->getMessage());
    }

    public function testValidateEmptyFullName(): void
    {
        $handler = $this->getHandler();
        $dto = $this->getUserDTO();
        $dto->fullName = '';

        $result = $handler->updateUser($dto);

        $this->assertCount(2, $result);
        $this->assertEquals('fullName', $result->get(0)->getPropertyPath());
        $this->assertEquals('This value should not be blank.', $result->get(0)->getMessage());
    }

    public function testValidateEmptyRoles(): void
    {
        $handler = $this->getHandler();
        $dto = $this->getUserDTO();
        $dto->role = [];

        $result = $handler->updateUser($dto);

        $this->assertCount(1, $result);
        $this->assertEquals('userRoles', $result->get(0)->getPropertyPath());
        $this->assertEquals('This collection should contain 1 element or more.', $result->get(0)->getMessage());
    }

    public function testValidateInvalidRole(): void
    {
        $handler = $this->getHandler();
        $dto = $this->getUserDTO();
        $dto->role = [1, 2];

        $result = $handler->updateUser($dto);

        $this->assertCount(1, $result);
        $this->assertEquals('userRoles', $result->get(0)->getPropertyPath());
        $this->assertEquals('Role 2 not found', $result->get(0)->getMessage());
    }

    public function testValidateEmptyUserProjectRoles(): void
    {
        $handler = $this->getHandler();
        $dto = $this->getUserDTO();
        $dto->projectRoles = [];

        $result = $handler->updateUser($dto);

        $this->assertCount(0, $result);
    }

    public function testValidateInvalidUserProjectRole(): void
    {
        $handler = $this->getHandler();
        $dto = $this->getUserDTO();
        $dto->projectRoles = ['1' => '2'];

        $result = $handler->updateUser($dto);

        $this->assertCount(1, $result);
        $this->assertEquals('projectRoles', $result->get(0)->getPropertyPath());
        $this->assertEquals('Role Project 2 not found', $result->get(0)->getMessage());
    }

    public function testValidateInvalidUserProject(): void
    {
        $handler = $this->getHandler();
        $dto = $this->getUserDTO();
        $dto->projectRoles = ['2' => '1'];

        $result = $handler->updateUser($dto);

        $this->assertCount(1, $result);
        $this->assertEquals('projectRoles', $result->get(0)->getPropertyPath());
        $this->assertEquals('Project 2 not found', $result->get(0)->getMessage());
    }

    public function testValidateNewExistingUsername(): void
    {
        $existing = new User();
        $existing->setUsername('existing');
        $existing->setEmail('test@test.com');
        $existing->setFullName('name');
        $this->em->persist($existing);
        $this->em->flush();

        $repositoryMock = $this->createMock(ObjectRepository::class);
        $emMock = $this->createMock(EntityManagerInterface::class);
        $repositoryMock
            ->method('findOneBy')
            ->willReturn(new User());
        $emMock
            ->method('getRepository')
            ->willReturn($repositoryMock);

        $handler = $this->getHandler();
        $dto = $this->getUserDTO();
        $dto->username = 'existing';

        $result = $handler->updateUser($dto);

        $this->assertCount(1, $result);
        $this->assertEquals('username', $result->get(0)->getPropertyPath());
        $this->assertEquals('This value is already used.', $result->get(0)->getMessage());
    }

    public function testValidateNewExistingEmail(): void
    {
        $existing = new User();
        $existing->setUsername('existing');
        $existing->setEmail('test@test.com');
        $existing->setFullName('name');
        $this->em->persist($existing);
        $this->em->flush();

        $repositoryMock = $this->createMock(ObjectRepository::class);
        $emMock = $this->createMock(EntityManagerInterface::class);
        $repositoryMock
            ->method('findOneBy')
            ->willReturn(new User());
        $emMock
            ->method('getRepository')
            ->willReturn($repositoryMock);

        $handler = $this->getHandler();
        $dto = $this->getUserDTO();
        $dto->email = 'test@test.com';

        $result = $handler->updateUser($dto);

        $this->assertCount(1, $result);
        $this->assertEquals('email', $result->get(0)->getPropertyPath());
        $this->assertEquals('This value is already used.', $result->get(0)->getMessage());
    }

    public function testValidateEditOk(): void
    {
        $user = new User();
        $user->setUsername('iguidea20');
        $user->setEmail('iguidea20@gmail.com');

        $handler = $this->getHandler();
        $dto = $this->getUserDTO();

        $result = $handler->updateUser($dto, $user);
        $this->assertCount(0, $result);
    }

    public function testGetListUser(): void
    {
        $user1 = new User();
        $user1->setUsername('egavrisco');
        $user1->setFullName('Elena Gavrisco');
        $user1->setPassword('Asdfgh123456!@#$%^');
        $user1->setEmail('egavrisco@gmail.com');
//        $user1->addUserRole(1, 3);
//        $user1->addUserRole(['1' => '1']);

        $user2 = new User();
        $user2->setUsername('iguidea20');
        $user2->setFullName('Ion Guidea');
        $user2->setPassword('Asdfgh123456!@#$%^');
        $user2->setEmail('iguidea1@gmail.com');

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
            static::$container->get(UserTransformer::class)
        );
        $result = $handler->getList();

        $dto1 = new UserDTO();
        $dto1->username = 'egavrisco';
        $dto1->fullName = 'Elena Gavrisco';
        $dto1->password = null;
        $dto1->email = 'egavrisco@gmail.com';
        $dto1->role = [];
        $dto1->projectRoles = [];
        $arr[] = $dto1;

        $dto2 = new UserDTO();
        $dto2->username = 'iguidea20';
        $dto2->fullName = 'Ion Guidea';
        $dto2->password = null;
        $dto2->email = 'iguidea1@gmail.com';
        $dto2->role = [];
        $dto2->projectRoles = [];
        $arr[] = $dto2;

        $this->assertCount(2, $result);
        $this->assertEquals($arr, $result);
    }

    /**
     * @return UserDTO
     */
    private function getUserDTO(): UserDTO
    {
        $dto = new UserDTO();
        $dto->username = 'iguidea20';
        $dto->fullName = 'Ion Guidea';
        $dto->password = 'Asdfgh123456!@#$%^';
        $dto->confirmPassword = 'Asdfgh123456!@#$%^';
        $dto->email = 'iguidea1@gmail.com';
        $dto->role = [1, 3];
        $dto->projectRoles = ['1' => '1'];

        return $dto;
    }
}
