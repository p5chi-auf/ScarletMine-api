<?php


namespace App\Entity;

use App\Services\ProjectHandler;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;


class ProjectHandlerTest extends KernelTestCase
{
    public function setUp()
    {
        self::bootKernel();
    }

    public function testValidateEmptyName(): void
    {
        $handler = $this->getHandler();
        $result = $handler->updateProject(['name' => ' '], new Project());
        $this->assertCount(1, $result);
        $this->assertEquals('name', $result->get(0)->getPropertyPath());
        $this->assertEquals('This value should not be blank.', $result->get(0)->getMessage());
    }

    private function getHandler(): ProjectHandler
    {
        $repositoryMock = $this->createMock(ObjectRepository::class);
        $emMock = $this->createMock(EntityManagerInterface::class);
        $emMock
            ->method('getRepository')
            ->willReturn($repositoryMock);

        return new ProjectHandler($emMock, static::$container->get('validator'));
    }

    public function testValidateNewExistingName(): void
    {
        $repositoryMock = $this->createMock(ObjectRepository::class);
        $emMock = $this->createMock(EntityManagerInterface::class);
        $repositoryMock
            ->method('findOneBy')
            ->willReturn(new Project());
        $emMock
            ->method('getRepository')
            ->willReturn($repositoryMock);
        $handler = $this->getHandler();
        $result = $handler->updateProject(['name' => 'name'], new Project());
        $this->assertCount(1, $result);
        $this->assertEquals('name', $result->get(0)->getPropertyPath());
        $this->assertEquals('This value is already used.', $result->get(0)->getMessage());
    }

    public function testValidateEditOk(): void
    {
        $project = new Project();
        $repositoryMock = $this->createMock(ObjectRepository::class);
        $emMock = $this->createMock(EntityManagerInterface::class);
        $repositoryMock
            ->method('findOneBy')
            ->willReturn($project);
        $emMock
            ->method('getRepository')
            ->willReturn($repositoryMock);
        $handler = $this->getHandler();
        $result = $handler->updateProject(['name' => 'name1'], $project);
        $this->assertCount(0, $result);
    }

    public function testValidateEditNotOK(): void
    {
        $project1 = new Project();
        $project2 = new Project();
        $repositoryMock = $this->createMock(ObjectRepository::class);
        $emMock = $this->createMock(EntityManagerInterface::class);
        $repositoryMock
            ->method('findOneBy')
            ->willReturn($project1);
        $emMock
            ->method('getRepository')
            ->willReturn($repositoryMock);
        $handler = $this->getHandler();
        $result = $handler->updateProject(['name' => 'name'], $project2);
        $this->assertCount(1, $result);
        $this->assertEquals('name', $result->get(0)->getPropertyPath());
        $this->assertEquals('This value is already used.', $result->get(0)->getMessage());
    }

    public function testValidateNewNameOK(): void
    {
        $project = new Project();
        $repositoryMock = $this->createMock(ObjectRepository::class);
        $emMock = $this->createMock(EntityManagerInterface::class);
        $repositoryMock
            ->method('findOneBy')
            ->willReturn($project);
        $emMock
            ->method('getRepository')
            ->willReturn($repositoryMock);
        $handler = $this->getHandler();
        $result = $handler->updateProject(['name' => 'name1'], $project);
        $this->assertCount(0, $result);
    }

    public function testSaveNullName(): void
    {
        $emMock = $this->createMock(EntityManagerInterface::class);
        $emMock
            ->method('persist');
        $handler = $this->getHandler();
        $result = $handler->updateProject(['name' => null], new Project());
        $this->assertCount(1, $result);
        $this->assertEquals('name', $result->get(0)->getPropertyPath());
        $this->assertEquals('This value should not be blank.', $result->get(0)->getMessage());
    }

    public function testGetListName(): void
    {
        $project1 = new Project();
        $project1->getId();
        $project1->setName('name1');

        $project2 = new Project();
        $project2->getId();
        $project2->setName('name2');

        $repositoryMock = $this->createMock(ObjectRepository::class);
        $emMock = $this->createMock(EntityManagerInterface::class);
        $repositoryMock
            ->method('findAll')
            ->willReturn([$project1, $project2]);
        $emMock
            ->method('getRepository')
            ->willReturn($repositoryMock);
        $handler = new ProjectHandler($emMock, static::$container->get('validator'));
        $result = $handler->getList();
        $this->assertEquals(
            [
                ['id' => null, 'name' => 'name1'],
                ['id' => null, 'name' => 'name2'],
            ],
            $result,
            '$result'
        );

        $this->assertCount(2, $result);
    }
}
