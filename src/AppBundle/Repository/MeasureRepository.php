<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;
use AppBundle\Entity\Sensor;

/**
 * MeasureRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class MeasureRepository extends EntityRepository
{
    public function getLastMeasureForSensor(Sensor $sensor)
    {
        $qb = $this->createQueryBuilder('m');

        return $qb->leftJoin('m.sensor', 's')
            ->where('s = :sensor')
            ->orderBy('m.id', 'DESC')
            ->setParameter('sensor', $sensor)
            ->setMaxResults(1)
            ->getQuery()->getSingleResult();
    }

    public function getMeasuresBySensorAndRange(Sensor $sensor, \DateTime $start, \DateTime $end)
    {
        $qb = $this->createQueryBuilder('m');

        return $qb->leftJoin('m.sensor', 's')
            ->where('s = :sensor')
            ->andWhere($qb->expr()->gte('m.date', ':start'))
            ->andWhere($qb->expr()->lte('m.date', ':end'))
            ->orderBy('m.id', 'DESC')
            ->setParameter('sensor', $sensor)
            ->setParameter('start', $start)
            ->setParameter('end', $end)

            ->getQuery()->getResult();
    }

    public function getMaxMeasuresBySensorAndRange(Sensor $sensor, \DateTime $start, \DateTime $end)
    {
        $qb = $this->createQueryBuilder('m');

        return $qb->leftJoin('m.sensor', 's')
            ->where('s = :sensor')
            ->andWhere($qb->expr()->gte('m.date', ':start'))
            ->andWhere($qb->expr()->lte('m.date', ':end'))
            ->orderBy('m.value', 'DESC')
            ->setMaxResults(1)
            ->setParameter('sensor', $sensor)
            ->setParameter('start', $start)
            ->setParameter('end', $end)

            ->getQuery()->getSingleResult();
    }

    public function getMinMeasuresBySensorAndRange(Sensor $sensor, \DateTime $start, \DateTime $end)
    {
        $qb = $this->createQueryBuilder('m');

        return $qb->leftJoin('m.sensor', 's')
            ->where('s = :sensor')
            ->andWhere($qb->expr()->gte('m.date', ':start'))
            ->andWhere($qb->expr()->lte('m.date', ':end'))
            ->orderBy('m.value', 'ASC')
            ->setMaxResults(1)
            ->setParameter('sensor', $sensor)
            ->setParameter('start', $start)
            ->setParameter('end', $end)

            ->getQuery()->getSingleResult();
    }

    public function getAvgMeasuresBySensorAndRange(Sensor $sensor, \DateTime $start, \DateTime $end)
    {
        $qb = $this->createQueryBuilder('m');


        return $qb->select("avg(m.value) as value_avg, count(m.value) as value_count")
            ->leftJoin('m.sensor', 's')
            ->where('s = :sensor')
            ->andWhere($qb->expr()->gte('m.date', ':start'))
            ->andWhere($qb->expr()->lte('m.date', ':end'))
            ->setParameter('sensor', $sensor)
            ->setParameter('start', $start)
            ->setParameter('end', $end)

            ->getQuery()->getSingleResult();
    }
}
