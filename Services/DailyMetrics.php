<?php

namespace rtPiwikBundle\Services;

use rtPiwikBundle\Document\DailyMetric;
use rtPiwikBundle\Document\Metrics;
use rtPiwikBundle\Document\DailyPercentageChangeMetric;
use rtPiwikBundle\Document\TotalMetric;

class DailyMetrics
{
    private $metricsService;
    private $commonMetrics;

    function __construct($metricsService, $commonMetrics)
    {
        $this->metricsService = $metricsService;
        $this->commonMetrics = $commonMetrics;
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
        $metrics = $this->commonMetrics->get($board, $slug, $yesterday, $userIds, CommonMetrics::DAILY_METRICS);

        dump(sprintf('daily metrics slug:%s', $slug));

        return $metrics;
    }


}

