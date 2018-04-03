<?php
/**
 * Created by PhpStorm.
 * User: skews
 * Date: 03.04.2018
 * Time: 17:38
 */

namespace PiwikBundle\Services;


use PiwikBundle\Document\LastWeekMetric;
use PiwikBundle\Document\PercentageChangeLastWeekMetric;

class LastWeekMetrics extends DailyMetrics implements iDailyMetrics
{
    private $localConn;
    private $remoteConn;

    function __construct($localConn, $remoteConn)
    {
        $this->localConn = $localConn;
        $this->remoteConn = $remoteConn;
    }

    /**
     * Get all metrics since last week
     */
    public function execute()
    {
        $now = new \DateTime();
        $date = new \DateTime();

        $today = $now->format('Y-m-d');

        $lastWeek = $date->getTimestamp() - 60 * 60 * 24 * 6;
        $metricsRepository = $this->getMetricsFromDate($lastWeek, $this->localConn, $this->remoteConn);
        $dateFrom = $date->setTimestamp($lastWeek)->format('Y-m-d');

        foreach ($metricsRepository as $metricRepository) {
            $lastWeekMetricData = $this->getContainer()->get("metrics")->getMetrics(
                $metricRepository->getSlug(),
                $dateFrom,
                $today
            );

            $lastWeekMetric = $this->getLastWeekMetric($metricRepository, $lastWeekMetricData);

            $percentageChangeLastWeek = $this->getPercentageChangeLastWeekMetric($metricRepository, $lastWeekMetric);

            if (is_null($metricRepository)) {
                $metricsModel = new Metrics();
                $metricsModel->setSlug($metricRepository->getSlug());

                // create it
                $this->localConn->persist($metricsModel);

                dump(
                    sprintf(
                        "created:daily:last_week slug:%s, dateFrom:%s, dateTo:%s",
                        $metricRepository->getSlug(),
                        $dateFrom,
                        $today
                    )
                );
            } else {
                $metricRepository->setUpdatedAt(new \DateTime());
                $metricRepository->setLastWeekMetric($lastWeekMetric);
                $metricRepository->setPercentageChangeLastWeek($percentageChangeLastWeek);

                dump(
                    sprintf(
                        "updated:daily:last_week slug:%s, dateFrom:%s, dateTo:%s",
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
     * Get metrics data since last week and current week
     * @param $metricRepository - metrics collection
     * @param $lastWeekMetric - piwik metrics data
     * @return PercentageChangeLastWeekMetric
     */
    private function getPercentageChangeLastWeekMetric($metricRepository, $lastWeekMetric)
    {
        // get percentageChangeLastDay from current metricRepo TODO could be check inside mode ?
        $percentageChangeLastWeek = $metricRepository->getPercentageChangeLastWeek();
        if (is_null($percentageChangeLastWeek)) {
            $percentageChangeLastWeek = new PercentageChangeLastWeekMetric();
        }

        // set percentageChangeLastDay from piwik and current value from local DB
        $visits = ($percentageChangeLastWeek->getVisits() - $lastWeekMetric->getVisits()) / 100;
        $percentageChangeLastWeek->setVisits($visits);

        $interactions = ($percentageChangeLastWeek->getInteractions() - $lastWeekMetric->getInteractions()) / 100;
        $percentageChangeLastWeek->setInteractions($interactions);

        $avgTimeSpent = ($percentageChangeLastWeek->getAvgTimeSpent() - $lastWeekMetric->getAvgTimeSpent()) / 100;
        $percentageChangeLastWeek->setAvgTimeSpent($avgTimeSpent);

        $pageViews = ($percentageChangeLastWeek->getPageViews() - $lastWeekMetric->getPageViews()) / 100;
        $percentageChangeLastWeek->setPageViews($pageViews);

        return $percentageChangeLastWeek;
    }

    /**
     * Get last week metrics data
     * @param $metricRepository - metrics collection
     * @param $lastWeekMetricData - piwik metrics data
     * @return LastWeekMetric
     */
    private function getLastWeekMetric($metricRepository, $lastWeekMetricData)
    {
        // get lastWeekMetric from current metricRepo TODO could be check inside mode ?
        $lastWeekMetric = $metricRepository->getLastWeekMetric();
        if (is_null($lastWeekMetric)) {
            $lastWeekMetric = new LastWeekMetric();
        }

        // set lastWeekMetricData from piwik
        $lastWeekMetric->setVisits($lastWeekMetricData->getVisits());
        $lastWeekMetric->setInteractions($lastWeekMetricData->getInteractions());
        $lastWeekMetric->setAvgTimeSpent($lastWeekMetricData->getAvgTimeSpent());
        $lastWeekMetric->setPageViews($lastWeekMetricData->getPageViews());

        return $lastWeekMetric;
    }


}