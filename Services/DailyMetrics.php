<?php

namespace rtPiwikBundle\Services;

use rtPiwikBundle\Document\Metrics;

class DailyMetrics
{
    private $commonMetrics;

    /**
     * DailyMetrics constructor.
     * @param CommonMetrics $commonMetrics
     */
    public function __construct($commonMetrics)
    {
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

