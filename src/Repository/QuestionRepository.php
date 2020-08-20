<?php

namespace App\Repository;

use App\Entity\Question;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Question|null find($id, $lockMode = null, $lockVersion = null)
 * @method Question|null findOneBy(array $criteria, array $orderBy = null)
 * @method Question[]    findAll()
 * @method Question[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class QuestionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Question::class);
    }

    public function findByTag($tag)
    {
        return $this->createQueryBuilder('q')
            ->innerJoin('q.tags', 't')
            ->andWhere('t = :tag')
            ->andWhere('q.isBlocked = false')
            ->setParameter('tag', $tag)
            ->getQuery()
            ->getResult();
    }

    /**
     * Update all outdated questions
     * active = false if updatedAt > 7
     * 
     * SQL = SELECT * FROM `question` WHERE DATEDIFF(NOW(), updated_at) > 7;
     * ou update direct
     * SQL = UPDATE `question` SET active=0 WHERE DATEDIFF(NOW(), updated_at) > 7;
     * 
     * Requêye qui prend en compte si updatedAt est NULL
     * auquel cas on doit prendre en compte la date de création de la question
     * SELECT * FROM `question` WHERE
     * (DATEDIFF(NOW(), updated_at) > 7
     * OR (updated_at IS NULL AND DATEDIFF(NOW(), created_at) > 7)) AND active=1
     * 
     * On convertit les requêtes SQL en DQL ou QB avec les fonctions Doctrine
     * cf : https://www.doctrine-project.org/projects/doctrine-orm/en/current/reference/dql-doctrine-query-language.html#functions-operators-aggregates
     */
    public function findAllOutdated()
    {
        $em = $this->getEntityManager();

        $query = $em->createQuery(
            'UPDATE App\Entity\Question q
            SET q.active=false
            WHERE
            (DATE_DIFF(CURRENT_DATE(), q.updatedAt) > 7
            OR (q.updatedAt IS NULL AND DATE_DIFF(CURRENT_DATE(), q.createdAt) > 7))
            AND q.active=true'
        );

        // Affichage de la requête SQL générée
        // echo $query->getSQL();

        // Retourne le nombre de lignes impactées par la requête
        // 0 si aucune lignes impactées
        return $query->getResult();
    }

//    /**
//     * @return Question[] Returns an array of Question objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('q')
            ->andWhere('q.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('q.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Question
    {
        return $this->createQueryBuilder('q')
            ->andWhere('q.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
