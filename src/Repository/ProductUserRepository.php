<?php

namespace App\Repository;

use App\Entity\ProductUser;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method ProductUser|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProductUser|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProductUser[]    findAll()
 * @method ProductUser[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductUserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProductUser::class);
    }
}
