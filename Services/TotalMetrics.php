<?php
/**
 * Created by PhpStorm.
 * User: skews
 * Date: 03.04.2018
 * Time: 18:01
 */

namespace rtPiwikBundle\Services;

use rtPiwikBundle\Document\Metrics;
use rtPiwikBundle\Document\TotalMetric;

class TotalMetrics
{
    private $metricsService;

    function __construct($metricsService)
    {
        $this->metricsService = $metricsService;
    }

    /**
     * Get total metrics
     * @param $board
     * @param \DateTime $date
     * @param $userIds
     * @return Metrics
     */
    public function get($board, \DateTime $date, $userIds)
    {
        $now = new \DateTime();
        $nowTs = $now->getTimestamp();
        $createdTs = $date->getTimestamp();

        while ($nowTs > $createdTs) {
            $createdTs = $createdTs + 60 * 60 * 60 * 12;

            $dateFrom = $date->format('Y-m-d');
            $dateTo = $date->setTimestamp($createdTs)->format('Y-m-d');


            $board = $this->updateMetricsByBoard($board, $dateFrom, $dateTo, $userIds);
        }

        $dateFrom = $date->setTimestamp($nowTs)->format('Y-m-d');
        $dateTo = $date->setTimestamp($createdTs)->format('Y-m-d');

        $board = $this->updateMetricsByBoard($board, $dateFrom, $dateTo, $userIds);

        return $board->getMetrics();
    }

    /**
     * @param $board
     * @param $dateFrom
     * @param $dateTo
     * @param $userIds
     * @return mixed
     */
    private function updateMetricsByBoard($board, $dateFrom, $dateTo, $userIds)
    {
        $metricData = $this->metricsService->getMetrics($board->getSlug(), $dateFrom, $dateTo, $userIds);

        $metrics = $board->getMetrics();
        if (is_null($metrics)) {
            $metrics = new Metrics();
            $totalMetric = new TotalMetric();

            $totalMetric->setVisits($metricData->getVisits());
            $totalMetric->setInteractions($metricData->getInteractions());
            $totalMetric->setAvgTimeSpent($metricData->getAvgTimeSpent());
            $totalMetric->setPageViews($metricData->getPageViews());

            $metrics->setTotalMetric($totalMetric);

            dump(sprintf("created:total slug:%s, dateFrom:%s, dateTo:%s", $board->getSlug(), $dateFrom, $dateTo));
        } else {
            $totalMetric = $metrics->getTotalMetric();
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

            $metrics->setUpdatedAt(new \DateTime());
            $metrics->setTotalMetric($totalMetric);

            dump(sprintf("updated:total slug:%s, dateFrom:%s, dateTo:%s", $board->getSlug(), $dateFrom, $dateTo));
        }

        $board->setMetrics($metrics);

        return $board;
    }
}