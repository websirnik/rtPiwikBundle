<?php
/**
 * Created by PhpStorm.
 * User: skews
 * Date: 03.04.2018
 * Time: 17:34
 */

namespace rtPiwikBundle\Services;


use rtPiwikBundle\Document\DailyMetric;
use rtPiwikBundle\Document\Metrics;
use rtPiwikBundle\Document\DailyPercentageChangeMetric;
use rtPiwikBundle\Document\TotalMetric;

class DailyMetrics
{
    private $metricsService;

    function __construct($metricsService)
    {
        $this->metricsService = $metricsService;
    }

    /**
     * Get all metrics since last day
     * @oaram $board
     * @param $slug
     * @param \DateTime $yesterday
     * @param $userIds
     * @return Metrics
     */
    public function get($board, $slug, \DateTime $yesterday, $userIds)
    {
        $now = new \DateTime();
        $dateTo = $now->format('Y-m-d');
        $dateFrom = $yesterday->format('Y-m-d');

        $metrics = $board->getMetrics();
        // if there is no metric repository
        if ($metrics === null) {
            $metrics = new Metrics();
        }

        $metricsData = $this->metricsService->getMetrics($slug, $dateFrom, $dateTo, $userIds);
        $dailyMetric = $this->getDailyMetric($metrics, $metricsData);
        $totalMetric = $this->getTotalMetric($metrics, $dailyMetric);
        $diff = $dateTo->diff($dateFrom)->format("%a");
        $pctDailyChange = $this->getDailyPercentageChangeMetric($diff, $metrics, $metricsData);

        $metrics->setTotalMetric($totalMetric);
        $metrics->setDailyMetric($dailyMetric);
        $metrics->setDailyPercentageChange($pctDailyChange);

        $metrics->setUpdatedAt(new \DateTime());

        dump(sprintf("daily metrics slug:%s, dateFrom:%s, dateTo:%s", $slug, $dateFrom, $dateTo));

        return $metrics;
    }


    /**
     * Get metrics data since last day and current day
     * @param $daysNumber
     * @param $metrics
     * @param MetricModel $dailyMetric
     * @return DailyPercentageChangeMetric
     */
    private function getDailyPercentageChangeMetric($daysNumber, $metrics, MetricModel $dailyMetric)
    {
        $dailyPrctChange = $metrics->getDailyPercentageChange();
        if ($dailyPrctChange === null) {
            $dailyPrctChange = new DailyPercentageChangeMetric();
        }

//        $dailyMetric->getVisits();
//        $dailyMetric->getInteractions();
//        $dailyMetric->getPageViews();
//        $dailyMetric->getSumTimeSpent();


        return $dailyPrctChange;
    }

    /**
     * Get last day metrics data
     * @param $metrics
     * @param MetricModel $metricsData
     * @return DailyMetric
     */
    private function getDailyMetric($metrics, MetricModel $metricsData): DailyMetric
    {
        $dailyMetric = $metrics->getDailyMetric();
        if ($dailyMetric === null) {
            $dailyMetric = new DailyMetric();
        }
        // set lastDayMetric from piwik
        $dailyMetric->setVisits($metricsData->getVisits());
        $dailyMetric->setInteractions($metricsData->getInteractions());
        $dailyMetric->setPageViews($metricsData->getPageViews());
        $dailyMetric->setSumTimeSpent($metricsData->getSumTimeSpent());

        return $dailyMetric;
    }

    /**
     * Get total metrics since last day
     * @param Metrics $metrics
     * @param DailyMetric $dailyMetric
     * @return TotalMetric
     */
    private function getTotalMetric(Metrics $metrics, DailyMetric $dailyMetric)
    {
        $totalMetric = $metrics->getTotalMetric();
        // if there is no metric repository
        if ($totalMetric === null) {
            $totalMetric = new TotalMetric();
            // set new total metric, because a new
            $totalMetric->setVisits($dailyMetric->getVisits());
            $totalMetric->setInteractions($dailyMetric->getInteractions());

            if ($dailyMetric->getVisits() > 0) {
                $avgTimeSpent = round($dailyMetric->getSumTimeSpent() / $dailyMetric->getVisits());
                $totalMetric->setAvgTimeSpent($avgTimeSpent);
            }

            $totalMetric->setPageViews($dailyMetric->getPageViews());
            $totalMetric->setSumTimeSpent($dailyMetric->getSumTimeSpent());
        } else {
            // get total
            // set total metrics
            $visits = $totalMetric->getVisits() + $dailyMetric->getVisits();
            $totalMetric->setVisits($visits);

            $interactions = $totalMetric->getInteractions() + $dailyMetric->getInteractions();
            $totalMetric->setInteractions($interactions);

            $sumTimeSpent = $totalMetric->getSumTimeSpent() + $dailyMetric->getSumTimeSpent();
            $totalMetric->setSumTimeSpent($sumTimeSpent);

            if ($visits > 0) {
                $avgTimeSpent = round($sumTimeSpent / $visits);
                $totalMetric->setAvgTimeSpent($avgTimeSpent);
            }

            $pageViews = $totalMetric->getPageViews() + $dailyMetric->getPageViews();
            $totalMetric->setPageViews($pageViews);
        }

        return $totalMetric;
    }
}

