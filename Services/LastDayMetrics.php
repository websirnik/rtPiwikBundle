<?php
/**
 * Created by PhpStorm.
 * User: skews
 * Date: 03.04.2018
 * Time: 17:34
 */

namespace rtPiwikBundle\Services;


use rtPiwikBundle\Document\LastDayMetric;
use rtPiwikBundle\Document\Metrics;
use rtPiwikBundle\Document\PercentageChangeLastDayMetric;
use rtPiwikBundle\Document\TotalMetric;

class LastDayMetrics
{
    private $metricsService;

    function __construct($metricsService)
    {
        $this->metricsService = $metricsService;
    }

    /**
     * Get all metrics since last day
     * @param \DateTime $date
     * @return Metrics
     */
    public function get($board, \DateTime $date, $userIds)
    {
        $now = new \DateTime();
        $today = $now->format('Y-m-d');
        $dateFrom = $date->format('Y-m-d');

        $metrics = $board->getMetrics();
        // if there is no metric repository
        if (is_null($metrics)) {
            $metrics = new Metrics();
            $board->setMetrics($metrics);
        }

        $metricsData = $this->metricsService->getMetrics($board->getSlug(), $dateFrom, $today, $userIds);
        $lastDayMetric = $this->getLastDayMetric($board, $metricsData);
        $totalMetric = $this->getTotalMetric($metrics, $lastDayMetric);
        $percentageChangeLastDay = $this->getPercentageChangeLastDayMetric($board, $metricsData);

        $metrics->setTotalMetric($totalMetric);
        $metrics->setLastDayMetric($lastDayMetric);
        $metrics->setPercentageChangeLastDay($percentageChangeLastDay);

        dump(sprintf("daily:last_day slug:%s, dateFrom:%s, dateTo:%s", $board->getSlug(), $dateFrom, $today));

        return $metrics;
    }


    /**
     * Get total metrics since last day
     * @param Metrics $metrics
     * @param LastDayMetric $lastDayMetric
     * @return TotalMetric
     */
    private
    function getTotalMetric(Metrics $metrics, LastDayMetric $lastDayMetric) {
        $totalMetric = $metrics->getTotalMetric();
        // if there is no metric repository
        if (is_null($totalMetric)) {
            $totalMetric = new TotalMetric();
            // set new total metric, because a new
            $totalMetric->setVisits($lastDayMetric->getVisits());
            $totalMetric->setInteractions($lastDayMetric->getInteractions());
            $totalMetric->setAvgTimeSpent($lastDayMetric->getAvgTimeSpent());
            $totalMetric->setPageViews($lastDayMetric->getPageViews());
        } else {
            // set updated total metrics
            $visits = $totalMetric->getVisits() + $lastDayMetric->getVisits();
            $totalMetric->setVisits($visits);

            $interactions = $totalMetric->getInteractions() + $lastDayMetric->getInteractions();
            $totalMetric->setInteractions($interactions);

            if ($lastDayMetric->getAvgTimeSpent() > 0) {
                $avgTimeSpent = round(($totalMetric->getAvgTimeSpent() + $lastDayMetric->getAvgTimeSpent()) / 2);
                $totalMetric->setAvgTimeSpent($avgTimeSpent);
            }

            $pageViews = $totalMetric->getPageViews() + $lastDayMetric->getPageViews();
            $totalMetric->setPageViews($pageViews);
        }

        return $totalMetric;
    }

    /**
     * Get metrics data since last day and current day
     * @param $board
     * @param MetricModel $lastDayMetric
     * @return PercentageChangeLastDayMetric
     */
    private
    function getPercentageChangeLastDayMetric($board, MetricModel $lastDayMetric) {
        // get percentageChangeLastDay from current metricRepo TODO could be check inside mode ?
        $percentageChangeLastDay = $board->getMetrics()->getPercentageChangeLastDay();
        if (is_null($percentageChangeLastDay)) {
            $percentageChangeLastDay = new PercentageChangeLastDayMetric();
        }

        // set percentageChangeLastDay from piwik and current value from local DB
        $visits = ($percentageChangeLastDay->getVisits() - $lastDayMetric->getVisits()) / 100;
        $percentageChangeLastDay->setVisits($visits);

        $interactions = ($percentageChangeLastDay->getInteractions() - $lastDayMetric->getInteractions()) / 100;
        $percentageChangeLastDay->setInteractions($interactions);

        $avgTimeSpent = ($percentageChangeLastDay->getAvgTimeSpent() - $lastDayMetric->getAvgTimeSpent()) / 100;
        $percentageChangeLastDay->setAvgTimeSpent($avgTimeSpent);

        $pageViews = ($percentageChangeLastDay->getPageViews() - $lastDayMetric->getPageViews()) / 100;
        $percentageChangeLastDay->setPageViews($pageViews);

        return $percentageChangeLastDay;
    }

    /**
     * Get last day metrics data
     * @param $board
     * @param $lastDayMetricData
     * @return LastDayMetric
     */
    private function getLastDayMetric($board, MetricModel $lastDayMetricData) {
        $lastDayMetric = $board->getMetrics()->getLastDayMetric();
        if (is_null($lastDayMetric)) {
            $lastDayMetric = new LastDayMetric();
        }

        // set lastDayMetric from piwik
        $lastDayMetric->setVisits($lastDayMetricData->getVisits());
        $lastDayMetric->setInteractions($lastDayMetricData->getInteractions());
        $lastDayMetric->setAvgTimeSpent($lastDayMetricData->getAvgTimeSpent());
        $lastDayMetric->setPageViews($lastDayMetricData->getPageViews());

        return $lastDayMetric;
    }
}

