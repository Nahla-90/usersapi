<?php

namespace App\Repository;

use App\Entity\Urls;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Urls|null find($id, $lockMode = null, $lockVersion = null)
 * @method Urls|null findOneBy(array $criteria, array $orderBy = null)
 * @method Urls[]    findAll()
 * @method Urls[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UrlsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Urls::class);
    }

    /**
     * Created By Nahla Sameh
     * @param $data
     * @param bool $count
     * @return array
     */
    public function getAll($data, $count = false)
    {
        $qb = $this->createQueryBuilder('url');
        if ($count === false) {
            $qb->select('url.id', 'url.text');
        } else {
            $qb->select('count(url.id) as count');
        }
        $qb->join('url.client', 'client')
            ->where('client.username =:username')
            ->setParameter('username', $data['username']);
        if ($count === false) {
            if (!empty($data['sortField']) && isset($data['sortOrder'])) {
                $qb->addOrderBy('url.' . $data['sortField'], $data['sortOrder']);
            }
            if (isset($data['offset']) && isset($data['limit'])) {
                $qb->setFirstResult($data['offset'])
                    ->setMaxResults($data['limit']);
            }
        }
        return $qb->getQuery()->getArrayResult();
    }
    // /**
    //  * @return Urls[] Returns an array of Urls objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Urls
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
