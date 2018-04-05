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
    private $userIds;

    private $client;

    function __construct($userIds)
    {
        $this->userIds = implode(";userId!=", $userIds);
        $this->client = new \GuzzleHttp\Client(['base_uri' => 'https://rtpiwik.frb.io/index.php']);
    }

    private function render(array $query = [])
    {
        $query = array_merge($this->defaultQuery, $query);

        return json_decode($this->client->get("/", ['query' => $query])->getBody(), true);
    }

    public function getMetrics($slug, $date)
    {
        $query = [
            'date' => $date,
            'method' => "API.get",
            'period' => "day",
            'segment' => sprintf("pageUrl!@edit;pageUrl=@%s;userId!=%s", $slug, $this->userIds),
            "expanded" => 0,
            "flat" => 0,
            "slug" => $slug,
        ];

        return $this->render($query);
    }

    public function getActions($slug, $date)
    {
        $query = [
            "date" => $date,
            "method" => "Actions.getPageUrls",
            "period" => "range",
            "segment" => sprintf("pageUrl!@edit;pageUrl=@%s;userId!=%s", $slug, $this->userIds),
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
            "slug" => $slug
        ];

        return $this->render($query);
    }
}