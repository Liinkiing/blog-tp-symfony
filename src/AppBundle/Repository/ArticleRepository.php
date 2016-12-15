<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Article;


/**
 * ArticleRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ArticleRepository extends \Doctrine\ORM\EntityRepository
{

    /**
     * @param $month
     * @param $year
     * @param string $order
     * @return Article[]]
     */
    public function findByDate($month, $year, $order = "DESC")
    {
        $startDate = \DateTimeImmutable::createFromFormat('d/m/Y H:i:s', "01/$month/$year 00:00:00");
        $endDate = $startDate->modify('last day of this month');
        $qb = $this->getEntityManager()->createQueryBuilder();
        return $qb->select('article')
            ->from('AppBundle:Article', 'article')
            ->where("article.createdAt BETWEEN :start AND :end")
            ->setParameters([
                'start' => $startDate,
                'end' => $endDate
            ])
            ->getQuery()
            ->getResult();

    }

    public function groupByYearAndMonth($order = "DESC")
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb2 = $this->getEntityManager()->createQueryBuilder();

        $groupByYear = $qb->select('MONTH(article.createdAt) AS month, YEAR(article.createdAt) AS year, COUNT(article) AS cpt')
            ->from('AppBundle:Article', 'article')
            ->addGroupBy('year')
            ->orderBy('article.createdAt', $order)
            ->getQuery()
            ->getArrayResult();

        $groupByMonth = $qb2->select('MONTH(article.createdAt) AS month, YEAR(article.createdAt) AS year, COUNT(article) AS cpt')
            ->from('AppBundle:Article', 'article')
            ->addGroupBy('month')
            ->addGroupBy('year')
            ->orderBy('article.createdAt', $order)
            ->getQuery()
            ->getArrayResult();

        $final = [];
        foreach ($groupByYear as $year) {
            $final[$year['year']] = ['count' => $year['cpt']];
        }

        foreach ($groupByMonth as $month) {
            if(!isset($final[$month['year']]['items'])) $final[$month['year']]['items'] = [];
            array_push($final[$month['year']]['items'], [$month['month'] => $month['cpt']]);
        }

        return $final;


    }


}
