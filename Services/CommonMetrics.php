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

    public function createMetricsClient($baseUri){
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
        return $this->metricsService ->getVisitedDocsMetrics($dateFrom, $dateTo, $userIds);
    }

    /**
     * @param $board
     * @param $slug
     * @param \DateTime $dateFrom
     * @param $dateTo
     * @param $userIds
     * @param $type
     * @return Metrics
     */
    public function get($board, $slug, $dateFrom, $dateTo, $userIds, $type)
    {
        $docMetrics = $board->getMetrics();
        // if there is no metric repository
        if ($docMetrics === null) {
            $docMetrics = new Metrics();
        }

        $calculatedMetrics = $this->metricsService->calculateMetrics($slug, $dateFrom, $dateTo, $userIds);
        $freshMetrics = $this->getMetricByType($docMetrics, $calculatedMetrics, $type);

        if ($type === self::DAILY_METRICS) {
            $totalMetric = $this->getTotalMetric($docMetrics, $freshMetrics);
            $docMetrics->setTotalMetric($totalMetric);
        }

        $prctChange = $this->getPercentageChangeMetric($docMetrics, $freshMetrics, $type);

        if ($type === self::DAILY_METRICS) {
            $docMetrics->setDailyMetric($freshMetrics);
            $docMetrics->setDailyPercentageChange($prctChange);

            $experienceViewed = 0;
            if ($docMetrics->getDailyMetric()->getVisits() > 0 && $board->getNumPages() > 0) {
                $experienceViewed = (($docMetrics->getDailyMetric()->getPageViews() / $docMetrics->getDailyMetric()->getVisits()) * 100) / $board->getNumPages();
            }

            $diff = $this->calcPrctDiff($experienceViewed, $docMetrics->getDailyPercentageChange()->getExperienceViewed());
            $docMetrics->getDailyMetric()->setExperienceViewed($experienceViewed);
            $docMetrics->getDailyPercentageChange()->setExperienceViewed($diff);
        }


        if ($type === self::WEEKLY_METRICS) {
            $docMetrics->setWeeklyMetric($freshMetrics);
            $docMetrics->setWeeklyPercentageChange($prctChange);

            $experienceViewed = 0;
            if ($docMetrics->getWeeklyMetric()->getVisits() > 0 && $board->getNumPages()) {
                $experienceViewed = (($docMetrics->getWeeklyMetric()->getPageViews() / $docMetrics->getWeeklyMetric()->getVisits()) * 100) / $board->getNumPages();
            }

            $diff = $this->calcPrctDiff($experienceViewed, $docMetrics->getWeeklyPercentageChange()->getExperienceViewed());
            $docMetrics->getWeeklyMetric()->setExperienceViewed($experienceViewed);
            $docMetrics->getWeeklyPercentageChange()->setExperienceViewed($diff);
        }

        $docMetrics->setUpdatedAt(new \DateTime());

        return $docMetrics;
    }

    /**
     * Get metrics data since last day and current day
     * @param Metrics $metrics
     * @param $metricsData
     * @param int $type
     * @return
     */
    protected function getPercentageChangeMetric(Metrics $metrics, $metricsData, $type)
    {
        if ($type === self::DAILY_METRICS) {
            $prctChange = new DailyPercentageChangeMetric();
            $metricByType = $this->getDailyMetric($metrics);
        }
        if ($type === self::WEEKLY_METRICS) {
            $prctChange = new WeeklyPercentageChangeMetric();
            $metricByType = $this->getWeeklyMetric($metrics);
        }

        $visits = is_numeric($metricByType->getVisits()) ? $metricByType->getVisits() : 0;
        $interactions = is_numeric($metricByType->getInteractions()) ? $metricByType->getInteractions() : 0;
        $sumTimeSpent = is_numeric($metricByType->getSumTimeSpent()) ? $metricByType->getSumTimeSpent() : 0;
        $pageViews = is_numeric($metricByType->getPageViews()) ? $metricByType->getPageViews() : 0;

        $avgTimeSpent = $visits > 0 ? round($sumTimeSpent / $visits) : 0;

        $prctChange->setVisits($this->calcPrctDiff($visits, $metricsData->getVisits()));
        $prctChange->setInteractions($this->calcPrctDiff($interactions, $metricsData->getInteractions()));
        $prctChange->setPageViews($this->calcPrctDiff($pageViews, $metricsData->getPageViews()));
        $prctChange->setSumTimeSpent($this->calcPrctDiff($avgTimeSpent, $metricsData->getAvgTimeSpent()));
        $prctChange->setAvgTimeSpent($this->calcPrctDiff($avgTimeSpent, $metricsData->getAvgTimeSpent()));

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
            $metricByType = $this->getDailyMetric($metrics);
        }

        if ($type === self::WEEKLY_METRICS) {
            $metricByType = $this->getWeeklyMetric($metrics);
        }

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

    public function calcPrctDiff($y1, $y2)
    {
        if ($y1 == 0 && $y2 == 0) {
            return 0;
        }

        return 100 * (($y1 - $y2) / (($y1 + $y2) / 2));
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