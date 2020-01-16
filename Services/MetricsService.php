<?php

namespace rtPiwikBundle\Services;

class MetricsService
{
    private $metric;
    private $analytics;

    /**
     * MetricsService constructor.
     * @param Analytics $analytics
     */
    function __construct($analytics)
    {
        $this->analytics = $analytics;
        $this->metric = new MetricModel();
    }

    /**
     * getSlugs returns array of all slugs
     * @param $dateFrom - date start
     * @param $dateTo - date end
     * @param $userIds - array of user's ids
     * @return array - array of slugs
     */
    public function getSlugs($dateFrom, $dateTo, $userIds)
    {
        $slugs = [];
        // make date range for piwik service
        $date = $dateFrom.','.$dateTo;

        // get data from piwik service for entry pages
        try {
            $analyticsEntryPages = $this->analytics->getEntryPages($date, $userIds);
            foreach ($analyticsEntryPages as $action) {
                if (isset($action["subtable"])) {
                    foreach ($action["subtable"] as $subtable) {
                        $s = $subtable["label"];
                        if (strpos($s, '?') !== false) {
                            $slugSplitted = explode("?", $s);
                            $s = $slugSplitted[0];
                        }

                        if ($s[0] === "/") {
                            $slugSplitted = explode("/", $s);
                            $s = $slugSplitted[1];
                        }

                        if (!in_array($s, $slugs, true)) {
                            $slugs[] = $s;
                        }
                    }
                }
            }

            return $slugs;
        } catch (\Exception $e) {
            dump($e->getMessage());

            return [];
        }
    }

    /**
     * calculateMetrics returns metrics calling piwik service for getting metrics
     * @param $slug - slug of the doc
     * @param $dateFrom - date start
     * @param $dateTo - date end
     * @param $userIds - array of user's ids
     * @return MetricModel - returns metrics model
     */
    public function calculateMetrics($slug, $dateFrom, $dateTo, $userIds): MetricModel
    {
        // create instance of metrics model
        $metric = new MetricModel();
        // define default values
        $visits = 0;
        $timeSpent = 0;
        $pageViews = 0;
        $interactions = 0;
        // make date range for piwik service
        $date = $dateFrom.','.$dateTo;

        // get data from piwik service for metrics
        try {
            $analyticsMetrics = $this->analytics->getMetrics($slug, $date, $userIds);
            foreach ($analyticsMetrics as $key => $m) {
                // collect all visits
                if (count($m) > 0 && isset($m["nb_visits"])) {
                    $visits += $m["nb_visits"];
                }
            }
        } catch (\Exception $e) {
            dump($e->getMessage());
        }


        // get data from piwik service for actions
        try {
            $analyticsActions = $this->analytics->getActions($slug, $date, $userIds);

            foreach ($analyticsActions as $key => $action) {
                if (count($action) > 0 && isset($action['sum_time_spent']) && $action['sum_time_spent'] > 0) {
                    // collect all page views and time spent
                    $pageViews += $action['nb_hits'];
                    $timeSpent += $action['sum_time_spent'];
                }
            }
        } catch (\Exception $e) {
            dump($e->getMessage());
        }


        // get data from piwik service for interactions
        try {
            $analyticsInteractions = $this->analytics->getInteractions($slug, $date);
            if (count($analyticsInteractions) > 0 && isset($analyticsInteractions[0]['nb_events'])) {
                $interactions = $analyticsInteractions[0]['nb_events'];
            }
        } catch (\Exception $e) {
            dump($e->getMessage());
        }


        // setted all data for metrics model
        $metric->setVisits($visits);
        $metric->setPageViews($pageViews);
        $metric->setInteractions($interactions);
        $metric->setSumTimeSpent($timeSpent);

        dump('views: '.$visits.' pageViews: '.$pageViews.' sumTimeSpent: '.$timeSpent.'s interactions: '.$interactions);

        return $metric;
    }

    public function getVisitedDocsMetrics($dateFrom, $dateTo, $userIds): array
    {
        $slugs = [];
        $dataRange = $dateFrom.','.$dateTo;
        try {
            $analyticsMetrics = $this->analytics->getVisitedDocs($dataRange, $userIds);

            foreach ($analyticsMetrics as $key => $m) {
                if (isset($m['subtable']) && count($m['subtable']) > 0) {
                    foreach ($m['subtable'] as $subKey => $subM) {
                        if (isset($subM['nb_visits']) && $subM['nb_visits'] > 0) {
                            $slug = $subM['label'];
                            if (strpos($slug, '/') === 0) {
                                $slug = substr($slug, 1);
                            }
                            if (!in_array($slug, $slugs, true)) {
                                $slugs[] = $slug;
                            }
                        }
                    }
                }
            }

            return $slugs;
        } catch (\Exception $e) {
            dump($e->getMessage());

            return [];
        }
    }
}