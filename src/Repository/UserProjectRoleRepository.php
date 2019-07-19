<?php

namespace App\Repository;

use App\Entity\UserProjectRole;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method UserProjectRole|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserProjectRole|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserProjectRole[]    findAll()
 * @method UserProjectRole[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserProjectRoleRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, UserProjectRole::class);
    }
}
