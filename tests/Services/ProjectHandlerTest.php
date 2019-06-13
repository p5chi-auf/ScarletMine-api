<?php


namespace App\Entity;

use App\Services\ProjectHandler;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;


class ProjectHandlerTest extends TestCase
{
    public function testValidateEmptyName(): void
    {
        $handler = $this->getHandler();
        $result = $handler->validate(['name' => null]);

        $this->assertFalse($result);
    }

    private function getHandler(): ProjectHandler
    {
        $repositoryMock = $this->createMock(ObjectRepository::class);
        $emMock = $this->createMock(EntityManagerInterface::class);
        $emMock
            ->method('getRepository')
            ->willReturn($repositoryMock);

        return new ProjectHandler($emMock);

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
        $handler = new ProjectHandler($emMock);

        $result = $handler->validate(['name' => 'name']);

        $this->assertFalse($result);
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

        $handler = new ProjectHandler($emMock);
        $result = $handler->validate(['name' => 'name'], $project);

        $this->assertTrue($result);
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

        $handler = new ProjectHandler($emMock);
        $result = $handler->validate(['name' => 'name'], $project2);

        $this->assertFalse($result);
    }

    public function testValidateNewName(): void
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

        $handler = new ProjectHandler($emMock);
        $result = $handler->validate(['name' => 'name'], $project);

        $this->assertTrue($result);
    }

    public function testSaveNullName(): void
    {
        $emMock = $this->createMock(EntityManagerInterface::class);
        $emMock
            ->expects($this->once())
            ->method('persist');

        $handler = new ProjectHandler($emMock);
        $result = $handler->save(['name' => 'name'], null);

        $this->assertNotNull($result);
    }

    public function testSaveNotNullName(): void
    {
        $project = new Project();
        $emMock = $this->createMock(EntityManagerInterface::class);
        $emMock
            ->expects($this->once())
            ->method('persist');

        $handler = new ProjectHandler($emMock);
        $result = $handler->save(['name' => 'name1'], $project);

        $this->assertEquals('name1', $result->getName());

    }

    public function testGetListName(): void
    {
        $project1 = new Project();
        $project1->setName('name1');

        $project2 = new Project();
        $project2->setName('name2');

        $repositoryMock = $this->createMock(ObjectRepository::class);
        $emMock = $this->createMock(EntityManagerInterface::class);
        $repositoryMock
            ->method('findAll')
            ->willReturn([$project1, $project2]);
        $emMock
            ->expects($this->once())
            ->method('getRepository')
            ->willReturn($repositoryMock);

        $handler = new ProjectHandler($emMock);
        $result = $handler->getList();

        $this->assertEquals(
            [
                ['id' => null, 'name' => 'name1'],
                ['id' => null, 'name' => 'name2'],

            ],
            $result
        );
    }
}
