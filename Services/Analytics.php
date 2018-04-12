<?php

namespace rtPiwikBundle\Services;


class Analytics
{
    private $defaultQuery = [
        'module' => 'API',
        'idSite' => '1',
        'format' => 'JSON',
        'token_auth' => '14fbc812766bffd3e5fc72925312b7b7',
    ];

    private $client;

    function __construct()
    {
        $this->client = new \GuzzleHttp\Client(['base_uri' => 'https://rtpiwik.frb.io/index.php']);
    }

    private $piwikReqLimit = 10;

    private function render(array $query = [], $requestAttempt = 0)
    {
        $query = array_merge($this->defaultQuery, $query);
        try {
            return json_decode($this->client->get("/", ['query' => $query])->getBody(), true);
        } catch (\Exception $e) {
            if ($requestAttempt < $this->piwikReqLimit) {
                $requestAttempt++;
                // sleep each time for request attempt * 30 sec | (30, 60, 90 ...) sec
                sleep($requestAttempt * 30);

                return $this->render($query, $requestAttempt);
            } else {
                return new \Exception("Requests to piwik fails ".$this->piwikReqLimit." times");
            }
        }
    }

    public function getEntryPages($date, $userIds)
    {
        $query = [
            "date" => $date,
            "method" => "Actions.getPageUrls",
            "period" => "range",
            "segment" => sprintf("pageUrl!@edit;userId!=%s", implode(";userId!=", $userIds)),
            "expanded" => 1,
            "flat" => 0,
        ];

        return $this->render($query);
    }

    public function getMetrics($slug, $date, $userIds)
    {
        $query = [
            'date' => $date,
            'method' => "API.get",
            'period' => "day",
            'segment' => sprintf("pageUrl!@edit;pageUrl=@%s;userId!=%s", $slug, implode(";userId!=", $userIds)),
            "expanded" => 0,
            "flat" => 0,
            "slug" => $slug,
        ];

        return $this->render($query);
    }

    public function getActions($slug, $date, $userIds)
    {
        $query = [
            "date" => $date,
            "method" => "Actions.getPageUrls",
            "period" => "range",
            "segment" => sprintf("pageUrl!@edit;pageUrl=@%s;userId!=%s", $slug, implode(";userId!=", $userIds)),
            "expanded" => 0,
            "flat" => 0,
            "slug" => $slug,
        ];

        return $this->render($query);
    }

    public function getInteractions($slug, $date)
    {
        $query = [
            "date" => $date,
            "expanded" => 1,
            "method" => "Events.getCategory",
            "period" => "range",
            "segment" => "eventCategory=@".$slug,
            "flat" => 0,
            "slug" => $slug,
        ];

        return $this->render($query);
    }
}