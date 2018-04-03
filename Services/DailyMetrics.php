<?php
/**
 * Created by PhpStorm.
 * User: skews
 * Date: 03.04.2018
 * Time: 17:49
 */

namespace rtPiwikBundle\Services;


class DailyMetrics
{
    /**
     * Connect to remote db for getting all boards by date range
     * Get slugs for selected date range
     * Get metrics by selected slugs
     * @param $dateFrom - Date start
     * @param $localConn - Local connection
     * @param $remoteConn - Remote connection
     * @return mixed
     */
    public function getMetricsFromDate($dateFrom, $localConn, $remoteConn)
    {
        $date = new \DateTime();

        $boardsRepository = $remoteConn
            ->getRepository('PiwikBundle:Board')
            ->findBy(
                array('updated' => array('$gt' => $date->setTimestamp($dateFrom))),
                array('created' => 'desc'),
                null,
                null
            );

        $slugs = [];
        foreach ($boardsRepository as $boardRepository) {
            array_push($slugs, $boardRepository->getSlug());
        }

        $metricsRepository = $localConn
            ->getRepository('PiwikBundle:Metrics')
            ->findBy(array('slug' => array('$in' => $slugs)));

        return $metricsRepository;
    }

}