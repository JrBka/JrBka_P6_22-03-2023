<?php

namespace App\Repository;

use App\Entity\Comment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Comment>
 *
 * @method Comment|null find($id, $lockMode = null, $lockVersion = null)
 * @method Comment|null findOneBy(array $criteria, array $orderBy = null)
 * @method Comment[]    findAll()
 * @method Comment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Comment::class);
    }

    public function save(Comment $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Comment $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findCommentsPaginated(int $page, int $limit, string $slug): array
    {

        $result = [];

        $query = $this->getEntityManager()->createQueryBuilder()
            ->select('comment')
            ->from('App:Comment', 'comment')
            ->setMaxResults($limit)
            ->orderBy('comment.createdAt','DESC')
            ->setFirstResult(($page * $limit) - $limit);

        $paginator = new Paginator($query);

        $data = $paginator->getQuery()->getResult();

        if (empty($data)) {
            return $result;
        }

        // round the number of pages to up
        $pages = ceil($paginator->count() / $limit);

        $result['data'] = $data;
        $result['pages'] = $pages;
        $result['page'] = $page;
        $result['limit'] = $limit;
        $result['slug'] = $slug;

        return $result;
    }

}

