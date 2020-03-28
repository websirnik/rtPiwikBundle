<?php

namespace rtPiwikBundle\Services;

use rtPiwikBundle\Document\DailyMetric;
use rtPiwikBundle\Document\DailyPercentageChangeMetric;
use rtPiwikBundle\Document\Metrics;
use rtPiwikBundle\Document\TotalMetric;
use rtPiwikBundle\Document\WeeklyMetric;
use rtPiwikBundle\Document\WeeklyPercentageChangeMetric;

class CommonMetrics implements CommonMetricsInt
{
    private $metricsService;

    /**
     * CommonMetrics constructor.
     * @param MetricsService $metricsService
     */
    function __construct($metricsService)
    {
        $this->metricsService = $metricsService;
    }

    public const DAILY_METRICS = 1;
    public const WEEKLY_METRICS = 2;

    public function createMetricsClient($baseUri)
    {
        $this->metricsService->setMetricsClient($baseUri);
    }

    /**
     * @param $dateFrom
     * @param $dateTo
     * @param $userIds
     * @return array
     */
    public function getVisitedDocsMetrics($dateFrom, $dateTo, $userIds): array
    {
        return $this->metricsService->getVisitedDocsMetrics($dateFrom, $dateTo, $userIds);
    }

    /**
     * @param $board
     * @param $slug
     * @param $dateFrom
     * @param $dateTo
     * @param $userIds
     * @param $type
     * @return Metrics
     */
    public function get($board, $slug, $dateFrom, $dateTo, $userIds, $type)
    {
        $numPages = count($board->getBoardResources()) ?: 0;
        $oldMetrics = $board->getMetrics();
        // if there is no metric repository
        if ($oldMetrics === null) {
            $oldMetrics = new Metrics();
        }

        $calculatedMetrics = $this->metricsService->calculateMetrics($slug, $dateFrom, $dateTo, $userIds);
        $newMetrics = $this->getMetricByType($oldMetrics, $calculatedMetrics, $type, $numPages);

        if ($type === self::DAILY_METRICS) {
            $totalMetric = $this->getTotalMetric($oldMetrics, $newMetrics, $numPages);
            $totalMetric->setUpdatedAt(new \DateTime());
            $oldMetrics->setTotalMetric($totalMetric);
        }

        $prctChange = $this->getPercentageChangeMetric($oldMetrics, $newMetrics, $type);

        if ($type === self::DAILY_METRICS) {
            $oldMetrics->setDailyMetric($newMetrics);
            $oldMetrics->setDailyPercentageChange($prctChange);

            $oldMetrics->getDailyMetric()->setUpdatedAt(new \DateTime());
            $oldMetrics->getDailyPercentageChange()->setUpdatedAt(new \DateTime());
        }


        if ($type === self::WEEKLY_METRICS) {
            $oldMetrics->setWeeklyMetric($newMetrics);
            $oldMetrics->setWeeklyPercentageChange($prctChange);

            $oldMetrics->getWeeklyMetric()->setUpdatedAt(new \DateTime());
            $oldMetrics->getWeeklyPercentageChange()->setUpdatedAt(new \DateTime());
        }


        return $oldMetrics;
    }

    /**
     * Get metrics data since last day and current day
     * @param Metrics $oldMetrics
     * @param $newMetrics
     * @param int $type
     * @return
     */
    protected function getPercentageChangeMetric(Metrics $oldMetrics, $newMetrics, $type)
    {
        if ($type === self::DAILY_METRICS) {
            $prctChange = new DailyPercentageChangeMetric();
            $metricByType = $this->getDailyMetric($oldMetrics);
        }
        if ($type === self::WEEKLY_METRICS) {
            $prctChange = new WeeklyPercentageChangeMetric();
            $metricByType = $this->getWeeklyMetric($oldMetrics);
        }

        $visits = is_numeric($metricByType->getVisits()) ? $metricByType->getVisits() : 0;
        $interactions = is_numeric($metricByType->getInteractions()) ? $metricByType->getInteractions() : 0;
        $sumTimeSpent = is_numeric($metricByType->getSumTimeSpent()) ? $metricByType->getSumTimeSpent() : 0;
        $pageViews = is_numeric($metricByType->getPageViews()) ? $metricByType->getPageViews() : 0;

        $avgTimeSpent = $visits > 0 ? round($sumTimeSpent / $visits) : 0;

        $prctChange->setVisits($this->calcPrctDiff($visits, $newMetrics->getVisits()));
        $prctChange->setInteractions($this->calcPrctDiff($interactions, $newMetrics->getInteractions()));
        $prctChange->setPageViews($this->calcPrctDiff($pageViews, $newMetrics->getPageViews()));
        $prctChange->setSumTimeSpent($this->calcPrctDiff($sumTimeSpent, $newMetrics->getSumTimeSpent()));
        $prctChange->setAvgTimeSpent($this->calcPrctDiff($avgTimeSpent, $newMetrics->getAvgTimeSpent()));
        $prctChange->setExperienceViewed($this->calcPrctDiff($metricByType->getExperienceViewed(), $newMetrics->getExperienceViewed()));

        return $prctChange;
    }

