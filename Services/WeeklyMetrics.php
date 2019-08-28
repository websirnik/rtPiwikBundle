<?php
/**
 * Created by PhpStorm.
 * User: skews
 * Date: 03.04.2018
 * Time: 17:38
 */

namespace rtPiwikBundle\Services;


use rtPiwikBundle\Document\WeeklyMetric;
use rtPiwikBundle\Document\WeeklyPercentageChangeMetric;
use rtPiwikBundle\Document\Board;
use rtPiwikBundle\Document\Metrics;
use rtPiwikBundle\Document\TotalMetric;

class WeeklyMetrics
{
    private $metricsService;

    function __construct($metricsService)
    {
        $this->metricsService = $metricsService;
    }

    /**
     * Get all metrics since last week
     * @param $board
     * @param $slug
     * @param \DateTime $date
     * @param $userIds
     * @return Metrics
     */
    public function get($board, $slug, \DateTime $lastWeek, $userIds)
    {
        $now = new \DateTime();
        $dateTo = $now->format('Y-m-d');
        $dateFrom = $lastWeek->format('Y-m-d');

        $metrics = $board->getMetrics();
        // if there is no metric repository
        if ($metrics === null) {
            $metrics = new Metrics();
        }

        $metricsData = $this->metricsService->getMetrics($slug, $dateFrom, $dateTo, $userIds);
        $weeklyMetric = $this->getWeeklyMetric($metrics, $metricsData);
        $totalMetric = $this->getTotalMetric($metrics, $weeklyMetric);
        $daysNumber = $now->diff($lastWeek)->format("%a");
        $pctWeeklyChange = $this->getWeeklyPercentageChangeMetric($daysNumber, $metrics, $metricsData);

        $metrics->setTotalMetric($totalMetric);
        $metrics->setWeeklyMetric($weeklyMetric);
        $metrics->setWeeklyPercentageChange($pctWeeklyChange);

        $metrics->setUpdatedAt(new \DateTime());

        dump(sprintf("weekly metrics slug:%s, dateFrom:%s, dateTo:%s", $slug, $dateFrom, $dateTo));

        return $metrics;
    }

    /**
     * Get metrics data since last week and current week
     * @param $daysNumber
     * @param $metrics
     * @param MetricModel $weeklyMetric
     * @return WeeklyPercentageChangeMetric
     */
    private function getWeeklyPercentageChangeMetric($daysNumber, $metrics, MetricModel $weeklyMetric)
    {
        $weeklyPrctChange = $metrics->getWeeklyPercentageChange();
        if ($weeklyPrctChange === null) {
            $weeklyPrctChange = new WeeklyPercentageChangeMetric();
        }

//        $weeklyMetric->getVisits();
//        $weeklyMetric->getInteractions();
//        $weeklyMetric->getPageViews();
//        $weeklyMetric->getSumTimeSpent();



        return $weeklyPrctChange;
    }

    /**
     * Get last week metrics data
     * @param $metrics
     * @param MetricModel $metricsData
     * @return WeeklyMetric
     */
    private function getWeeklyMetric($metrics, MetricModel $metricsData)
    {
        $weeklyMetric = $metrics->getWeeklyMetric();
        if ($weeklyMetric === null) {
            $weeklyMetric = new WeeklyMetric();
        }
        // set lastWeekMetricData from piwik
        $weeklyMetric->setVisits($metricsData->getVisits());
        $weeklyMetric->setInteractions($metricsData->getInteractions());
        $weeklyMetric->setPageViews($metricsData->getPageViews());
        $weeklyMetric->setSumTimeSpent($metricsData->getSumTimeSpent());

        return $weeklyMetric;
    }

    /**
     * Get total metrics since last day
     * @param Metrics $metrics
     * @param WeeklyMetric $weeklyMetric
     * @return TotalMetric
     */
    private function getTotalMetric(Metrics $metrics, WeeklyMetric $weeklyMetric)
    {
        $totalMetric = $metrics->getTotalMetric();
        // if there is no metric repository
        if ($totalMetric === null) {
            $totalMetric = new TotalMetric();
            // set new total metric, because a new
            $totalMetric->setVisits($weeklyMetric->getVisits());
            $totalMetric->setInteractions($weeklyMetric->getInteractions());

            if ($weeklyMetric->getVisits() > 0) {
                $avgTimeSpent = round($weeklyMetric->getSumTimeSpent() / $weeklyMetric->getVisits());
                $totalMetric->setAvgTimeSpent($avgTimeSpent);
            }

            $totalMetric->setPageViews($weeklyMetric->getPageViews());
            $totalMetric->setSumTimeSpent($weeklyMetric->getSumTimeSpent());
        } else {
            // set updated total metrics
            $visits = $totalMetric->getVisits() + $weeklyMetric->getVisits();
            $totalMetric->setVisits($visits);

            $interactions = $totalMetric->getInteractions() + $weeklyMetric->getInteractions();
            $totalMetric->setInteractions($interactions);

            $sumTimeSpent = $totalMetric->getSumTimeSpent() + $weeklyMetric->getSumTimeSpent();
            $totalMetric->setSumTimeSpent($sumTimeSpent);

            if ($visits > 0) {
                $avgTimeSpent = round($sumTimeSpent / $visits);
                $totalMetric->setAvgTimeSpent($avgTimeSpent);
            }

            $pageViews = $totalMetric->getPageViews() + $weeklyMetric->getPageViews();
            $totalMetric->setPageViews($pageViews);
        }

        return $totalMetric;
    }


}