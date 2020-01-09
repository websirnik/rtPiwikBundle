<?php

namespace rtPiwikBundle\Services;

use rtPiwikBundle\Document\Metrics;

class WeeklyMetrics
{
    private $commonMetrics;

    /**
     * WeeklyMetrics constructor.
     * @param CommonMetrics $commonMetrics
     */
    public function __construct($commonMetrics)
    {
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
    public function get($board, $slug, \DateTime $date, $userIds)
    {
        $metrics = $this->commonMetrics->get($board, $slug, $date, $userIds, CommonMetrics::WEEKLY_METRICS);

        dump(sprintf('weekly metrics slug:%s', $slug));

        return $metrics;
    }
}