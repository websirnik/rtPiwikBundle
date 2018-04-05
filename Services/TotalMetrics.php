<?php
/**
 * Created by PhpStorm.
 * User: skews
 * Date: 03.04.2018
 * Time: 18:01
 */

namespace rtPiwikBundle\Services;

use rtPiwikBundle\Document\Metrics;
use rtPiwikBundle\Document\TotalMetric;

class TotalMetrics
{
    private $metricsService;

    function __construct()
    {
        $this->metricsService = new MetricsService();
    }

    /**
     * Get total metrics
     * @param \rtPiwikBundle\Document\Board $board
     * @return Metrics
     */
    public function get(\rtPiwikBundle\Document\Board $board)
    {
        $date = new \DateTime();
        $now = $date->getTimestamp();
        $created = $date->modify($board->getCreated()->format('Y-m-d'))->getTimestamp();

        while ($now > $created) {
            $created = $created + 60 * 60 * 60 * 12;

            $dateFrom = $date->format('Y-m-d');
            $dateTo = $date->setTimestamp($created)->format('Y-m-d');

            $this->updateMetricsByBoard($board, $dateFrom, $dateTo);
        }

        $dateFrom = $date->setTimestamp($now)->format('Y-m-d');
        $dateTo = $date->setTimestamp($created)->format('Y-m-d');

        $this->updateMetricsByBoard($board, $dateFrom, $dateTo);

        return $board->getMetrics();
    }

    /**
     * @param \rtPiwikBundle\Document\Board $board
     * @param $dateFrom
     * @param $dateTo
     * @return Metrics
     */
    private function updateMetricsByBoard(\rtPiwikBundle\Document\Board $board, $dateFrom, $dateTo)
    {
        $metricData = $this->metricsService->getMetrics($board->getSlug(), $dateFrom, $dateTo);
        $metrics = $board->getMetrics();
        if (is_null($metrics)) {
            $metrics = new Metrics();
            $totalMetric = new TotalMetric();

            $totalMetric->setVisits($metricData->getVisits());
            $totalMetric->setInteractions($metricData->getInteractions());
            $totalMetric->setAvgTimeSpent($metricData->getAvgTimeSpent());
            $totalMetric->setPageViews($metricData->getPageViews());

            $metrics->setTotalMetric($totalMetric);

            dump(sprintf("created:total slug:%s, dateFrom:%s, dateTo:%s", $board->getSlug(), $dateFrom, $dateTo));
        } else {
            $totalMetric = $metrics->getTotalMetric();
            if (is_null($totalMetric)) {
                $totalMetric = new TotalMetric();
            }

            $visits = $totalMetric->getVisits() + $metricData->getVisits();
            $totalMetric->setVisits($visits);

            $interactions = $totalMetric->getInteractions() + $metricData->getInteractions();
            $totalMetric->setInteractions($interactions);

            if ($metricData->getAvgTimeSpent() > 0) {
                $avgTimeSpent = round(($totalMetric->getAvgTimeSpent() + $metricData->getAvgTimeSpent()) / 2);
                $totalMetric->setAvgTimeSpent($avgTimeSpent);
            }

            $pageViews = $totalMetric->getPageViews() + $metricData->getPageViews();
            $totalMetric->setPageViews($pageViews);

            $metrics->setUpdatedAt(new \DateTime());
            $metrics->setTotalMetric($totalMetric);

            dump(sprintf("updated:total slug:%s, dateFrom:%s, dateTo:%s", $board->getSlug(), $dateFrom, $dateTo));
        }

        return $metrics;
    }


}