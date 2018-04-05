<?php
/**
 * Created by PhpStorm.
 * User: skews
 * Date: 03.04.2018
 * Time: 17:38
 */

namespace rtPiwikBundle\Services;


use rtPiwikBundle\Document\LastWeekMetric;
use rtPiwikBundle\Document\PercentageChangeLastWeekMetric;
use rtPiwikBundle\Document\Board;
use rtPiwikBundle\Document\Metrics;

class LastWeekMetrics
{
    private $metricsService;

    function __construct()
    {
        $this->metricsService = new MetricsService();
    }

    /**
     * Get all metrics since last week
     * @param Board $board
     * @param \DateTime $date
     * @return Metrics
     */
    public function get(Board $board, \DateTime $date)
    {
        $now = new \DateTime();
        $today = $now->format('Y-m-d');
        $dateFrom = $date->format('Y-m-d');

        $lastWeekMetricData = $this->metricsService->getMetrics($board->getSlug(), $dateFrom, $today);

        $lastWeekMetric = $this->getLastWeekMetric($board, $lastWeekMetricData);

        $percentageChangeLastWeek = $this->getPercentageChangeLastWeekMetric($board, $lastWeekMetricData);

        $metrics = $board->getMetrics();
        if (is_null($metrics)) {
            $metrics = new Metrics();

            dump(
                sprintf(
                    "created:daily:last_week slug:%s, dateFrom:%s, dateTo:%s",
                    $board->getSlug(),
                    $dateFrom,
                    $today
                )
            );
        } else {
            $metrics->setLastWeekMetric($lastWeekMetric);
            $metrics->setPercentageChangeLastWeek($percentageChangeLastWeek);

            dump(
                sprintf(
                    "updated:daily:last_week slug:%s, dateFrom:%s, dateTo:%s",
                    $board->getSlug(),
                    $dateFrom,
                    $today
                )
            );
        }

        return $metrics;
    }

    /**
     * Get metrics data since last week and current week
     * @param Board $board
     * @param MetricModel $lastWeekMetric
     * @return PercentageChangeLastWeekMetric
     */
    private function getPercentageChangeLastWeekMetric(Board $board, MetricModel $lastWeekMetric)
    {
        // get percentageChangeLastDay from current metricRepo TODO could be check inside mode ?
        $percentageChangeLastWeek = $board->getMetrics()->getPercentageChangeLastWeek();
        if (is_null($percentageChangeLastWeek)) {
            $percentageChangeLastWeek = new PercentageChangeLastWeekMetric();
        }

        // set percentageChangeLastDay from piwik and current value from local DB
        $visits = ($percentageChangeLastWeek->getVisits() - $lastWeekMetric->getVisits()) / 100;
        $percentageChangeLastWeek->setVisits($visits);

        $interactions = ($percentageChangeLastWeek->getInteractions() - $lastWeekMetric->getInteractions()) / 100;
        $percentageChangeLastWeek->setInteractions($interactions);

        $avgTimeSpent = ($percentageChangeLastWeek->getAvgTimeSpent() - $lastWeekMetric->getAvgTimeSpent()) / 100;
        $percentageChangeLastWeek->setAvgTimeSpent($avgTimeSpent);

        $pageViews = ($percentageChangeLastWeek->getPageViews() - $lastWeekMetric->getPageViews()) / 100;
        $percentageChangeLastWeek->setPageViews($pageViews);

        return $percentageChangeLastWeek;
    }

    /**
     * Get last week metrics data
     * @param Board $board
     * @param MetricModel $lastWeekMetricData
     * @return LastWeekMetric
     */
    private function getLastWeekMetric(Board $board, MetricModel $lastWeekMetricData)
    {
        // get lastWeekMetric from current metricRepo TODO could be check inside mode ?
        $lastWeekMetric = $board->getMetrics()->getLastWeekMetric();
        if (is_null($lastWeekMetric)) {
            $lastWeekMetric = new LastWeekMetric();
        }

        // set lastWeekMetricData from piwik
        $lastWeekMetric->setVisits($lastWeekMetricData->getVisits());
        $lastWeekMetric->setInteractions($lastWeekMetricData->getInteractions());
        $lastWeekMetric->setAvgTimeSpent($lastWeekMetricData->getAvgTimeSpent());
        $lastWeekMetric->setPageViews($lastWeekMetricData->getPageViews());

        return $lastWeekMetric;
    }


}