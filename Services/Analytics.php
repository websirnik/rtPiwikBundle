<?php

namespace rtPiwikBundle\Services;


class Analytics implements AnalyticsInt
{
    public const DB_PIWIK_MASTER = 'https://rtpiwik.frb.io/index.php';
    public const DB_PIWIK_MASTER_REPLICA = 'https://rt-piwik-replica.frb.io/index.php';

    private $defaultQuery = [
        'module' => 'API',
        'idSite' => '1',
        'format' => 'JSON',
        'token_auth' => '14fbc812766bffd3e5fc72925312b7b7',
    ];

    private $client;

    public function setClient($baseUri)
    {
        $this->client = new \GuzzleHttp\Client(['base_uri' => $baseUri]);
    }

    public function getClient()
    {
        return $this->client;
    }

    private $piwikReqLimit = 10;

    /**
     * render makes requests to piwik service
     * if received error try again for 10 times
     * stepm increase in ariphmetics progression with time sleep
     * @param array $query - query to piwik service
     * @param int $requestAttempt - amount of attempts with request to server
     * @return \Exception|mixed - returns exception of data
     * @throws \Exception
     */
    private function render(array $query = [], $requestAttempt = 0)
    {
        $query = array_merge($this->defaultQuery, $query);
        try {
            return json_decode($this->getClient()->get("/", ['query' => $query])->getBody(), true);
        } catch (\Exception $e) {
            if ($requestAttempt < $this->piwikReqLimit) {
                $requestAttempt++;
                // sleep each time for request attempt * 30 sec | (30, 60, 90 ...) sec
                sleep($requestAttempt * 5);

                return $this->render($query, $requestAttempt);
            }

            throw new \RuntimeException('Requests to piwik fails ' . $this->piwikReqLimit . ' times');
        }
    }

    /**
     * getEntryPages returns data from piwik service with entry pages
     * notice: segment should has page url which
     * not containce edit &
     * not containce analytics &
     * containce slug &
     * all user ids not equels in user's ids
     * @param $date - date range
     * @param $userIds - array of user's ids
     * @return \Exception|mixed - returns exception of data
     * @throws \Exception
     */
    public function getEntryPages($date, $userIds)
    {
        $segment = sprintf("actionUrl!@edit;actionUrl!@analytics");
        if (count($userIds) > 0) {
            $segment = sprintf("actionUrl!@edit;actionUrl!@analytics;userId!=%s", implode(";userId!=", $userIds));
        }
        $query = [
            "filter_limit" => -1,
            "date" => $date,
            "method" => "Actions.getPageUrls",
            "period" => "range",
            "segment" => $segment,
            "expanded" => 1,
            "flat" => 0,
        ];

        return $this->render($query);
    }

    /**
     * getMetrics returns data from piwik service with metrics
     * notice: segment should has page url which
     * containce slug &
     * all user ids not equels in user's ids
     * @param $slug - doc slug
     * @param $date - date range
     * @param $userIds - array of user's ids
     * @return \Exception|mixed - returns exception of data
     * @throws \Exception
     */
    public function getMetrics($slug, $date, $userIds)
    {
        $segment = sprintf("actionUrl!@edit;actionUrl!@analytics;actionUrl=@%s", $slug);
        if (count($userIds) > 0) {
            $segment = sprintf("actionUrl!@edit;actionUrl!@analytics;actionUrl=@%s;userId!=%s", $slug, implode(";userId!=", $userIds));
        }
        $query = [
            "filter_limit" => -1,
            'date' => $date,
            'method' => "API.get",
            'period' => "day",
            'segment' => $segment,
            "expanded" => 0,
            "flat" => 0,
            "slug" => $slug,
        ];

        return $this->render($query);
    }

    /**
     * getActions returns data from piwik service with actions
     * notice: segment should has page url which
     * not containce edit &
     * not containce analytics &
     * containce slug &
     * all user ids not equels in user's ids
     * @param $slug - doc slug
     * @param $date - date range
     * @param $userIds - array of user's ids
     * @return \Exception|mixed - returns exception of data
     * @throws \Exception
     */
    public function getActions($slug, $date, $userIds)
    {
        $segment = sprintf("actionUrl=@%s", $slug);
        if (count($userIds) > 0) {
            $segment = sprintf("actionUrl=@%s;userId!=%s", $slug, implode(";userId!=", $userIds));
        }
        $query = [
            "filter_limit" => -1,
            "date" => $date,
            "method" => "Actions.getPageUrls",
            "period" => "range",
            "segment" => $segment,
            "expanded" => 0,
            "flat" => 1,
            "slug" => $slug,
        ];

        return $this->render($query);
    }

    /**
     * getInteractions returns data from piwik service with interactions
     * notice: segment should has action url which
     * containce slug
     * @param $slug - doc slug
     * @param $date - date range
     * @param $userIds - array of user's ids
     * @return \Exception|mixed - returns exception of data
     * @throws \Exception
     */
    public function getInteractions($slug, $date, $userIds)
    {
        $segment = sprintf("actionUrl=@%s", $slug);
        if (count($userIds) > 0) {
            $segment = sprintf("actionUrl=@%s;userId!=%s", $slug, implode(";userId!=", $userIds));
        }
        $query = [
            "flat" => 1,
            "date" => $date,
            "expanded" => 1,
            "method" => "Events.getCategory",
            "period" => "range",
            "segment" => $segment,
            "slug" => $slug,
            "filter_limit" => -1,
        ];

        return $this->render($query);
    }

    /**
     * getVisitedDocs returns visited docs
     * notice: segment should has page url which
     * not containce edit &
     * not containce analytics &
     * containce slug &
     * all user ids not equels in user's ids
     * @param $date - date range
     * @param $userIds - array of user's ids
     * @return \Exception|mixed - returns exception of data
     * @throws \Exception
     */
    public function getVisitedDocs($date, $userIds, $offset = 0, $limit = -1)
    {
        $segment = "actionUrl!@edit;actionUrl!@analytics";
        if (count($userIds) > 0) {
            $segment = sprintf("actionUrl!@edit;actionUrl!@analytics;userId!=%s", implode(";userId!=", $userIds));
        }
        $query = [
            "filter_offset" => $offset,
            "filter_limit" => $limit,
            "date" => $date,
            "method" => "Live.getLastVisitsDetails",
            "period" => "range",
            "segment" => $segment,
            "expanded" => 1,
            "flat" => 0,
        ];
        return $this->render($query);
    }
}