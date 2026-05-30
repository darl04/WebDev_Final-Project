<?php

namespace App\Repository;

use App\Entity\Orders;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Orders>
 */
class OrdersRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Orders::class);
    }

    /**
     * @return Orders[]
     */
    public function findForUser(User $user): array
    {
        $qb = $this->createQueryBuilder('o')
            ->leftJoin('o.customer', 'c')->addSelect('c')
            ->leftJoin('o.products', 'p')->addSelect('p')
            ->where('o.createdBy = :user')
            ->setParameter('user', $user);

        if ($user->getEmail()) {
            $qb->orWhere('LOWER(c.email) = LOWER(:email)')
                ->setParameter('email', $user->getEmail());
        }

        return $qb->orderBy('o.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Orders[]
     */
    public function findRecent(int $limit = 10): array
    {
        return $this->createQueryBuilder('o')
            ->leftJoin('o.customer', 'c')->addSelect('c')
            ->leftJoin('o.products', 'p')->addSelect('p')
            ->orderBy('o.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function userOwnsOrder(Orders $order, User $user): bool
    {
        if ($order->getCreatedBy()?->getId() === $user->getId()) {
            return true;
        }

        $customerEmail = $order->getCustomer()?->getEmail();
        if ($customerEmail && $user->getEmail()) {
            return strcasecmp($customerEmail, $user->getEmail()) === 0;
        }

        return false;
    }

//    /**
//     * @return Orders[] Returns an array of Orders objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('o')
//            ->andWhere('o.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('o.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Orders
//    {
//        return $this->createQueryBuilder('o')
//            ->andWhere('o.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
