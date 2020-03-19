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
     * @param $baseUri
     */
    public function setMetricsClient($baseUri): void
    {
        $this->analytics->setClient($baseUri);
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

    private function getSplittedSlug($label): bool
    {
        $splitted = explode('/', $label);

        return isset($splitted[2]);
    }

    private function includes($str, $searchString): bool
    {
        return strpos($str, $searchString) !== false;
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
        dump('start calculating metrics');
        dump(sprintf("slug:%s ", $slug));
        dump(sprintf("dateFrom:%s dateTo:%s", $dateFrom, $dateTo));
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
            dump('getting metrics from piwik..');
            $analyticsMetrics = $this->analytics->getMetrics($slug, $date, $userIds);
            dump('calc visits');
            foreach ($analyticsMetrics as $key => $m) {
                // collect all visits
                if (count($m) > 0 && isset($m["nb_visits"])) {
                    $visits += $m["nb_visits"];
                }
            }
            dump("visits:", sprintf("%d", $visits));
        } catch (\Exception $e) {
            dump('getting metrics from piwik fail');
            dump($e->getMessage());
        }


        // get data from piwik service for actions
        try {
            dump('getting actions from piwik..');
            $analyticsActions = $this->analytics->getActions($slug, $date, $userIds);
            dump('calc actions');
            foreach ($analyticsActions as $key => $action) {
                if (count($action) > 0 && isset($action['nb_hits']) && $action['nb_hits'] > 0) {
                    // collect all page views and time spent
                    if (isset($action['label']) && $this->includes($action['label'], $slug) && !$this->includes($action['label'], 'edit') && !$this->includes(
                            $action['label'],
                            'analytics'
                        ) && $this->getSplittedSlug($action['label'])) {
                        $pageViews += $action['nb_hits'];
                    }
                }

                if (count($action) > 0 && isset($action['sum_time_spent']) && $action['sum_time_spent'] > 0) {
                    // collect all page views and time spent
                    if (isset($action['label']) && $this->includes($action['label'], $slug) && !$this->includes($action['label'], 'edit') && !$this->includes(
                            $action['label'],
                            'analytics'
                        ) && $this->getSplittedSlug($action['label'])) {
                        $timeSpent += $action['sum_time_spent'];
                    }
                }
            }
            dump("pageViews:", sprintf("%d", $pageViews));
            dump("timeSpent:", sprintf("%d", $timeSpent));
        } catch (\Exception $e) {
            dump('getting actions from piwik fail');
            dump($e->getMessage());
        }


        // get data from piwik service for interactions
        try {
            dump('getting interactions from piwik..');
            $analyticsInteractions = $this->analytics->getInteractions($slug, $date, $userIds);
            dump('calc interactions');

            foreach ($analyticsInteractions as $key => $inter) {
                // collect all visits
                if (count($inter) > 0 && isset($inter["nb_events"]) && $inter["nb_events"] > 0) {
                    if (isset($inter['label']) && !$this->includes($inter['label'], 'edit') && !$this->includes($inter['label'], 'analytics') && $this->includes($inter['label'], $slug)) {
                        $interactions += $inter["nb_events"];
                    }
                }
            }

            dump("interactions:", sprintf("%d", $interactions));
        } catch (\Exception $e) {
            dump('getting interactions from piwik fail');
            dump($e->getMessage());
        }


        // setted all data for metrics model
        $metric->setVisits($visits);
        $metric->setPageViews($pageViews);
        $metric->setInteractions($interactions);
        $metric->setSumTimeSpent($timeSpent);

        dump("total:", sprintf("visits:%d pageViews:%d sumTimeSpent:%d's interactions:%d", $visits, $pageViews, $timeSpent, $interactions));

        return $metric;
    }

    /**
     * Converts URL path string to array
     * From: http://stackoverflow.com/a/9122293/257815
     * @param  string $path [description]
     * @return array       [description]
     */
    private function getPathArray($path)
    {

        $array = explode('/', $path);

        //remove empty value from array
        $array = array_filter($array);

        // Resettings array key as they might start with 1 instead of 0
        $array = array_values($array);

        return $array;
    }

    private function getslug($url)
    {
        $parsedUrl = parse_url($url);

        $pathArray = $this->getPathArray($parsedUrl['path']);

        return isset($pathArray[1]) ? $pathArray[1] : null;
    }


    public function getVisitedDocsMetrics($dateFrom, $dateTo, $userIds): array
    {
        $slugs = [];
        $dataRange = $dateFrom.','.$dateTo;
        try {

            $analyticsMetrics = $this->analytics->getVisitedDocs($dataRange, $userIds);

            foreach ($analyticsMetrics as $key => $visit) {
                if (isset($visit['actionDetails']) && count($visit['actionDetails']) > 0) {

                    foreach ($visit['actionDetails'] as $details) {
                        if (isset($details['url']) && $slug = $this->getslug($details['url'])) {
                            if (!in_array($slug, $slugs)) {
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