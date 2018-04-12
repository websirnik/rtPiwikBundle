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
use Symfony\Component\Config\Definition\Exception\Exception;

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
     * @param bool $reCalculate
     * @return Metrics
     */

    public function get($board, \DateTime $date, $userIds, $reCalculate = false)
    {
        $now = new \DateTime();
        $nowTs = $now->getTimestamp();
        $createdTs = $date->getTimestamp();


        if (!$reCalculate) {
            $metrics = $board->getMetrics();
        } else {
            $metrics = new Metrics();
        }

        $lastUpdated = $metrics->getLastUpdated();
        $lastUpdatedTs = $lastUpdated->getTimestamp();

        if($lastUpdatedTs > $nowTs - 60 * 60 * 24){
            return $metrics;
        }

        while ($nowTs > $createdTs) {
            // each month
            $createdTs = $createdTs + 60 * 60 * 60 * 12;

            $dateFrom = $date->format('Y-m-d');
            $dateTo = $date->setTimestamp($createdTs)->format('Y-m-d');

            $metrics = $this->updateMetricsByBoard($metrics, $board, $dateFrom, $dateTo, $userIds);
        }

        $dateFrom = $date->setTimestamp($nowTs)->format('Y-m-d');
        $dateTo = $date->setTimestamp($createdTs)->format('Y-m-d');

        // need to do again because the last batch of data should be updated
        $metrics = $this->updateMetricsByBoard($metrics, $board, $dateFrom, $dateTo, $userIds);

        $metrics->setLastUpdated(new \DateTime());

        return $metrics;
    }

    /**
     * @param $metrics
     * @param $board
     * @param $dateFrom
     * @param $dateTo
     * @param $userIds
     * @return mixed
     */
    private function updateMetricsByBoard($metrics, $board, $dateFrom, $dateTo, $userIds)
    {
        $metricData = $this->metricsService->getMetrics($board->getSlug(), $dateFrom, $dateTo, $userIds);

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

        return $metrics;
    }
}