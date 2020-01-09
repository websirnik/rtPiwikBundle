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
     * @param \DateTime $date
     * @param $userIds
     * @return Metrics
     */
    public function get($board, $slug, \DateTime $date, $userIds)
    {
        $metrics = $this->commonMetrics->get($board, $slug, $date, $userIds, CommonMetrics::DAILY_METRICS);

        dump(sprintf('daily metrics slug:%s', $slug));

        return $metrics;
    }


}

