<?php

namespace rtPiwikBundle\Services;


use rtPiwikBundle\Document\DailyMetric;
use rtPiwikBundle\Document\DailyPercentageChangeMetric;
use rtPiwikBundle\Document\Metrics;
use rtPiwikBundle\Document\TotalMetric;
use rtPiwikBundle\Document\WeeklyMetric;
use rtPiwikBundle\Document\WeeklyPercentageChangeMetric;

class CommonMetrics
{

    private $metricsService;

    function __construct($metricsService, $commonMetrics)
    {
        $this->metricsService = $metricsService;
    }

    public const DAILY_METRICS = 1;
    public const WEEKLY_METRICS = 1;

    public function get($board, $slug, \DateTime $yesterday, $userIds, $type)
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
        $dailyMetric = $this->getMetricByType($metrics, $metricsData, $type);
        $totalMetric = $this->getTotalMetric($metrics, $dailyMetric);
        $pctDailyChange = $this->getPercentageChangeMetric($metrics, $dailyMetric, $type);

        $metrics->setTotalMetric($totalMetric);
        $metrics->setDailyMetric($dailyMetric);
        $metrics->setDailyPercentageChange($pctDailyChange);

        $metrics->setUpdatedAt(new \DateTime());

        return $metrics;
    }

    /**
     * Get metrics data since last day and current day
     * @param $metrics
     * @param $metricsData
     * @return DailyPercentageChangeMetric
     */
    protected function getPercentageChangeMetric(Metrics $metrics, $metricsData, $type)
    {
        if ($type === self::DAILY_METRICS) {
            $prctChange = new DailyPercentageChangeMetric();
            $metricbyType = $metrics->getDailyMetric();
        }
        if ($type === self::WEEKLY_METRICS) {
            $prctChange = new WeeklyPercentageChangeMetric();
            $metricbyType = $metrics->getWeeklyMetric();
        }

        $visits = $metricbyType->getVisits() || 0;
        $interactions = $metricbyType->getInteractions() || 0;
        $sumTimeSpent = $metricbyType->getSumTimeSpent() || 0;
        $pageViews = $metricbyType->getPageViews() || 0;

        $avgTimeSpent = $visits > 0 ? round($sumTimeSpent / $visits) : 0;

        $prctChange->setVisits($this->getPrctChange($visits, $metricsData->getVisits()));
        $prctChange->setInteractions($this->getPrctChange($interactions, $metricsData->getInteractions()));
        $prctChange->setPageViews($this->getPrctChange($pageViews, $metricsData->getPageViews()));
        $prctChange->setSumTimeSpent($this->getPrctChange($avgTimeSpent, $metricsData->getAvgTimeSpent()));
        $prctChange->setAvgTimeSpent($this->getPrctChange($avgTimeSpent, $metricsData->getAvgTimeSpent()));

        return $prctChange;
    }

    /**
     * Get last day metrics data
     * @param $metrics
     * @param MetricModel $metricsData
     * @return
     */
    protected function getMetricByType($metrics, MetricModel $metricsData, $type)
    {
        if ($type === self::DAILY_METRICS) {
            $metricByType = $metrics->getDailyMetric();
            if ($metricByType === null) {
                $metricByType = new DailyMetric();
            }
        }

        if ($type === self::WEEKLY_METRICS) {
            $metricByType = $metrics->getWeeklyMetric();
            if ($metricByType === null) {
                $metricByType = new WeeklyMetric();
            }
        }

        // set lastDayMetric from piwik
        $metricByType->setVisits($metricsData->getVisits());
        $metricByType->setInteractions($metricsData->getInteractions());
        $metricByType->setPageViews($metricsData->getPageViews());
        $metricByType->setSumTimeSpent($metricsData->getSumTimeSpent());
        if ($metricByType->getVisits() > 0) {
            $avgTimeSpent = round($metricByType->getSumTimeSpent() / $metricByType->getVisits());
            $metricByType->setAvgTimeSpent($avgTimeSpent);
        }

        return $metricByType;
    }

    /**
     * Get total metrics since last day
     * @param Metrics $metrics
     * @param $metric
     * @return TotalMetric
     */
    protected function getTotalMetric(Metrics $metrics, $metric)
    {
        $totalMetric = $metrics->getTotalMetric();
        // if there is no metric repository
        if ($totalMetric === null) {
            $totalMetric = new TotalMetric();
            // set new total metric, because a new
            $totalMetric->setVisits($metric->getVisits());
            $totalMetric->setInteractions($metric->getInteractions());

            if ($metric->getVisits() > 0) {
                $avgTimeSpent = round($metric->getSumTimeSpent() / $metric->getVisits());
                $totalMetric->setAvgTimeSpent($avgTimeSpent);
            }

            $totalMetric->setPageViews($metric->getPageViews());
            $totalMetric->setSumTimeSpent($metric->getSumTimeSpent());
        } else {
            // get total
            // set total metrics
            $visits = $totalMetric->getVisits() + $metric->getVisits();
            $totalMetric->setVisits($visits);

            $interactions = $totalMetric->getInteractions() + $metric->getInteractions();
            $totalMetric->setInteractions($interactions);

            $sumTimeSpent = $totalMetric->getSumTimeSpent() + $metric->getSumTimeSpent();
            $totalMetric->setSumTimeSpent($sumTimeSpent);

            if ($visits > 0) {
                $avgTimeSpent = round($sumTimeSpent / $visits);
                $totalMetric->setAvgTimeSpent($avgTimeSpent);
            }

            $pageViews = $totalMetric->getPageViews() + $metric->getPageViews();
            $totalMetric->setPageViews($pageViews);
        }

        return $totalMetric;
    }

    protected function getPrctChange($y1, $y2)
    {
        $diff = $y2 - $y1;
        if ($y1 > 0) {
            $diff /= $y1;
        }

        return $diff * 100;
    }

}