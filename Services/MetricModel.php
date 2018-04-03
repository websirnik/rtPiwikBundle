<?php
/**
 * Created by PhpStorm.
 * User: skews
 * Date: 30.03.2018
 * Time: 14:11
 */

namespace rtPiwikBundle\Services;

class MetricModel
{

    protected $visits;

    protected $pageViews;

    protected $interactions;

    protected $avgTimeSpent;

    function __construct($visits = 0, $pageViews = 0, $interactions = 0, $avgTimeSpent = 0)
    {
        $this->visits = $visits;
        $this->pageViews = $pageViews;
        $this->interactions = $interactions;
        $this->avgTimeSpent = $avgTimeSpent;
    }


    public function setVisits($visits)
    {
        $this->visits = $visits;

        return $this;
    }

    public function getVisits()
    {
        return $this->visits;
    }

    public function setPageViews($pageViews)
    {
        $this->pageViews = $pageViews;

        return $this;
    }


    public function getPageViews()
    {
        return $this->pageViews;
    }

    public function setInteractions($interactions)
    {
        $this->interactions = $interactions;

        return $this;
    }


    public function getInteractions()
    {
        return $this->interactions;
    }

    public function setAvgTimeSpent($avgTimeSpent)
    {
        $this->avgTimeSpent = $avgTimeSpent;

        return $this;
    }


    public function getAvgTimeSpent()
    {
        return $this->avgTimeSpent;
    }
}
