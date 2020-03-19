<?php
namespace rtPiwikBundle\Services;


interface TotalMetricInt
{
    public function createMetricsClient($baseUri);
    public function get($board, $slug, \DateTime $date, $userIds, $reCalculate = false);
}