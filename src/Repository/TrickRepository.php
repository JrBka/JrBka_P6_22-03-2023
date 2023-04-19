<?php

namespace App\Repository;

use App\Entity\Trick;
use App\Service\RemoveImage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Trick>
 *
 * @method Trick|null find($id, $lockMode = null, $lockVersion = null)
 * @method Trick|null findOneBy(array $criteria, array $orderBy = null)
 * @method Trick[]    findAll()
 * @method Trick[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TrickRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Trick::class);
    }

    public function save(Trick $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Trick $entity,RemoveImage $removeImage,array $images,bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);
        if (!empty($images)){
            $removeImage->removeImages($images);
        }

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }


    /**
     * This function count all tricks
     *
     * @return float|int|mixed|string
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function countTricks():mixed
    {
        return $this->getEntityManager()->createQueryBuilder()
            ->select('count(tricks)')
            ->from('App:Trick','tricks')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * This function paginates tricks
     *
     * @param int $limit
     * @return array
     */
    public function findTricksPaginated(int $limit): array {

        $query = $this->getEntityManager()->createQueryBuilder()
            ->select('tricks')
            ->from('App:Trick','tricks')
            ->setMaxResults($limit);

        $data = $query->getQuery()->getResult();

        if (empty($data)){
            return [];
        }else{
            return $data;
        }
    }

}

