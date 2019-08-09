<?php

namespace App\Services;

use App\DTO\TaskDTO;
use App\Entity\Project;
use App\Entity\Status;
use App\Entity\User;
use App\Transformer\TaskTransformer;
use App\Transformer\UserTransformer;
use Doctrine\ORM\EntityManagerInterface;
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
        $dto = $this->getTaskDTO();
        $dto->title = '';
        $result = $handler->updateTask($dto);
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

        return new TaskHandler(
            $emMock,
            static::$container->get('validator'),
            static::$container->get(UserTransformer::class),
            static::$container->get(TaskTransformer::class)
        );
    }

    private function getTaskDTO(): TaskDTO
    {
        $dto = new TaskDTO();
        $dto->title = 'title';
        $dto->description = 'describe';
        $dto->status = 1;
        $dto->users = [1];
        $dto->project = 2;
        $dto->createdBy = 1;

        return $dto;
    }

    public function testValidateOK(): void
    {
        $handler = $this->getHandler();
        $dto = $this->getTaskDTO();
        $result = $handler->updateTask($dto);
        $this->assertCount(0, $result);
    }

    public function testValidateStatusInvalid(): void
    {
        $handler = $this->getHandler();
        $dto = $this->getTaskDTO();
        $dto->status = 2;
        $result = $handler->updateTask($dto);

        $this->assertCount(2, $result);
        $this->assertEquals('status', $result->get(0)->getPropertyPath());
        $this->assertEquals('This value should not be null.', $result->get(0)->getMessage());
    }

}
