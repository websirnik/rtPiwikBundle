<?php

namespace rtPiwikBundle\Services;


class MetricsService
{
    private $metric;
    private $analytics;

    function __construct()
    {
        $this->analytics = new Analytics();
        $this->metric = new MetricModel();
    }

    public function getMetrics($slug, $dateFrom, $dateTo)
    {
        $date = $dateFrom.','.$dateTo;

        $analyticsMetrics = $this->analytics->getMetrics($slug, $date);
        foreach ($analyticsMetrics as $key => $m) {
            if (count($m) > 0 && isset($m["nb_visits"])) {
                $this->metric->setVisits($this->metric->getVisits() + $m["nb_visits"]);
            }
        }

        $analyticsActions = $this->analytics->getActions($slug, $date);
        $avgTimeSpentCount = 0;
        foreach ($analyticsActions as $key => $action) {
            if (count($action) > 0 && isset($action["sum_time_spent"]) && $action["sum_time_spent"] > 0) {
                $avgTimeSpentCount++;
                $this->metric->setPageViews($this->metric->getPageViews() + $action["nb_hits"]);
                $this->metric->setAvgTimeSpent($this->metric->getAvgTimeSpent() + $action["sum_time_spent"]);
            }
        }

        if ($avgTimeSpentCount > 0) {
            $this->metric->setAvgTimeSpent(round($this->metric->getAvgTimeSpent() / $avgTimeSpentCount));
        }

        $analyticsInteractions = $this->analytics->getInteractions($slug, $date);
        if (isset($analyticsInteractions[0]["nb_events"])) {
            $this->metric->setInteractions($analyticsInteractions[0]["nb_events"]);
        }

        return $this->metric;
    }
}