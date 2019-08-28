<?php

namespace rtPiwikBundle\Services;


use rtPiwikBundle\Document\WeeklyMetric;
use rtPiwikBundle\Document\WeeklyPercentageChangeMetric;
use rtPiwikBundle\Document\Metrics;
use rtPiwikBundle\Document\TotalMetric;

class WeeklyMetrics
{
    private $metricsService;
    private $commonMetrics;

    function __construct($metricsService, $commonMetrics)
    {
        $this->metricsService = $metricsService;
        $this->commonMetrics = $commonMetrics;
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
        $metrics = $this->commonMetrics->get($board, $slug, $lastWeek, $userIds, CommonMetrics::WEEKLY_METRICS);

        dump(sprintf('weekly metrics slug:%s', $slug));

        return $metrics;
    }
}