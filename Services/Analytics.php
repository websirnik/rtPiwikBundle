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
            'segment' => sprintf("pageUrl=@%s;userId!=%s", $slug, $this->userIds),
            "expanded" => 0,
            "flat" => 0,
        ];

        return $this->render($query);
    }

    public function getActions($slug, $date)
    {
        $query = [
            "date" => $date,
            "method" => "Actions.getPageUrls",
            "period" => "range",
            "segment" => sprintf("pageUrl=@%s;userId!=%s", $slug, $this->userIds),
            "expanded" => 0,
            "flat" => 0,
        ];

        $actions = $this->render($query);

        if ($actions > 0 && isset($actions[0]["idsubdatatable"])) {
            $idsubdatatable = $actions[0]["idsubdatatable"];

            $query = [
                "date" => $query["date"],
                "filter_pattern" => "^((?!edit).)*$",
                "idSubtable" => $idsubdatatable,
                "method" => $query["method"],
                "period" => $query["period"],
                "segment" => $query["segment"],
                "slug" => $query["slug"],
                "expanded" => $query["expanded"],
                "flat" => $query["flat"],
            ];

            return $this->render($query);
        }

        return $this->render();
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
        ];

        return $this->render($query);
    }
}