<?php


namespace rtPiwikBundle\Services;

use rtPiwikBundle\Document\Metrics;
use rtPiwikBundle\Document\TotalMetric;

class TotalMetrics implements TotalMetricInt {
    /* @var MetricsService $metricsService */
    private $metricsService;

    function __construct($metricsService) {
        $this->metricsService = $metricsService;
    }

    public function createMetricsClient($baseUri){
        $this->metricsService->setMetricsClient($baseUri);
    }

    /**
     * Get total metrics
     * @param $board
     * @param $slug
     * @param \DateTime $date
     * @param $userIds
     * @param bool $reCalculate
     * @return Metrics
     */
    public function get($board, $slug, \DateTime $date, $userIds, $reCalculate = false) {
        $dateFrom = clone $date;
        $dateTo = null;
        $yesterday = (new \DateTime())->setTime(0, 0)->modify('-1 day');

        if (!$reCalculate && $metrics = $board->getMetrics()) {

            if ($lastCalculated = $metrics->getLastCalculated()) {
                if ($lastCalculated >= $yesterday) {
                    return $metrics;
                }
                $dateFrom = $lastCalculated;
            }

        } else {
            $metrics = new Metrics();
        }

        while ($dateTo !== $yesterday) {

            $dateTmp = clone $dateFrom;
            $dateTo = $dateTmp->modify('+1 month');

            if ($dateTo > $yesterday) {
                $dateTo = $yesterday;
            }
            $metrics = $this->updateMetricsByBoard(
                $slug,
                $metrics,
                $board,
                $dateFrom->format('Y-m-d'),
                $dateTo->format('Y-m-d'),
                $userIds
            );

            $dateFrom = $dateTo;
            // do not counted twice the same data we move to next day from new period
            $dateFrom->modify('+1 day');
            $metrics->setLastCalculated($dateTo);

        }

        return $metrics;
    }

    /**
     * updateMetricsByBoard returns updated or calcutaed total metrics
     * @param $slug - slug of the document
     * @param Metrics $metrics - model of metrics
     * @param $board - object of the document
     * @param $dateFrom - date from
     * @param $dateTo - date to
     * @param $userIds - array of usr's ids
     * @return Metrics - object of calculated total metrics
     */
    public function updateMetricsByBoard($slug, Metrics $metrics, $board, $dateFrom, $dateTo, $userIds) {
        // get metrics data by slug, date and user's ids
        $metricData = $this->metricsService->calculateMetrics($slug, $dateFrom, $dateTo, $userIds);
        // if data new (empty)
        if ($metrics === null) {
            // created new instances
            $metrics = new Metrics();
            $totalMetric = new TotalMetric();
            // collect total metrics
            $totalMetric->setVisits($metricData->getVisits());
            $totalMetric->setInteractions($metricData->getInteractions());

            if($metricData->getVisits() > 0){
                $avgTimeSpent = round( $metricData->getSumTimeSpent()  / $metricData->getVisits());
                $totalMetric->setAvgTimeSpent($avgTimeSpent);
            }

            $totalMetric->setPageViews($metricData->getPageViews());
            $totalMetric->setSumTimeSpent($metricData->getSumTimeSpent());
            // set new total metrics
            $metrics->setTotalMetric($totalMetric);

            dump(sprintf("created:total slug:%s, dateFrom:%s, dateTo:%s", $slug, $dateFrom, $dateTo));
        } else {
            // get total metrics from db
            $totalMetric = $metrics->getTotalMetric();
            if ($totalMetric === null) {
                $totalMetric = new TotalMetric();
            }
            // collect exists visits and new visists
            $visits = $totalMetric->getVisits() + $metricData->getVisits();
            $totalMetric->setVisits($visits);

            // collect exists interactions and new interactions
            $interactions = $totalMetric->getInteractions() + $metricData->getInteractions();
            $totalMetric->setInteractions($interactions);


            $sumTimeSpent = $totalMetric->getSumTimeSpent() + $metricData->getSumTimeSpent();
            $totalMetric->setSumTimeSpent($sumTimeSpent);

            // to calculate avg time spent
            // we should get sum time spent for all period
            // get visists for all period
            // and make calucalation of this field
            if($visits > 0) {
                $avgTimeSpent = round($sumTimeSpent / $visits);
                $totalMetric->setAvgTimeSpent($avgTimeSpent);
            }

            // collect exists page views and new page views
            $pageViews = $totalMetric->getPageViews() + $metricData->getPageViews();
            $totalMetric->setPageViews($pageViews);

            // set updated to for ignoring case to calculate again the same data
            $metrics->setUpdatedAt(new \DateTime());
            // set updated total metrics
            $metrics->setTotalMetric($totalMetric);

            dump(sprintf("updated:total slug:%s, dateFrom:%s, dateTo:%s", $slug, $dateFrom, $dateTo));
        }

        return $metrics;
    }
}