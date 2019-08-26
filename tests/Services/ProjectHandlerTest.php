<?php

namespace App\Entity;

use App\DTO\ProjectDTO;
use App\Services\ProjectHandler;
use App\Transformer\ProjectTransformer;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ProjectHandlerTest extends KernelTestCase
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

    public function testValidateEmptyName(): void
    {
        $handler = $this->getHandler();
        $dto = $this->getProjectDTO();
        $dto->name = '';
        $result = $handler->updateProject($dto, new Project());

        $this->assertCount(2, $result);
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

        return new ProjectHandler($emMock,
            static::$container->get('validator'),
            static::$container->get(ProjectTransformer::class));
    }

    public function testValidateNewExistingName(): void
    {
        $existing = new Project();
        $existing->setName('existing');
        $this->em->persist($existing);
        $this->em->flush();

        $repositoryMock = $this->createMock(ObjectRepository::class);
        $emMock = $this->createMock(EntityManagerInterface::class);
        $repositoryMock
            ->method('findOneBy')
            ->willReturn(new Project());
        $emMock
            ->method('getRepository')
            ->willReturn($repositoryMock);

        $handler = $this->getHandler();
        $dto = $this->getProjectDTO();
        $dto->name = 'existing';

        $result = $handler->updateProject($dto);

        $this->assertCount(1, $result);
        $this->assertEquals('name', $result->get(0)->getPropertyPath());
        $this->assertEquals('This value is already used.', $result->get(0)->getMessage());
    }

    public function testValidateEditOk(): void
    {
        $project = new Project();
        $project->setName('name');

        $handler = $this->getHandler();
        $dto = $this->getProjectDTO();

        $result = $handler->updateProject($dto, $project);
        $this->assertCount(0, $result);
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
        $handler = new ProjectHandler(
            $emMock,
            static::$container->get('validator'),
            static::$container->get(ProjectTransformer::class)
        );
        $result = $handler->getList();

        $dto1 = new ProjectDTO();
        $dto1->name = 'name1';
        $arr[] = $dto1;

        $dto2 = new ProjectDTO();
        $dto2->name = 'name2';
        $arr[] = $dto2;

        $this->assertCount(2, $result);
        $this->assertEquals($arr, $result);
    }

    /**
     * @return ProjectDTO
     */
    private function getProjectDTO(): ProjectDTO
    {
        $dto = new ProjectDTO();
        $dto->name = 'name';

        return $dto;
    }
}
