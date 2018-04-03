<?php
/**
 * Created by PhpStorm.
 * User: skews
 * Date: 03.04.2018
 * Time: 18:01
 */

namespace rtPiwikBundle\Services;


use rtPiwikBundle\Document\TotalMetric;

class TotalMetrics
{

    const BATCH = 100;

    private $localConn;
    private $remoteConn;

    function __construct($localConn, $remoteConn)
    {
        $this->localConn = $localConn;
        $this->remoteConn = $remoteConn;
    }

    public function execute()
    {
        $boardsTotal = $this->remoteConn->createQueryBuilder('PiwikBundle:Board')->getQuery()->execute()->count();
        $i = 0;
        while ($boardsTotal > $i) {
            $this->getTotalMetricsByRepository($this->remoteConn, $this->localConn, TotalMetrics::BATCH, $i);
            $i += TotalMetrics::BATCH;
        }

        $this->getTotalMetricsByRepository($this->remoteConn, $this->localConn, $boardsTotal - $i, $i);
    }

    /**
     * Get all metrics form repository
     * @param $remoteConn - connection
     * @param $localConn - connection
     * @param null $limit - batch of items
     * @param null $skip - start from item
     */
    private function getTotalMetricsByRepository($remoteConn, $localConn, $limit = null, $skip = null)
    {
        $boardsRepository = $remoteConn
            ->getRepository('rtPiwikBundle:Board')
            ->findBy(array(), array('created' => 'desc'), $limit, $skip);

        foreach ($boardsRepository as $boardRepository) {
            $boardSlug = $boardRepository->getSlug();
            $boardCreated = $boardRepository->getCreated();

            $this->getTotalMetrics($localConn, $boardCreated, $boardSlug);
        }
    }

    /**
     * Get total metrics
     * @param $conn - connection
     * @param $boardCreated - document created date
     * @param $boardSlug - document slug
     */
    private function getTotalMetrics($conn, $boardCreated, $boardSlug)
    {
        // universal for total and daily
        if (!is_null($boardCreated) && !is_null($boardSlug)) {
            $metricRepository = $conn
                ->getRepository('PiwikBundle:Metrics')
                ->findOneBy(array('slug' => $boardSlug));

            $date = new \DateTime();
            $now = $date->getTimestamp();
            $created = $date->modify($boardCreated->format('Y-m-d'))->getTimestamp();

            $metricsModel = new Metrics();
            while ($now > $created) {
                $created = $created + 60 * 60 * 60 * 12;

                $dateFrom = $date->format('Y-m-d');
                $dateTo = $date->setTimestamp($created)->format('Y-m-d');

                $this->upsert($boardSlug, $dateFrom, $dateTo, $metricRepository, $metricsModel, $conn);
            }

            $dateFrom = $date->setTimestamp($now)->format('Y-m-d');
            $dateTo = $date->setTimestamp($created)->format('Y-m-d');

            $this->upsert($boardSlug, $dateFrom, $dateTo, $metricRepository, $metricsModel, $conn);
        }
    }

    /**
     * Update existing metrics data or insert a new
     * @param $slug - uniq id
     * @param $dateFrom - date start
     * @param $dateTo - date end
     * @param $metricRepository - metrics collection
     * @param Metrics $metricsModel - metrics model
     * @param $conn - connection
     */
    private function upsert($slug, $dateFrom, $dateTo, $metricRepository, Metrics $metricsModel, $conn)
    {
        $metricData = $this->getContainer()->get("metrics")->getMetrics($slug, $dateFrom, $dateTo);
        if (is_null($metricRepository)) {
            $totalMetric = new TotalMetric();

            $totalMetric->setVisits($metricData->getVisits());
            $totalMetric->setInteractions($metricData->getInteractions());
            $totalMetric->setAvgTimeSpent($metricData->getAvgTimeSpent());
            $totalMetric->setPageViews($metricData->getPageViews());

            $metricsModel->setSlug($slug);
            $metricsModel->setTotalMetric($totalMetric);

            $conn->persist($metricsModel);

            dump(sprintf("created:total slug:%s, dateFrom:%s, dateTo:%s", $slug, $dateFrom, $dateTo));
        } else {
            $totalMetric = $metricRepository->getTotalMetric();
            if (is_null($totalMetric)) {
                $totalMetric = new TotalMetric();
            }

            $visits = $totalMetric->getVisits() + $metricData->getVisits();
            $totalMetric->setVisits($visits);

            $interactions = $totalMetric->getInteractions() + $metricData->getInteractions();
            $totalMetric->setInteractions($interactions);

            if ($metricData->getAvgTimeSpent() > 0) {
                $avgTimeSpent = round(($totalMetric->getAvgTimeSpent() + $metricData->getAvgTimeSpent()) / 2);
                $totalMetric->setAvgTimeSpent($avgTimeSpent);
            }

            $pageViews = $totalMetric->getPageViews() + $metricData->getPageViews();
            $totalMetric->setPageViews($pageViews);

            $metricRepository->setUpdatedAt(new \DateTime());
            $metricRepository->setTotalMetric($totalMetric);

            dump(sprintf("updated:total slug:%s, dateFrom:%s, dateTo:%s", $slug, $dateFrom, $dateTo));
        }

        $conn->flush();
        $conn->clear();
    }


}