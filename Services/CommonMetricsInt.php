<?php
namespace rtPiwikBundle\Services;


interface CommonMetricsInt
{
    public function createMetricsClient($baseUri);
    public function get($board, $slug, $dateFrom, $dateTo, $userIds, $type);
}