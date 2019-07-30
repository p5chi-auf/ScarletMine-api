<?php

namespace App\Services;

use App\Entity\Project;
use App\Entity\Status;
use App\Entity\Task;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Doctrine\Common\Persistence\ObjectRepository;

class TaskHandlerTest extends KernelTestCase
{
    public function setUp()
    {
        self::bootKernel();
    }

    public function testValidateEmptyTitle(): void
    {
        $handler = $this->getHandler();
        $result = $handler->updateTask(
            [
                'title' => '',
                'description' => 'blla',
                'status' => 1,
                'users' => [],
                'project' => 3,
            ],
            new Task()
        );
        $this->assertCount(1, $result);
        $this->assertEquals('title', $result->get(0)->getPropertyPath());
        $this->assertEquals('This value should not be blank.', $result->get(0)->getMessage());
    }

    private function getHandler(): TaskHandler
    {
        $userRepositoryMock = $this->createMock(ObjectRepository::class);
        $userRepositoryMock
            ->method('find')
            ->willReturnMap(
                [
                    [1, new User()],
                    [2, null],
                ]
            );

        $projectRepositoryMock = $this->createMock(ObjectRepository::class);
        $projectRepositoryMock->method('find')->willReturn(new Project());

        $statusRepositoryMock = $this->createMock(ObjectRepository::class);
        $statusRepositoryMock
            ->method('find')
            ->willReturnMap(
                [
                    [1, new Status()],
                    [2, null],
                ]
            );
        $emMock = $this->createMock(EntityManagerInterface::class);
        $emMock
            ->method('getRepository')
            ->willReturnMap(
                [
                    [User::class, $userRepositoryMock],
                    [Project::class, $projectRepositoryMock],
                    [Status::class, $statusRepositoryMock],
                ]
            );
        $securityMock = $this->createMock(\Symfony\Component\Security\Core\Security::class);
        $securityMock->method('getUser')->willReturn(new User());

        return new TaskHandler($emMock, static::$container->get('validator'), $securityMock);
    }


    public function testValidateOK(): void
    {
        $handler = $this->getHandler();
        $result = $handler->updateTask(
            [
                'title' => 'title',
                'description' => 'description',
                'status' => 1,
                'users' => [],
                'project' => 1,
                'createdBy' => 1,
            ],
            new Task()
        );
        $this->assertCount(0, $result);
    }

    public function testValidateStatusInvalid(): void
    {
        $handler = $this->getHandler();
        $result = $handler->updateTask(
            [
                'title' => 'title',
                'description' => 'description',
                'status' => 2,
                'users' => [],
                'project' => 1,
                'createdBy' => 1,
            ],
            new Task()
        );
        $this->assertCount(2, $result);
        $this->assertEquals('status', $result->get(0)->getPropertyPath());
        $this->assertEquals('This value should not be null.', $result->get(0)->getMessage());
    }
}
