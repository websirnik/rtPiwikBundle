<?php

namespace rtPiwikBundle\Services;


class MetricsService
{
    private $metric;
    private $analytics;

    function __construct($userIds)
    {
        $this->analytics = new Analytics($userIds);
        $this->metric = new MetricModel();
    }

    public function getMetrics($slug, $dateFrom, $dateTo)
    {
        $date = $dateFrom.','.$dateTo;

        $analyticsMetrics = $this->analytics->getMetrics($slug, $date);
        foreach ($analyticsMetrics as $key => $m) {
            if (count($m) > 0 && isset($m["nb_visits"])) {
                $visits = $this->metric->getVisits() + $m["nb_visits"];
                $this->metric->setVisits($visits);
            }
        }

        $analyticsActions = $this->analytics->getActions($slug, $date);
        $avgTimeSpentCount = 0;
        foreach ($analyticsActions as $key => $action) {
            if (count($action) > 0 && isset($action["sum_time_spent"]) && $action["sum_time_spent"] > 0) {
                $avgTimeSpentCount++;
                $pageViews = $this->metric->getPageViews() + $action["nb_hits"];
                $this->metric->setPageViews($pageViews);

                $timeSpent = $this->metric->getAvgTimeSpent() + $action["sum_time_spent"];
                $this->metric->setAvgTimeSpent($timeSpent);
            }
        }

        if ($avgTimeSpentCount > 0) {
            $avgTimeSpent = round($this->metric->getAvgTimeSpent() / $avgTimeSpentCount);
            $this->metric->setAvgTimeSpent($avgTimeSpent);
        }

        $analyticsInteractions = $this->analytics->getInteractions($slug, $date);
        if (isset($analyticsInteractions[0]["nb_events"])) {
            $interactions = $analyticsInteractions[0]["nb_events"];
            $this->metric->setInteractions($interactions);
        }

        return $this->metric;
    }
}