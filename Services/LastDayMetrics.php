<?php
/**
 * Created by PhpStorm.
 * User: skews
 * Date: 03.04.2018
 * Time: 17:34
 */

namespace rtPiwikBundle\Services;


use rtPiwikBundle\Document\LastDayMetric;
use rtPiwikBundle\Document\PercentageChangeLastDayMetric;
use rtPiwikBundle\Document\TotalMetric;

class LastDayMetrics extends DailyMetrics implements iDailyMetrics
{

    private $localConn;
    private $remoteConn;

    function __construct($localConn, $remoteConn)
    {
        $this->localConn = $localConn;
        $this->remoteConn = $remoteConn;
    }

    /**
     * Get all merricst since last day
     */
    public function execute()
    {
        $now = new \DateTime();
        $date = new \DateTime();

        $today = $now->format('Y-m-d');

        $yesterday = $date->getTimestamp() - 60 * 60 * 24;
        $metricsRepository = $this->getMetricsFromDate($yesterday, $this->localConn, $this->remoteConn);
        $dateFrom = $date->setTimestamp($yesterday)->format('Y-m-d');

        foreach ($metricsRepository as $metricRepository) {
            // get data from piwik by each slug TODO could be batch request to piwik ?
            $lastDayMetricData = $this->getContainer()->get("metrics")->getMetrics(
                $metricRepository->getSlug(),
                $dateFrom,
                $today
            );

            $lastDayMetric = $this->getLastDayMetric($metricRepository, $lastDayMetricData);

            $percentageChangeLastDay = $this->getPercentageChangeLastDayMetric($metricRepository, $lastDayMetric);

            // if there is no metric repository
            if (is_null($metricRepository)) {
                $metricsModel = new Metrics();
                $totalMetric = $this->getTotalMetric($metricRepository, $lastDayMetric);

                $metricsModel->setSlug($metricRepository->getSlug());
                $metricsModel->setTotalMetric($totalMetric);

                // create it
                $this->localConn->persist($metricsModel);

                dump(
                    sprintf(
                        "created:daily:last_day slug:%s, dateFrom:%s, dateTo:%s",
                        $metricRepository->getSlug(),
                        $dateFrom,
                        $today
                    )
                );
            } else {
                $totalMetric = $this->getTotalMetric($metricRepository, $lastDayMetric);

                $metricRepository->setUpdatedAt(new \DateTime());
                $metricRepository->setTotalMetric($totalMetric);
                $metricRepository->setLastDayMetric($lastDayMetric);
                $metricRepository->setPercentageChangeLastDay($percentageChangeLastDay);

                dump(
                    sprintf(
                        "updated:daily:last_day slug:%s, dateFrom:%s, dateTo:%s",
                        $metricRepository->getSlug(),
                        $dateFrom,
                        $today
                    )
                );
            }
        }

        $this->localConn->flush();
        $this->localConn->clear();
    }

    /**
     * Get total metrics since last day
     * @param $metricRepository - metrics collection
     * @param $lastDayMetric - piwik metrics data
     * @return TotalMetric
     */
    private function getTotalMetric($metricRepository, $lastDayMetric)
    {
        $totalMetric = new TotalMetric();
        // if there is no metric repository
        if (is_null($metricRepository)) {
            // set new total metric, because a new
            $totalMetric->setVisits($lastDayMetric->getVisits());
            $totalMetric->setInteractions($lastDayMetric->getInteractions());
            $totalMetric->setAvgTimeSpent($lastDayMetric->getAvgTimeSpent());
            $totalMetric->setPageViews($lastDayMetric->getPageViews());
        } else {
            // get totalMetric from current metricRepo TODO could be check inside mode ?
            $totalMetric = $metricRepository->getTotalMetric();
            if (is_null($totalMetric)) {
                $totalMetric = new TotalMetric();
            }
            // set updated total metrics
            $visits = $totalMetric->getVisits() + $lastDayMetric->getVisits();
            $totalMetric->setVisits($visits);

            $interactions = $totalMetric->getInteractions() + $lastDayMetric->getInteractions();
            $totalMetric->setInteractions($interactions);

            if ($lastDayMetric->getAvgTimeSpent() > 0) {
                $avgTimeSpent = round(($totalMetric->getAvgTimeSpent() + $lastDayMetric->getAvgTimeSpent()) / 2);
                $totalMetric->setAvgTimeSpent($avgTimeSpent);
            }

            $pageViews = $totalMetric->getPageViews() + $lastDayMetric->getPageViews();
            $totalMetric->setPageViews($pageViews);
        }

        return $totalMetric;
    }

    /**
     * Get metrics data since last day and current day
     * @param $metricRepository - metrics collection
     * @param $lastDayMetric - piwik metrics data
     * @return PercentageChangeLastDayMetric
     */
    private function getPercentageChangeLastDayMetric($metricRepository, $lastDayMetric)
    {
        // get percentageChangeLastDay from current metricRepo TODO could be check inside mode ?
        $percentageChangeLastDay = $metricRepository->getPercentageChangeLastDay();
        if (is_null($percentageChangeLastDay)) {
            $percentageChangeLastDay = new PercentageChangeLastDayMetric();
        }

        // set percentageChangeLastDay from piwik and current value from local DB
        $visits = ($percentageChangeLastDay->getVisits() - $lastDayMetric->getVisits()) / 100;
        $percentageChangeLastDay->setVisits($visits);

        $interactions = ($percentageChangeLastDay->getInteractions() - $lastDayMetric->getInteractions()) / 100;
        $percentageChangeLastDay->setInteractions($interactions);

        $avgTimeSpent = ($percentageChangeLastDay->getAvgTimeSpent() - $lastDayMetric->getAvgTimeSpent()) / 100;
        $percentageChangeLastDay->setAvgTimeSpent($avgTimeSpent);

        $pageViews = ($percentageChangeLastDay->getPageViews() - $lastDayMetric->getPageViews()) / 100;
        $percentageChangeLastDay->setPageViews($pageViews);

        return $percentageChangeLastDay;
    }

    /**
     * Get last day metrics data
     * @param $metricRepository - metrics collection
     * @param $lastDayMetricData - piwik metrics data
     * @return LastDayMetric
     */
    private function getLastDayMetric($metricRepository, $lastDayMetricData)
    {
        // get lastDayMetric from current metricRepo TODO could be check inside mode ?
        $lastDayMetric = $metricRepository->getLastDayMetric();
        if (is_null($lastDayMetric)) {
            $lastDayMetric = new LastDayMetric();
        }

        // set lastDayMetric from piwik
        $lastDayMetric->setVisits($lastDayMetricData->getVisits());
        $lastDayMetric->setInteractions($lastDayMetricData->getInteractions());
        $lastDayMetric->setAvgTimeSpent($lastDayMetricData->getAvgTimeSpent());
        $lastDayMetric->setPageViews($lastDayMetricData->getPageViews());

        return $lastDayMetric;
    }
}

