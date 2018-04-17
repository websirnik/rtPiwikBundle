<?php

namespace rtPiwikBundle\Services;


class MetricsService
{
    private $metric;
    private $analytics;

    function __construct($analytics)
    {
        $this->analytics = $analytics;
        $this->metric = new MetricModel();
    }

    public function getSlugs($dateFrom, $dateTo, $userIds)
    {
        $slugs = [];
        $date = $dateFrom.','.$dateTo;

        $analyticsEntryPages = $this->analytics->getEntryPages($date, $userIds);
        foreach ($analyticsEntryPages as $action) {
            if (isset($action["subtable"])) {
                foreach ($action["subtable"] as $subtable) {
                    $s = $subtable["label"];
                    if (strpos($s, '?') !== false) {
                        $slugSplitted = explode("?", $s);
                        $s = $slugSplitted[0];
                    }

                    if ($s[0] == "/") {
                        $slugSplitted = explode("/", $s);
                        $s = $slugSplitted[1];
                    }

                    if (!in_array($s, $slugs)) {
                        array_push($slugs, $s);
                    }
                }
            }
        }

        return $slugs;
    }

    public function getMetrics($slug, $dateFrom, $dateTo, $userIds)
    {
        $date = $dateFrom.','.$dateTo;
        $visits = 0;
        $analyticsMetrics = $this->analytics->getMetrics($slug, $date, $userIds);
        foreach ($analyticsMetrics as $key => $m) {
            if (count($m) > 0 && isset($m["nb_visits"])) {
                $visits += $m["nb_visits"];
            }
        }
        $this->metric->setVisits($visits);


        $analyticsActions = $this->analytics->getActions($slug, $date, $userIds);


        $timeSpent = 0;
        foreach ($analyticsActions as $key => $action) {
            if (count($action) > 0 && isset($action["sum_time_spent"]) && $action["sum_time_spent"] > 0) {

                $pageViews = $this->metric->getPageViews() + $action["nb_hits"];
                $this->metric->setPageViews($pageViews);

                $timeSpent += $action["sum_time_spent"];
            }
        }

        if ($this->metric->getVisits() > 0) {
            $avgTimeSpent = round($timeSpent / $this->metric->getVisits());
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