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
            'segment' => "pageUrl=@".$slug.";userId!=51dbeae06a0239d21f3b436e;userId!=52529972c301bf6604e7931d;userId!=5697b7c6296fd3cc638b476e;userId!=59a01dfc1d76fc545528aba4;userId!=597e3bf71d76fc393954d4aa;userId!=5a4d12ea1d76fc5fdc0e8c6d;userId!=5a27531f1d76fc42895dff82;userId!=587d4a8e0c87bbca148b482d;userId!=5a3823181d76fc50c975c18b;userId!=566946fbbe562b4f588b4641;userId!=574dcde4a7e80ea4118b7847;userId!=5a4e38771d76fc61796b4ba3",
            "slug" => $slug,
            "expanded" => 0,
            "flat" => 0,
        ];

        return $this->render($query);
    }

    public function getActions($slug, $date)
    {
        $query = [
            "date" => $date,
            "label" => "relayto>".$slug,
            "method" => "Actions.getPageUrls",
            "period" => "range",
            "segment" => "userId!=52529972c301bf6604e7931d;userId!=51dbeae06a0239d21f3b436e;userId!=566946fbbe562b4f588b4641;userId!=587d4a8e0c87bbca148b482d;userId!=5697b7c6296fd3cc638b476e;userId!=574dcde4a7e80ea4118b7847",
            "slug" => $slug,
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
            "slug" => $slug,
            "flat" => 0,
        ];

        return $this->render($query);
    }
}