    /**
     * Get last day metrics data
     * @param $oldMetrics
     * @param MetricModel $metricsData
     * @return
     */
    protected function getMetricByType($oldMetrics, MetricModel $metricsData, $type, $numPages = 0)
    {
        if ($type === self::DAILY_METRICS) {
            $metricByType = $this->getDailyMetric($oldMetrics);
        }

        if ($type === self::WEEKLY_METRICS) {
            $metricByType = $this->getWeeklyMetric($oldMetrics);
        }

        $metricByType->setVisits($metricsData->getVisits());
        $metricByType->setInteractions($metricsData->getInteractions());
        $metricByType->setPageViews($metricsData->getPageViews());
        $metricByType->setSumTimeSpent($metricsData->getSumTimeSpent());
        if ($metricByType->getVisits() > 0) {
            $avgTimeSpent = round($metricByType->getSumTimeSpent() / $metricByType->getVisits());
            $metricByType->setAvgTimeSpent($avgTimeSpent);
        }

        $experienceViewed = $this->calcExperienceViewed($numPages, $metricByType->getVisits(), $metricByType->getPageViews());
        $metricByType->setExperienceViewed($experienceViewed);

        return $metricByType;
    }

    /**
     * Get total metrics since last day
     * @param Metrics $oldMetrics
     * @param $newMetrics
     * @return TotalMetric
     */
    protected function getTotalMetric(Metrics $oldMetrics, $newMetrics, $numPages = 0)
    {
        $oldTotalMetric = $oldMetrics->getTotalMetric();
        // if there is no metric repository
        if ($oldTotalMetric === null) {
            $oldTotalMetric = new TotalMetric();
            // set new total metric, because a new
            $oldTotalMetric->setVisits($newMetrics->getVisits());
            $oldTotalMetric->setInteractions($newMetrics->getInteractions());

            if ($newMetrics->getVisits() > 0) {
                $avgTimeSpent = round($newMetrics->getSumTimeSpent() / $newMetrics->getVisits());
                $oldTotalMetric->setAvgTimeSpent($avgTimeSpent);
            }

            $oldTotalMetric->setPageViews($newMetrics->getPageViews());
            $oldTotalMetric->setSumTimeSpent($newMetrics->getSumTimeSpent());
        } else {
            // get total
            // set total metrics
            $visits = $oldTotalMetric->getVisits() + $newMetrics->getVisits();
            $oldTotalMetric->setVisits($visits);

            $interactions = $oldTotalMetric->getInteractions() + $newMetrics->getInteractions();
            $oldTotalMetric->setInteractions($interactions);

            $sumTimeSpent = $oldTotalMetric->getSumTimeSpent() + $newMetrics->getSumTimeSpent();
            $oldTotalMetric->setSumTimeSpent($sumTimeSpent);

            if ($visits > 0) {
                $avgTimeSpent = round($sumTimeSpent / $visits);
                $oldTotalMetric->setAvgTimeSpent($avgTimeSpent);
            }

            $pageViews = $oldTotalMetric->getPageViews() + $newMetrics->getPageViews();
            $oldTotalMetric->setPageViews($pageViews);
        }

        $experienceViewed = $this->calcExperienceViewed($numPages, $oldTotalMetric->getVisits(), $oldTotalMetric->getPageViews());
        $oldTotalMetric->setExperienceViewed($experienceViewed);

        return $oldTotalMetric;
    }

    private function calcExperienceViewed($numPages, $visits, $pagesViews)
    {
        $experienceViewed = 0;
        if ($numPages && $visits) {
            $experienceViewed = $pagesViews / $visits * 100 / $numPages;
        }

        return $experienceViewed > 100 ? 100 : $experienceViewed;
    }

    public function calcPrctDiff($y1, $y2)
    {
        if ($y1 === 0) {
            return 0;
        }

        return (($y2 - $y1) / $y1) * 100;
    }

    protected function getDailyMetric($metrics)
    {
        $metric = $metrics->getDailyMetric();
        if ($metric === null) {
            $metric = new DailyMetric();
        }

        return $metric;
    }

    protected function getWeeklyMetric($metrics)
    {
        $metric = $metrics->getWeeklyMetric();
        if ($metric === null) {
            $metric = new WeeklyMetric();
        }

        return $metric;
    }

}